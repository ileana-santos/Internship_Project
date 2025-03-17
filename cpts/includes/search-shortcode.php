<?php
if (!defined('ABSPATH')) {
    exit;
}

class Search_Builder {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_search_builder_menu'));
        add_action('admin_init', array($this, 'register_search_builder_settings'));

        add_action('pre_get_posts', function ($query) {
            if (!is_admin() && $query->is_main_query()) {
                if (!empty($_GET['post_type'])) {
                    $query->set('post_type', sanitize_text_field($_GET['post_type']));
                }
                if (!empty($_GET['taxonomy'])) {
                    $tax_query = [];
                    foreach ($_GET['taxonomy'] as $taxonomy => $term_id) {
                        if ($term_id !== '0') {
                            $tax_query[] = [
                                'taxonomy' => sanitize_text_field($taxonomy),
                                'field'    => 'id',
                                'terms'    => intval($term_id),
                            ];
                        }
                    }
                    if (!empty($tax_query)) {
                        $query->set('tax_query', $tax_query);
                    }
                }
            }
        });
    }

    public function add_search_builder_menu() {
        add_submenu_page(
            'cpts',              // Parent slug
            'Search Builder',           // Page title
            'Search Builder',           // Menu title
            'manage_options',           // Capability
            'search_builder',    // Menu slug
            array($this, 'render_search_builder_page') // Callback
        );
    }

    public function register_search_builder_settings() {
        register_setting(
            'search_builder_settings', // Option group
            'search_builder_options'  // Option name
        );
    }

    public function render_search_builder_page() {
        // Fetch saved settings
        $searchable_options = get_option('cpts_searchable_options', []);
        $filterable_taxonomy_options = get_option('cpts_taxonomy_options', []);
        $search_builder_options = get_option('search_builder_options', []);

        $post_types = array_filter(
            get_post_types(['public' => true, '_builtin' => false], 'objects'),
            function ($post_type) use ($searchable_options) {
                return isset($searchable_options[$post_type->name]) && $searchable_options[$post_type->name] == 1;
            }
        );

        $taxonomies = [];
        foreach ($post_types as $post_type) {
            $related_taxonomies = get_object_taxonomies($post_type->name, 'objects');

            foreach ($related_taxonomies as $taxonomy) {
                if (isset($filterable_taxonomy_options[$taxonomy->name]) && $filterable_taxonomy_options[$taxonomy->name] == 1) {
                    $taxonomies[$taxonomy->name] = $taxonomy;
                }
            }
        }

        // Generate shortcode
        $selected_post_types = array_keys($search_builder_options['post_types'] ?? []);
        $selected_taxonomies = array_keys($search_builder_options['taxonomies'] ?? []);
        $posts_per_page = $search_builder_options['posts_per_page'] ?? 10;

        $shortcode_atts = [
            'post_types' => implode(',', $selected_post_types),
            'taxonomies' => implode(',', $selected_taxonomies),
            'posts_per_page' => $posts_per_page,
        ];
        $generated_shortcode = '[search_builder ' . htmlspecialchars(http_build_query
            ($shortcode_atts, '', ' ')) . ']';

        ?>
        <div class="wrap">
            <h1>Search Builder</h1>
            <form method="post" action="options.php">
                <?php settings_fields('search_builder_settings'); ?>

                <h3>Select Post Types</h3>
                <div class="row">
                    <?php foreach ($post_types as $post_type): ?>
                        <div class="col-md-2">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    name="search_builder_options[post_types][<?php echo esc_attr($post_type->name); ?>]"
                                    id="post_type_<?php echo esc_attr($post_type->name); ?>"
                                    value="1"
                                    <?php checked(isset($search_builder_options['post_types'][$post_type->name])); ?>
                                >
                                <label class="form-check-label" for="post_type_<?php echo esc_attr($post_type->name); ?>">
                                    <?php echo esc_html($post_type->label); ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3>Filterable Taxonomies</h3>
                <div class="row">
                    <?php foreach ($taxonomies as $taxonomy): ?>
                        <div class="col-md-2">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    name="search_builder_options[taxonomies][<?php echo esc_attr($taxonomy->name); ?>]"
                                    id="taxonomy_<?php echo esc_attr($taxonomy->name); ?>"
                                    value="1"
                                    <?php checked(isset($search_builder_options['taxonomies'][$taxonomy->name])); ?>
                                >
                                <label class="form-check-label" for="taxonomy_<?php echo esc_attr($taxonomy->name); ?>">
                                    <?php echo esc_html($taxonomy->label); ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3>Number of Results Per Page</h3>
                <input
                    type="number"
                    name="search_builder_options[posts_per_page]"
                    value="<?php echo esc_attr($posts_per_page); ?>"
                    class="form-control"
                >

                <h3>Generated Shortcode</h3>
                <p>
                    <code><?php echo esc_html($generated_shortcode); ?></code>
                </p>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=search_builder')); ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php
    }
}

new Search_Builder();

class Search_Shortcode {

    public function __construct() {
        add_shortcode('search_builder', array($this, 'render_search_form'));
    }

    public function render_search_form($atts) {
        $builder_settings = get_option('search_builder_options', []);
        $post_types = $builder_settings['post_types'] ?? [];
        $taxonomies = $builder_settings['taxonomies'] ?? [];
        $posts_per_page = $builder_settings['posts_per_page'] ?? 10;

        ob_start();
        ?>
        <form method="get" id="search-builder-form" class="search-builder-form">
            <!-- Search Bar -->
            <div class="d-flex justify-content-center align-items-center mt-3">
                <div class="input-group" style="width: 100%;">
                    <input
                            type="text"
                            class="form-control"
                            name="s"
                            placeholder="Search..."
                            aria-label="Search"
                            style="border-radius: 15px;"
                            value="<?php echo esc_attr(get_query_var('s', '')); ?>"
                    >
                </div>
            </div>

            <!-- Post Types and Taxonomies -->
            <div class="row mt-6">
                <?php if (!empty($post_types)): ?>
                    <div class="col-md-3">
                        <label for="post_type"></label>
                        <select name="post_type" id="post_type" class="form-control">
                            <option value="">All</option>
                            <?php foreach ($post_types as $post_type => $enabled): ?>
                                <option value="<?php echo esc_attr($post_type); ?>" <?php selected(get_query_var('post_type'), $post_type); ?>>
                                    <?php echo esc_html(get_post_type_object($post_type)->label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php foreach ($taxonomies as $taxonomy => $enabled): ?>
                    <div class="col-md-3">
                        <label for="taxonomy_<?php echo esc_attr($taxonomy); ?>">
                            <?php echo esc_html(get_taxonomy($taxonomy)->label); ?>
                        </label>
                        <?php
                        wp_dropdown_categories([
                            'taxonomy'         => $taxonomy,
                            'name'             => "taxonomy[$taxonomy]",
                            'id'               => "taxonomy_$taxonomy",
                            'show_option_all'  => 'All',
                            'class'            => 'form-control',
                            'selected'         => get_query_var('taxonomy')[$taxonomy] ?? '',
                        ]);
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Search and Reset Buttons -->
            <div class="d-flex gap-2 mt-3 justify-content-left">
                <button type="button" id="search-submit" class="btn btn-primary">Search</button>
                <button type="button" id="search-reset" class="btn btn-secondary">Reset</button>
            </div>
        </form>
        <div id="search-results"></div>
        <?php
        return ob_get_clean();
    }
}

new Search_Shortcode();