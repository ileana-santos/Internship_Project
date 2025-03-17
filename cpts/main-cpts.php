<?php
/**
 * Plugin Name: CPTS
 * Description: A plugin for creating searchable and filterable custom post types and taxonomies.
 * Version: 1.0
 * Author: Ileana Santos
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MainCustomPostTypes {

    public function __construct() {
        // Initialize hooks for Custom Post Types and Taxonomies
        add_action('init', array($this, 'load_custom_post_types'));
        add_action('init', array($this, 'load_custom_taxonomies'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_custom_admin_css'));


        // Include other files
        require_once plugin_dir_path(__FILE__) . 'admin/menu.php';
        require_once plugin_dir_path(__FILE__) . 'admin/post-type-settings.php';
        require_once plugin_dir_path(__FILE__) . 'includes/taxonomy-handler.php';
        require_once plugin_dir_path(__FILE__) . 'admin/settings/searchable/searchable-settings.php';
        require_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';
        require_once plugin_dir_path(__FILE__) . 'includes/search-shortcode.php';
        require_once plugin_dir_path(__FILE__) . 'admin/settings/export_import/export-import-handler.php';
        require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
    }

    // Load custom post types from the options table and register them
    public function load_custom_post_types() {
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
                    'singular'    => $post_type_data['singular'] ?? ucfirst($slug),
                    'plural'      => $post_type_data['plural'] ?? ucfirst($slug) . 's',
                    'description' => $post_type_data['description'] ?? '',
                    'public'      => isset($post_type_data['public']) ? (bool)$post_type_data['public'] : true, // Default public to true
                    'exclude_from_search' => isset($post_type_data['exclude_from_search']) ? (bool)$post_type_data['exclude_from_search'] : false, // Default exclude_from_search to false
                ];
            }
        }

        // Store the transformed data for the table
        update_option('custom_post_types_table_view', $transformed_data);
    }

    // Load custom taxonomies from the options table and register them
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

    public function enqueue_custom_admin_css() {
        wp_enqueue_style('custom-css', plugin_dir_url(__FILE__) . 'css/admin-style.css');
    }
}

// Initialize the plugin class
if (class_exists('MainCustomPostTypes')) {
    new MainCustomPostTypes();
}

// Initialize the class
if (class_exists('Searchable_Settings')) {
    new Searchable_Settings();
}

if (class_exists('CPT_Shortcode')) {
    new CPT_Shortcode();
}

if (class_exists('Export_Import_Handler')) {
    new Export_Import_Handler();
}