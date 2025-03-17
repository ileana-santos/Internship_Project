<?php
if (!defined('ABSPATH')) {
    exit; 
}

$custom_post_types = get_option('custom_post_types_table_view', array());
$editing = isset($_GET['edit_post_type']);
$post_type_data = $editing ? $custom_post_types[$_GET['edit_post_type']] ?? [] : [];

// Render the page
?>
<div class="wrap">
    <h1>Manage Custom Post Types</h1>
    <h2><?php echo $editing ? 'Edit' : 'Create'; ?> Custom Post Type</h2>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="<?php echo $editing ? 'edit_custom_post_type' : 'save_custom_post_type'; ?>">
            <?php wp_nonce_field('save_custom_post_type_action', 'save_custom_post_type_nonce'); ?>
            <input type="hidden" name="original_post_type_slug" value="<?php echo esc_attr($_GET['edit_post_type'] ?? ''); ?>" />
            <table class="form-table">
                <tr>
                    <th scope="row">Post Type Slug</th>
                    <td><input type="text" name="custom_post_type_slug" value="<?php echo esc_attr($post_type_data['slug']); ?>" required></td>
                </tr>
                <tr>
                    <th scope="row">Singular Label</th>
                    <td><input type="text" name="custom_post_type_singular" value="<?php echo esc_attr($post_type_data['singular']); ?>" required></td>
                </tr>
                <tr>
                    <th scope="row">Plural Label</th>
                    <td><input type="text" name="custom_post_type_plural" value="<?php echo esc_attr($post_type_data['plural']); ?>" required></td>
                </tr>
                <tr>
                    <th scope="row">Description</th>
                    <td><textarea name="custom_post_type_description"><?php echo esc_attr($post_type_data['description'] ?? ''); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row">Public</th>
                    <td>
                        <select name="custom_post_type_public">
                            <option value="1" <?php selected($post_type_data['public'] ?? '', '1'); ?>>Yes</option>
                            <option value="0" <?php selected($post_type_data['public'] ?? '', '0'); ?>>No</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Exclude from Search</th>
                    <td>
                        <select name="custom_post_type_exclude_search">
                            <option value="0" <?php selected($post_type_data['exclude_from_search'] ?? '', '0'); ?>>No</option>
                            <option value="1" <?php selected($post_type_data['exclude_from_search'] ?? '', '1'); ?>>Yes</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Menu Position</th>
                    <td><input type="number" name="custom_post_type_menu_position" value="<?php echo esc_attr($post_type_data['menu_position'] ?? ''); ?>"></td>
                </tr>
                <tr>
                    <th scope="row">Supports</th>
                    <td>
                        <input type="checkbox" name="custom_post_type_supports[]" value="title" <?php checked(in_array('title', $post_type_data['supports'] ?? [])); ?>> Title<br>
                        <input type="checkbox" name="custom_post_type_supports[]" value="editor" <?php checked(in_array('editor', $post_type_data['supports'] ?? [])); ?>> Editor<br>
                        <input type="checkbox" name="custom_post_type_supports[]" value="comments" <?php checked(in_array('comments', $post_type_data['supports'] ?? [])); ?>> Comments<br>
                        <input type="checkbox" name="custom_post_type_supports[]" value="thumbnail" <?php checked(in_array('thumbnail', $post_type_data['supports'] ?? [])); ?>> Thumbnail<br>
                        <input type="checkbox" name="custom_post_type_supports[]" value="author" <?php checked(in_array('author', $post_type_data['supports'] ?? [])); ?>> Author<br>
                        <input type="checkbox" name="custom_post_type_supports[]" value="excerpt" <?php checked(in_array('excerpt', $post_type_data['supports'] ?? [])); ?>> Excerpt<br>
                        <input type="checkbox" name="custom_post_type_supports[]" value="trackbacks" <?php checked(in_array('trackbacks', $post_type_data['supports'] ?? [])); ?>> Trackbacks<br>
                        <input type="checkbox" name="custom_post_type_supports[]" value="page-attributes" <?php checked(in_array('page-attributes', $post_type_data['supports'] ?? [])); ?>> Page-attributes<br>
                    </td>
                </tr>
            </table>
            <br>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary me-2">
                    <?php echo $editing ? 'Update Custom Post Type' : 'Save Custom Post Type'; ?>
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cpts')); ?>" class="btn btn-danger">
                    Cancel
                </a>
            </div>
        </form>
        <br>
    </form>
    <h2>Existing Custom Post Types</h2>
    <input type="text" id="search-cpts" placeholder="Search Custom Post Types">
    <button id="search-cpts-button" class="btn btn-primary btn-sm">Search</button>
    <button id="reset-cpts-button" class="btn btn-secondary btn-sm">Reset</button><br><br>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th>Post Type Slug</th>
            <th>Singular Label</th>
            <th>Plural Label</th>
            <th>Description</th>
            <th>Public</th>
            <th>Exclude from Search</th>
            <th>Shortcode</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
        </thead>
        <tbody id="cpts-results">
        <?php
        $custom_post_types = get_option('custom_post_types_table_view', array());
        if (is_array($custom_post_types) && !empty($custom_post_types)) {
            foreach ($custom_post_types as $slug => $details) {
                ?>
                <tr>
                    <td><?php echo esc_html($details['slug']); ?></td>
                    <td><?php echo esc_html($details['singular']); ?></td>
                    <td><?php echo esc_html($details['plural']); ?></td>
                    <td><?php echo esc_html($details['description'] ?? ''); ?></td>
                    <td><?php echo isset($details['public']) && $details['public'] ? 'Yes' : 'No'; ?></td>
                    <td><?php echo !empty($details['exclude_from_search']) ? 'Yes' : 'No'; ?></td>
                    <td><code>[cpts post_type="<?php echo esc_attr($slug); ?>"]</code></td>
                    <td><a href="?page=cpts&edit_post_type=<?php echo esc_attr($slug); ?>">Edit</a></td>
                    <td><a href="<?php echo wp_nonce_url(
                            admin_url('admin-post.php?action=delete_custom_post_type&slug=' . urlencode($slug)),
                            'delete_custom_post_type_' . $slug
                        ); ?>" onclick="return confirm('Are you sure you want to delete this custom post type?');">Delete</a>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="9">No custom post types found.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
