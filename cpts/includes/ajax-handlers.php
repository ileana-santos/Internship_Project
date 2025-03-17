<?php
if (!defined('ABSPATH')) {
    exit;
}

    add_action('wp_ajax_cpts_ajax_search_handler', 'cpts_ajax_search_handler');
    add_action('wp_ajax_nopriv_cpts_ajax_search_handler', 'cpts_ajax_search_handler');
    add_action('wp_ajax_asearch', 'ajax_search');
    add_action('wp_ajax_nopriv_asearch', 'ajax_search');
    // Enqueue scripts for AJAX
    add_action('admin_enqueue_scripts', 'enqueue_cpts_ajax_scripts');

// Register AJAX handlers for logged-in and guest users
add_action('wp_enqueue_scripts', 'enqueue_ajax_scripts');

function cpts_ajax_search_handler() {
    check_ajax_referer('cpts_ajax_search_nonce', '_ajax_nonce');

    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';

    $custom_post_types = get_option('acustom_post_types_table_view', array());


    $filtered_results = array_filter($custom_post_types, function ($post_type) use ($query) {
        return stripos($post_type['slug'], $query) !== false ||
            stripos($post_type['singular'], $query) !== false ||
            stripos($post_type['plural'], $query) !== false;
    });

    ob_start();
    if ($filtered_results) {
        foreach ($filtered_results as $slug => $details) {
            echo "<tr>
                <td>{$details['slug']}</td>
                <td>{$details['singular']}</td>
                <td>{$details['plural']}</td>
                <td>{$details['description']}</td>
                <td>" . ($details['public'] ? 'Yes' : 'No') . "</td>
                <td>" . (!empty($details['exclude_from_search']) ? 'Yes' : 'No') . "</td>
                <td>[custom_post_type slug=\"{$details['slug']}\"]</td>
                <td><a href='?page=acpts&edit_post_type={$slug}'>Edit</a></td>
                <td><a href='?page=acpts&delete_post_type={$slug}'>Delete</a></td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='9'>No results found.</td></tr>";
    }
    $output = ob_get_clean();

    wp_send_json_success($output);
}

function ajax_search() {
    parse_str($_POST['data'], $form_data);

    $searchable_options = get_option('acpts_searchable_options', []);
    $filterable_options = get_option('acpts_filterable_options', []);

    $query_args = [
        'post_status'    => 'publish',
        'tax_query'      => [],
        'posts_per_page' => -1,
    ];

    if (!empty($form_data['post_type'])) {
        if (isset($searchable_options[$form_data['post_type']]) && $searchable_options[$form_data['post_type']] == 1) {
            $query_args['post_type'] = $form_data['post_type'];
        } else {
            echo '<p>This post type is not searchable.</p>';
            wp_die();
        }
    } else {
        $query_args['post_type'] = array_keys(array_filter($searchable_options));
    }

    if (!empty($form_data['s'])) {
        $post_type = $form_data['post_type'] ?? null;

        if ($post_type && isset($filterable_options[$post_type]['title']) && $filterable_options[$post_type]
            ['title'] == 1) {
            add_filter('posts_search', function ($search, $wp_query) use ($form_data) {
                global $wpdb;
                if (!empty($wp_query->query_vars['s'])) {
                    $search = $wpdb->prepare(
                        " AND {$wpdb->posts}.post_title LIKE %s ",
                        '%' . $wpdb->esc_like($form_data['s']) . '%'
                    );
                }
                return $search;
            }, 10, 2);
        } else {
            $allowed_post_types = array_filter($query_args['post_type'], function ($type) use ($filterable_options) {
                return isset($filterable_options[$type]['title']) && $filterable_options[$type]['title'] == 1;
            });
            if (!empty($allowed_post_types)) {
                $query_args['post_type'] = $allowed_post_types;
                $query_args['s'] = $form_data['s'];
            } else {
                echo '<p>Searching by title is not allowed for the selected post type(s).</p>';
                wp_die();
            }
        }
    }


    if (!empty($form_data['taxonomy'])) {
        foreach ($form_data['taxonomy'] as $taxonomy => $term_id) {
            if (!empty($term_id) && $term_id !== '0') {
                $query_args['tax_query'][] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term_id,
                ];
            }
        }
    }

    $query = new WP_Query($query_args);

    remove_filter('posts_search', '__return_null', 10);

    // Display results
    if ($query->have_posts()) {
        echo '<div class="container mt-4">';
        echo '<div class="row row-cols-1 row-cols-md-2 g-4">';

        while ($query->have_posts()) {
            $query->the_post();

            echo '<div class="col">';
            echo '<div class="card h-100">';
            if (has_post_thumbnail()) {
                echo '<img src="' . get_the_post_thumbnail_url(get_the_ID(), 'medium') . '" class="card-img-top" alt="'
                    . esc_attr(get_the_title()) . '">';
            } else {
                echo '<img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Placeholder">';
            }
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . get_the_title() . '</h5>';
            echo '<p class="card-text">' . wp_trim_words(get_the_excerpt(), 20, ' [...]') . '</p>';
            echo '</div>';
            echo '<div class="card-footer bg-white border-0">';
            echo '<a href="' . get_permalink() . '" class="btn btn-primary w-100">Read More</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    } else {
        echo '<p class="text-center mt-4">No results found.</p>';
    }

    wp_reset_postdata();
    wp_die();
}

// Enqueue scripts for AJAX functionality
function enqueue_cpts_ajax_scripts() {
    wp_enqueue_script('cpts-ajax-search', plugin_dir_url(__FILE__) . '../js/ajax-search.js', array('jquery'), null, true);
    wp_localize_script('cpts-ajax-search', 'cptsAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('cpts_ajax_search_nonce')
    ));

}

function enqueue_ajax_scripts() {
    wp_enqueue_script(
        'ajax-search',
        plugin_dir_url(__FILE__) . '../js/ajax-search.js',
        array('jquery'),
        null,
        true
    );


    wp_localize_script('ajax-search', 'ajax', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}