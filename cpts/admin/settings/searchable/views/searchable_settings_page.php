<?php
if (!defined('ABSPATH')) {
    exit;
}

// Function to render the Searchable & Filterable Settings page.
function render_searchable_settings_page() {
    ?>
    <div class="wrap">
        <h1 class="mb-4">Searchable & Filterable Settings</h1>

        <!-- Form for Searchable Post Types -->
        <form method="post" action="options.php" class="bg-light p-4 rounded shadow mb-4">
            <?php
            settings_fields('cpts_searchable_settings_group');
            $searchable_options = get_option('cpts_searchable_options', []);
            $post_types = get_post_types(['public' => true, '_builtin' => false], 'objects');
            ?>
            <h3>Searchable Post Types</h3>
            <div class="row">
                <?php foreach ($post_types as $post_type): ?>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="cpts_searchable_options[<?php echo esc_attr($post_type->name); ?>]"
                                id="searchable_<?php echo esc_attr($post_type->name); ?>"
                                value="1"
                                <?php checked(1, isset($searchable_options[$post_type->name]) ? $searchable_options[$post_type->name] : 0); ?>
                            >
                            <label class="form-check-label" for="searchable_<?php echo esc_attr($post_type->name); ?>">
                                <?php echo esc_html($post_type->label); ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cpts_searchable')); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        <!-- Form for Filterable Fields -->
        <form method="post" action="options.php" class="bg-light p-4 rounded shadow mb-4">
            <?php
            settings_fields('cpts_filterable_settings_group');
            $filterable_options = get_option('cpts_filterable_options', []);
            ?>
            <h3>Filterable Fields for Custom Post Types</h3>
            <div class="row">
                <?php foreach ($post_types as $post_type): ?>
                    <div class="col-md-2">
                        <h4><?php echo esc_html($post_type->label); ?></h4>
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="cpts_filterable_options[<?php echo esc_attr($post_type->name); ?>][title]"
                                id="filter_title_<?php echo esc_attr($post_type->name); ?>"
                                value="1"
                                <?php checked(1, isset($filterable_options[$post_type->name]['title']) ? $filterable_options[$post_type->name]['title'] : 0); ?>
                            >
                            <label class="form-check-label" for="filter_title_<?php echo esc_attr($post_type->name); ?>">Title</label>
                        </div>
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="cpts_filterable_options[<?php echo esc_attr($post_type->name); ?>][author]"
                                id="filter_author_<?php echo esc_attr($post_type->name); ?>"
                                value="1"
                                <?php checked(1, isset($filterable_options[$post_type->name]['author']) ? $filterable_options[$post_type->name]['author'] : 0); ?>
                            >
                            <label class="form-check-label" for="filter_author_<?php echo esc_attr($post_type->name); ?>">Author</label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cpts_searchable')); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        <!-- Form for Filterable Taxonomies -->
        <form method="post" action="options.php" class="bg-light p-4 rounded shadow">
            <?php
            settings_fields('cpts_taxonomy_settings_group');
            $taxonomy_options = get_option('cpts_taxonomy_options', []);
            $taxonomies = get_taxonomies(['public' => true], 'objects');
            ?>
            <h3>Filterable Taxonomies</h3>
            <div class="row">
                <?php foreach ($taxonomies as $taxonomy): ?>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="cpts_taxonomy_options[<?php echo esc_attr($taxonomy->name); ?>]"
                                id="filter_taxonomy_<?php echo esc_attr($taxonomy->name); ?>"
                                value="1"
                                <?php checked(1, isset($taxonomy_options[$taxonomy->name]) ? $taxonomy_options[$taxonomy->name] : 0); ?>
                            >
                            <label class="form-check-label" for="filter_taxonomy_<?php echo esc_attr($taxonomy->name); ?>">
                                <?php echo esc_html($taxonomy->label); ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cpts_searchable')); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php
}
