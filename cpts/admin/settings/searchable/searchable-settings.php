<?php
if (!defined('ABSPATH')) {
    exit;
}

class Searchable_Settings {
    public function __construct() {
        add_action('admin_init', array($this, 'register_searchable_settings'));
        add_action('admin_init', array($this, 'register_all_settings'));

    }



    // Register settings and fields
    public function register_searchable_settings() {
        register_setting(
            'cpts_searchable_settings_group',
            'cpts_searchable_options'
        );

        add_settings_section(
            'cpts_searchable_section',
            'Searchable Post Types Settings',
            null,
            'cpts_searchable'
        );

        add_settings_field(
            'cpts_searchable_field',
            'Searchable Post Types',
            array($this, 'render_searchable_field'),
            'cpts_searchable',
            'cpts_searchable_section'
        );
    }

    public function render_searchable_field() {
        $options = get_option('cpts_searchable_options', []);
        $post_types = get_post_types(['public' => true], 'objects');

        echo '<div class="row g-3">';
        foreach ($post_types as $post_type) {
            echo '<div class="col-md-4">';
            echo '<div class="form-check">';
            echo '<input 
            class="form-check-input" 
            type="checkbox" 
            name="cpts_searchable_options[' . esc_attr($post_type->name) . ']" 
            id="post_type_' . esc_attr($post_type->name) . '" 
            value="1" 
            ' . checked(1, isset($options[$post_type->name]) ? $options[$post_type->name] : 0, false) . '>';
            echo '<label class="form-check-label" for="post_type_' . esc_attr($post_type->name) . '">'
                . esc_html($post_type->label) .
                '</label>';

            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
        echo '<div class="d-flex gap-2 mt-3">';
        echo '<button type="submit" class="btn btn-primary">Save Changes</button>';
        echo '<a href="' . esc_url(admin_url('admin.php?page=cpts')) . '" class="btn btn-secondary">Cancel</a>';
        echo '</div>';
    }

    public function register_filterable_settings() {
        register_setting(
            'cpts_filterable_settings_group',
            'cpts_filterable_options'
        );

        add_settings_section(
            'cpts_filterable_section',
            'Filterable Fields Settings',
            null,
            'cpts_filterable'
        );

        add_settings_field(
            'cpts_filterable_field',
            'Filterable Fields for Custom Post Types',
            array($this, 'render_filterable_field'),
            'cpts_filterable',
            'cpts_filterable_section'
        );
    }

    public function render_filterable_field() {
        $options = get_option('cpts_filterable_options', []);
        $post_types = get_post_types(['public' => true], 'objects');

        echo '<div class="row g-3">';
        foreach ($post_types as $post_type) {
            echo '<div class="col-md-6">';
            echo '<h4>' . esc_html($post_type->label) . '</h4>';
            echo '<div class="form-check">';
            echo '<input type="checkbox" class="form-check-input" name="cpts_filterable_options[' . esc_attr($post_type->name) .
                '][title]" value="1" ' . checked(1, isset($options[$post_type->name]['title']) ? $options[$post_type->name]['title'] : 0, false) . '>';
            echo '<label class="form-check-label">Title</label>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    public function register_taxonomy_settings() {
        register_setting(
            'cpts_taxonomy_settings_group',
            'cpts_taxonomy_options'
        );

        add_settings_section(
            'cpts_taxonomy_section',
            'Filterable Taxonomies Settings',
            null,
            'cpts_taxonomy'
        );

        add_settings_field(
            'cpts_taxonomy_field',
            'Filterable Taxonomies',
            array($this, 'render_taxonomy_field'),
            'cpts_taxonomy',
            'cpts_taxonomy_section'
        );
    }

    public function render_taxonomy_field() {
        $options = get_option('cpts_taxonomy_options', []);
        $post_types = get_post_types(['public' => true], 'objects');

        echo '<div class="row g-3">';
        foreach ($post_types as $post_type) {
            $taxonomies = get_object_taxonomies($post_type->name, 'objects');
            if (!empty($taxonomies)) {
                echo '<div class="col-md-6">';
                echo '<h4>' . esc_html($post_type->label) . ' Taxonomies</h4>';
                foreach ($taxonomies as $taxonomy) {
                    echo '<div class="form-check">';
                    echo '<input type="checkbox" class="form-check-input" name="cpts_taxonomy_options[' . esc_attr($taxonomy->name) . ']" value="1" ' .
                        checked(1, isset($options[$taxonomy->name]) ? $options[$taxonomy->name] : 0, false) . '>';
                    echo '<label class="form-check-label">' . esc_html($taxonomy->label) . '</label>';
                    echo '</div>';
                }
                echo '</div>';
            }
        }
        echo '</div>';
    }

    public function register_all_settings() {
        $this->register_searchable_settings();
        $this->register_filterable_settings();
        $this->register_taxonomy_settings();
    }
}

