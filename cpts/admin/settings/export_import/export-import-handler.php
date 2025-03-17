<?php
class Export_Import_Handler {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_export_import_menu'));
        add_action('admin_post_export_settings', [$this, 'export_settings']);
        add_action('admin_post_import_settings', [$this, 'import_settings']);
        add_action('init', [$this, 'register_all_imported_post_types']);
    }


    public function add_export_import_menu() {
        add_submenu_page(
            'cpts',
            'Export/Import Settings',
            'Export/Import',
            'manage_options',
            'export-import',
            array($this, 'render_export_import_page')
        );
    }

    public function render_export_import_page() {
        $post_types = get_post_types(['public' => true], 'objects'); // Fetch public custom post types
        include plugin_dir_path(__FILE__) . 'view/export_import_page.php';
    }

    public function export_settings() {

        if (!current_user_can('manage_options') || !check_admin_referer('export_import', 'export_import_nonce')) {
            wp_die(__('Unauthorized request.', 'cpts'));
        }

        $selected_post_types = $_POST['post_types'] ?? [];
        if (empty($selected_post_types)) {
            wp_die(__('No post types selected.', 'cpts'));
        }

        $export_data = [];
        foreach ($selected_post_types as $post_type) {
            $query = new WP_Query([
                'post_type' => $post_type,
                'posts_per_page' => -1,
            ]);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $export_data[$post_type][] = [
                        'ID'         => get_the_ID(),
                        'title'      => get_the_title(),
                        'content'    => get_the_content(),
                        'meta'       => get_post_meta(get_the_ID()),
                        'taxonomy'   => wp_get_post_terms(get_the_ID(), get_object_taxonomies($post_type)),
                    ];
                }
            }
            wp_reset_postdata();
        }

        $json = json_encode($export_data, JSON_PRETTY_PRINT);
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="cpts-export.json"');
        echo $json;
        exit;
    }

    public function import_settings() {
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            wp_die(__('Failed to upload file.', 'cpts'));
        }

        $file_contents = file_get_contents($_FILES['import_file']['tmp_name']);
        $imported_data = json_decode($file_contents, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($imported_data)) {
            wp_die(__('Invalid JSON file.', 'cpts'));
        }

        $existing_data = get_option('custom_post_types', []);
        $transformed_data = [];
        foreach ($imported_data as $slug => $data) {
            $transformed_data[$slug] = [
                'singular_label'     => sanitize_text_field($data['singular_label'] ?? ucfirst($slug)),
                'plural_label'       => sanitize_text_field($data['plural_label'] ?? ucfirst($slug) . 's'),
                'description'        => sanitize_textarea_field($data['description'] ?? ''),
                'public'             => isset($data['public']) ? (bool)$data['public'] : true,
                'exclude_from_search' => isset($data['exclude_from_search']) ? (bool)$data['exclude_from_search'] : false,
            ];
        }

        $merged_data = array_merge($existing_data, $transformed_data);
        update_option('custom_post_types', $merged_data);

        wp_redirect(admin_url('admin.php?page=cpts&import=success'));
        exit;
    }

    public function register_imported_post_type($post_type, $data) {
        $args = [
            'labels' => [
                'name'          => $data['plural_label'],
                'singular_name' => $data['singular_label'],
            ],
            'public'              => $data['public'] ?? true,
            'has_archive'         => $data['has_archive'] ?? true,
            'show_in_rest'        => $data['show_in_rest'] ?? true,
            'exclude_from_search' => $data['exclude_from_search'] ?? false,
            'rewrite'             => ['slug' => $post_type],
        ];

        register_post_type($post_type, $args);
    }

    public function register_all_imported_post_types() {
        $custom_post_types = get_option('custom_post_types', []);

        foreach ($custom_post_types as $post_type => $data) {
            if (isset($data['singular_label'], $data['plural_label'])) {
                $this->register_imported_post_type($post_type, $data);
            }
        }
    }
}
