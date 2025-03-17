<?php
class CPT_Menu {

    public function __construct() {
        require_once plugin_dir_path(__FILE__) . '../includes/taxonomy-handler.php';
        require_once plugin_dir_path(__FILE__) . 'post-type-settings.php';
        require_once plugin_dir_path(__FILE__) . '/settings/searchable/views/searchable_settings_page.php';

        add_action('admin_notices', array($this, 'cpts_display_error_notice'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('init', array('CPTRegister', 'register_post_type'));
        add_action('admin_post_save_custom_post_type', ['CPTRegister', 'save_custom_post_type']);
        add_action('admin_post_edit_custom_post_type', ['CPTRegister', 'edit_custom_post_type']);
        add_action('admin_post_delete_custom_post_type', ['CPTRegister', 'delete_custom_post_type']);
        add_action('admin_post_save_taxonomy', ['TaxonomyHandler', 'save_taxonomy']);
        add_action('admin_post_edit_taxonomy', ['TaxonomyHandler', 'edit_taxonomy']);
        add_action('admin_post_delete_custom_taxonomy', ['TaxonomyHandler', 'delete_custom_taxonomy']);
        add_action('init', function() {
            $custom_post_types = get_option('custom_post_types_table_view', array());
            foreach ($custom_post_types as $slug => $post_type_data) {
                CPTRegister::register_post_type($slug, array(
                    'name' => $post_type_data['plural'],
                    'singular_name' => $post_type_data['singular'],
                ));
            }
        });

        //bootstrap
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles_and_scripts'));

        add_action('init', function () {
            $settings = get_option('cpts_settings', []);

            foreach ($settings as $post_type => $post_type_data) {
                $args = [
                    'labels' => [
                        'name'          => $post_type_data['plural_label'],
                        'singular_name' => $post_type_data['singular_label'],
                    ],
                    'public'              => $post_type_data['public'] ?? true,
                    'has_archive'         => true,
                    'show_in_rest'        => true,
                    'exclude_from_search' => $post_type_data['exclude_from_search'] ?? false,
                    'rewrite'             => ['slug' => $post_type],
                ];

                register_post_type($post_type, $args);
            }
        });
    }

    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            'CPTs',
            'CPTs',
            'manage_options',
            'cpts',
            array($this, 'render_ctps')
        );

        // Submenu for Taxonomies
        add_submenu_page(
            'cpts',
            'Manage Taxonomies',
            'Taxonomies',
            'manage_options',
            'taxonomies',
            array($this, 'render_taxonomies_page')
        );

        // Submenu for Searchable Settings
        add_submenu_page(
            'cpts',
            'Searchable Settings',
            'Searchable Settings',
            'manage_options',
            'cpts_searchable',
           'render_searchable_settings_page'
        );
    }

    public function enqueue_admin_styles_and_scripts() {
        // Enqueue Bootstrap CSS
        wp_enqueue_style(
            'bootstrap-css',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css'
        );

        // Enqueue Bootstrap JS
        wp_enqueue_script(
            'bootstrap-js',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js',
            array('jquery'),
            null,
            true
        );
    }

// Function to render the Custom Post Types page
    public function render_ctps() {
        include plugin_dir_path(__FILE__) . 'settings/ctp-setting.php';
    }

    // Function to render the Taxonomies page
    public function render_taxonomies_page() {
        include plugin_dir_path(__FILE__) . 'settings/taxonomies.php';
    }

    function cpts_display_error_notice() {
        // Check if the 'error' parameter is set in the URL
        if (isset($_GET['error']) && $_GET['error'] === 'slug_exists') {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>Error:</strong> The slug you entered is not available. Please choose a different slug.</p>';
            echo '</div>';
        }
    }

}

new CPT_Menu();