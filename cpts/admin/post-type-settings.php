<?php

class CPTRegister {
 public function __construct(){
     add_action('init', array('CPTRegister', 'register_post_type'));
     add_action('admin_post_save_custom_post_type', ['CPTRegister', 'save_custom_post_type']);
     add_action('admin_post_edit_custom_post_type', ['CPTRegister', 'edit_custom_post_type']);
     add_action('admin_post_delete_custom_post_type', ['CPTRegister', 'delete_custom_post_type']);
 }

    public static function register_all() {
        $custom_post_types = get_option('custom_post_types', array());

        if (!empty($custom_post_types)) {
            foreach ($custom_post_types as $slug => $labels) {
                self::register_post_type($slug, $labels);
            }
        }
    }
    public static function register_post_type($slug = '', $labels = array()) {
    if (empty($slug) || empty($labels)) {
        return;
    }

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => $slug),
        'supports' => array(
                            'title',
                            'editor',
                            'author',
                            'thumbnail',
                            'excerpt',
                            'trackbacks',
                            'custom-fields',
                            'comments',
                            'revisions',
                            'page-attributes',
                            'post-formats'
                            ),
    );

    register_post_type($slug, $args);
}

    public static function save_custom_post_type() {
        if (!isset($_POST['save_custom_post_type_nonce']) ||
            !wp_verify_nonce($_POST['save_custom_post_type_nonce'], 'save_custom_post_type_action')) {
            wp_die(__('Invalid nonce specified', 'cpts'),
                __('Error', 'cpts'), array('response' => 403));
        }

        if (isset($_POST['custom_post_type_slug'], $_POST['custom_post_type_singular'], $_POST['custom_post_type_plural'])) {

            $post_type_slug = sanitize_text_field($_POST['custom_post_type_slug']);
            $singular_label = sanitize_text_field($_POST['custom_post_type_singular']);
            $plural_label = sanitize_text_field($_POST['custom_post_type_plural']);
            $description = sanitize_textarea_field($_POST['custom_post_type_description'] ?? '');
            $public = isset($_POST['custom_post_type_public']) ? (bool) $_POST['custom_post_type_public'] : true;
            $exclude_from_search = isset($_POST['custom_post_type_exclude_search']) ? (bool) $_POST['custom_post_type_exclude_search'] : false;
            $menu_position = !empty($_POST['custom_post_type_menu_position']) ? (int) $_POST['custom_post_type_menu_position'] : 25;
            $supports = isset($_POST['custom_post_type_supports']) ? array_map('sanitize_text_field', $_POST['custom_post_type_supports']) : ['title', 'editor'];

            $custom_post_types = get_option('custom_post_types_table_view', array());

            if (array_key_exists($post_type_slug, $custom_post_types) || post_type_exists($post_type_slug)) {
                wp_redirect(add_query_arg('error', 'slug_exists', admin_url('admin.php?page=cpts')));
                exit;
            }

            if (!is_array($custom_post_types)) {
                $custom_post_types = array();
            }

            $custom_post_types[$post_type_slug] = array(
                'slug' => $post_type_slug,
                'singular' => $singular_label,
                'plural' => $plural_label,
                'description' => $description,
                'public' => $public,
                'exclude_from_search' => $exclude_from_search,
                'menu_position' => $menu_position,
                'supports' => $supports,
            );

            $update_result = update_option('custom_post_types', $custom_post_types);

            CPTRegister::register_post_type($post_type_slug, array(
                'name' => $plural_label,
                'singular_name' => $singular_label,
            ));

            flush_rewrite_rules();

            wp_redirect(admin_url('admin.php?page=cpts&status=success'));
            exit;
        } else {
            error_log('Required fields not set.');
        }
    }

    public static function edit_custom_post_type() {
        if (!isset($_POST['save_custom_post_type_nonce']) || !wp_verify_nonce($_POST['save_custom_post_type_nonce'], 'save_custom_post_type_action')) {
            wp_die(__('Invalid nonce specified', 'cpts'), __('Error', 'cpts'), array('response' => 403));
        }

        if (isset($_POST['custom_post_type_slug'], $_POST['custom_post_type_singular'], $_POST['custom_post_type_plural'])) {
            $new_slug = sanitize_text_field($_POST['custom_post_type_slug']);
            // Check if the slug already exists among custom post types or registered post types
            $custom_post_types = get_option('custom_post_types_table_view', array());
            $original_slug = sanitize_text_field($_POST['original_post_type_slug']);
            if (isset($custom_post_types[$original_slug])) {
                if ($new_slug !== $original_slug) {
                    if (array_key_exists($new_slug, $custom_post_types) || post_type_exists($new_slug)) {
                        wp_redirect(add_query_arg('error', 'slug_exists', admin_url('admin.php?page=cpts')));
                        exit;
                    }
                }
            }
            $singular_label = sanitize_text_field($_POST['custom_post_type_singular']);
            $plural_label = sanitize_text_field($_POST['custom_post_type_plural']);
            $description = sanitize_textarea_field($_POST['custom_post_type_description'] ?? '');
            $public = isset($_POST['custom_post_type_public']) ? (bool) $_POST['custom_post_type_public'] : true;
            $exclude_from_search = isset($_POST['custom_post_type_exclude_search']) ? (bool) $_POST['custom_post_type_exclude_search'] : false;
            $menu_position = !empty($_POST['custom_post_type_menu_position']) ? (int) $_POST['custom_post_type_menu_position'] : 25;
            $supports = isset($_POST['custom_post_type_supports']) ? array_map('sanitize_text_field', $_POST['custom_post_type_supports']) : ['title', 'editor'];

            if (!is_array($custom_post_types)) {
                $custom_post_types = array();
            }

            if ($original_slug && $original_slug !== $new_slug && isset($custom_post_types[$original_slug])) {
                unset($custom_post_types[$original_slug]);
            }

            $custom_post_types[$new_slug] = array(
                'slug' => $new_slug,
                'singular' => $singular_label,
                'plural' => $plural_label,
                'description' => $description,
                'public' => $public,
                'exclude_from_search' => $exclude_from_search,
                'menu_position' => $menu_position,
                'supports' => $supports,
            );

            update_option('custom_post_types', $custom_post_types);

            flush_rewrite_rules();

            wp_redirect(admin_url('admin.php?page=cpts&status=success'));
            exit;
        }
    }

    // Load custom post types from the options table and register them
    public static function load_custom_post_types() {
        $custom_post_types = get_option('custom_post_types', []);

        // Array to hold transformed data for the table
        $transformed_data = [];

        if (!empty($custom_post_types)) {
            foreach ($custom_post_types as $slug => $post_type_data) {
                // Register the post type
                $args = [
                    'labels' => [
                        'name'          => $post_type_data['plural'] ?? ucfirst($slug) . 's',
                        'singular_name' => $post_type_data['singular'] ?? ucfirst($slug),
                    ],
                    'description'        => $post_type_data['description'] ?? '',
                    'public'             => $post_type_data['public'] ?? true,
                    'exclude_from_search' => $post_type_data['exclude_from_search'] ?? false,
                    'menu_position'      => $post_type_data['menu_position'] ?? null,
                    'supports'           => $post_type_data['supports'] ?? ['title', 'editor'],
                    'has_archive'        => true,
                    'rewrite'            => ['slug' => $slug],
                    'show_in_menu'       => true,
                ];
                register_post_type($slug, $args);

                // Transform data for the table
                $transformed_data[$slug] = [
                    'slug'        => $slug, // Ensure the slug key exists
                    'singular'    => $post_type_data['singular'] ?? ucfirst($slug), // Map singular_label to singular
                    'plural'      => $post_type_data['plural'] ?? ucfirst($slug) . 's', // Map plural_label to plural
                    'description' => $post_type_data['description'] ?? '', // Use the description key as-is
                    'public'      => isset($post_type_data['public']) ? (bool)$post_type_data['public'] : true, // Default public to true
                    'exclude_from_search' => isset($post_type_data['exclude_from_search']) ? (bool)$post_type_data['exclude_from_search'] : false, // Default exclude_from_search to false
                ];
            }
        }
        
        update_option('custom_post_types_table_view', $transformed_data);
    }


    // Delete Custom Post Type
    public static function delete_custom_post_type() {
        if (!isset($_GET['slug']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_custom_post_type_' . $_GET['slug'])) {
            wp_die(__('Invalid request', 'cpts'), __('Error', 'cpts'), ['response' => 403]);
        }

        $slug = sanitize_text_field($_GET['slug']);
        $custom_post_types = get_option('custom_post_types', []);

        if (isset($custom_post_types[$slug])) {
            unset($custom_post_types[$slug]);
            update_option('custom_post_types', $custom_post_types);
            flush_rewrite_rules();
        }

        wp_redirect(admin_url('admin.php?page=cpts'));
        exit;
    }


//transform the imported CTP for table in the exiting CTP
    public function transform_ctps() {
        $custom_post_types = get_option('custom_post_types', []);

        $transformed_data = [];

        foreach ($custom_post_types as $slug => $details) {
            $transformed_data[$slug] = [
                'slug'        => $slug,
                'singular'    => $details['singular_label'] ?? ucfirst($slug),
                'plural'      => $details['plural_label'] ?? ucfirst($slug) . 's',
                'description' => $details['description'] ?? '',
                'public'      => isset($details['public']) ? (bool) $details['public'] : true,
                'exclude_from_search' => isset($details['exclude_from_search']) ? (bool) $details['exclude_from_search'] : false,
            ];
        }

        return $transformed_data;
    }
}