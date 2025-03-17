<div class="wrap">
    <h1 class="mb-4"><?php esc_html_e('Export/Import Settings', 'cpts'); ?></h1>
    <div class="container">
        <div class="row">
            <!-- Export Settings Section -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header text-black">
                        <h2 class="h5 mb-0"><?php esc_html_e('Export Settings', 'cpts'); ?></h2>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('export_import', 'export_import_nonce'); ?>
                            <div class="form-group mb-3">
                                <label for="post_types" class="form-label"><?php esc_html_e('Select Post Types', 'cpts'); ?></label>
                                <select name="post_types[]" id="post_types" class="form-select" multiple>
                                    <?php foreach ($post_types as $post_type): ?>
                                        <option value="<?php echo esc_attr($post_type->name); ?>">
                                            <?php echo esc_html($post_type->label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted"><?php esc_html_e('Hold Ctrl (or Cmd) to select multiple post types.', 'cpts'); ?></small>
                            </div>
                            <button type="submit" name="action" value="export_settings" class="btn btn-primary">
                                <?php esc_html_e('Export', 'cpts'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Import Settings Section -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header text-black">
                        <h2 class="h5 mb-0"><?php esc_html_e('Import Settings', 'cpts'); ?></h2>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                            <?php wp_nonce_field('export_import', 'export_import_nonce'); ?>
                            <div class="form-group mb-3">
                                <label for="import_file" class="form-label"><?php esc_html_e('Select File', 'cpts'); ?></label>
                                <input type="file" name="import_file" id="import_file" class="form-control form-control-lg w-100" accept=".json" required>
                            </div>
                            <button type="submit" name="action" value="import_settings" class="btn btn-success">
                                <?php esc_html_e('Import', 'cpts'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
