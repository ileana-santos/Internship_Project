<?php
class TaxonomyHandler {

    public function __construct(){
        add_action('admin_post_save_taxonomy', ['TaxonomyHandler', 'save_taxonomy']);
        add_action('admin_post_edit_taxonomy', ['TaxonomyHandler', 'edit_taxonomy']);
        add_action('admin_post_delete_custom_taxonomy', ['TaxonomyHandler', 'delete_custom_taxonomy']);
    }
    public static function register() {
        $taxonomies = get_option('custom_taxonomies', array());

        if (is_array($taxonomies) && !empty($taxonomies)) {
            foreach ($taxonomies as $taxonomy_slug => $taxonomy) {
                $labels = array(
                    'name'              => $taxonomy['plural'],
                    'singular_name'     => $taxonomy['singular'],
                    'search_items'      => 'Search ' . $taxonomy['plural'],
                    'all_items'         => 'All ' . $taxonomy['plural'],
                    'edit_item'         => 'Edit ' . $taxonomy['singular'],
                    'update_item'       => 'Update ' . $taxonomy['singular'],
                    'add_new_item'      => 'Add New ' . $taxonomy['singular'],
                    'new_item_name'     => 'New ' . $taxonomy['singular'] . ' Name',
                    'menu_name'         => $taxonomy['plural'],
                );

                register_taxonomy($taxonomy_slug, $taxonomy['associated_post_type'], array(
                    'labels'        => $labels,
                    'hierarchical'  => true,
                    'public'        => true,
                    'show_ui'       => true,
                    'show_in_menu'  => true,
                    'show_in_rest'  => true,
                ));
            }
        }
    }
    public static function save_taxonomy() {
        if (!isset($_POST['save_taxonomy_nonce']) || !wp_verify_nonce($_POST['save_taxonomy_nonce'], 'save_taxonomy_action')) {
            wp_die(__('Invalid nonce specified', 'cpts'), __('Error', 'cpts'), array('response' => 403));
        }

        if (isset($_POST['taxonomy_slug'], $_POST['taxonomy_singular'], $_POST['taxonomy_plural'], $_POST['associated_post_type'])) {
            $taxonomy_slug = sanitize_text_field($_POST['taxonomy_slug']);
            $custom_taxonomies = get_option('custom_taxonomies', array());

            if (array_key_exists($taxonomy_slug, $custom_taxonomies) || post_type_exists($taxonomy_slug)) {
                wp_redirect(add_query_arg('error', 'slug_exists', admin_url('admin.php?page=cpts')));
                exit;
            }

            $singular_label = sanitize_text_field($_POST['taxonomy_singular']);
            $plural_label = sanitize_text_field($_POST['taxonomy_plural']);
            $associated_post_type = sanitize_text_field($_POST['associated_post_type']);

            if (isset($_POST['original_taxonomy_slug'])) {
                $original_slug = sanitize_text_field($_POST['original_taxonomy_slug']);

                if (isset($custom_taxonomies[$original_slug])) {
                    unset($custom_taxonomies[$original_slug]);
                }
            }

            $custom_taxonomies[$taxonomy_slug] = array(
                'slug' => $taxonomy_slug,
                'singular' => $singular_label,
                'plural' => $plural_label,
                'associated_post_type' => $associated_post_type,
            );

            update_option('custom_taxonomies', $custom_taxonomies);

            flush_rewrite_rules();

            wp_redirect(admin_url('admin.php?page=taxonomies&status=success'));
            exit;
        }
    }
    public static function edit_taxonomy() {
        // Validate the nonce
        if (!isset($_POST['save_taxonomy_nonce']) || !wp_verify_nonce($_POST['save_taxonomy_nonce'], 'save_taxonomy_action')) {
            wp_die(__('Invalid nonce specified', 'cpts'), __('Error', 'cpts'), ['response' => 403]);
        }

        // Ensure all required fields are provided
        if (empty($_POST['taxonomy_slug']) || empty($_POST['taxonomy_singular']) || empty($_POST['taxonomy_plural']) || empty($_POST['associated_post_type']) || empty($_POST['original_taxonomy_slug'])) {
            wp_die(__('Missing required fields', 'cpts'), __('Error', 'cpts'), ['response' => 400]);
        }

        // Sanitize input
        $new_slug = sanitize_text_field($_POST['taxonomy_slug']);
        $original_slug = sanitize_text_field($_POST['original_taxonomy_slug']);
        $singular_label = sanitize_text_field($_POST['taxonomy_singular']);
        $plural_label = sanitize_text_field($_POST['taxonomy_plural']);
        $associated_post_type = sanitize_text_field($_POST['associated_post_type']);

        // Retrieve existing taxonomies
        $custom_taxonomies = get_option('custom_taxonomies', []);

        // Handle slug change
        if (isset($custom_taxonomies[$original_slug])) {
            if ($new_slug !== $original_slug && (isset($custom_taxonomies[$new_slug]) || taxonomy_exists($new_slug))) {
                wp_redirect(add_query_arg('error', 'slug_exists', admin_url('admin.php?page=taxonomies')));
                exit;
            }

            // Remove the old taxonomy if slug has changed
            if ($new_slug !== $original_slug) {
                unset($custom_taxonomies[$original_slug]);
            }
        }

        // Update or add taxonomy
        $custom_taxonomies[$new_slug] = [
            'slug' => $new_slug,
            'singular' => $singular_label,
            'plural' => $plural_label,
            'associated_post_type' => $associated_post_type,
        ];

        // Save changes
        update_option('custom_taxonomies', $custom_taxonomies);

        // Register the updated taxonomy
        register_taxonomy($new_slug, $associated_post_type, [
            'labels' => [
                'name' => $plural_label,
                'singular_name' => $singular_label,
            ],
            'public' => true,
            'hierarchical' => true,
        ]);

        
        flush_rewrite_rules();

        
        wp_redirect(admin_url('admin.php?page=taxonomies&status=success'));
        exit;
    }

    // Delete Custom Taxonomy
    public static function delete_custom_taxonomy() {
        if (!isset($_GET['slug']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_custom_taxonomy_' . $_GET['slug'])) {
            wp_die(__('Invalid request', 'cpts'), __('Error', 'cpts'), ['response' => 403]);
        }

        $slug = sanitize_text_field($_GET['slug']);
        $custom_taxonomies = get_option('custom_taxonomies', []);

        if (isset($custom_taxonomies[$slug])) {
            unset($custom_taxonomies[$slug]);
            update_option('custom_taxonomies', $custom_taxonomies);
            flush_rewrite_rules();
        }

        wp_redirect(admin_url('admin.php?page=taxonomies'));
        exit;
    }

    public function load_custom_taxonomies() {
        $custom_taxonomies = get_option('custom_taxonomies', array());
        if (!empty($custom_taxonomies)) {
            foreach ($custom_taxonomies as $slug => $taxonomy_data) {
                $args = array(
                    'labels' => array(
                        'name' => $taxonomy_data['plural'],
                        'singular_name' => $taxonomy_data['singular']
                    ),
                    'public' => true,
                    'rewrite' => array('slug' => $slug),
                    'show_in_menu' => true,
                );
                register_taxonomy($slug, $taxonomy_data['associated_post_type'], $args);
            }
        }
    }
}