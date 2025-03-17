<?php
if (!defined('ABSPATH')) {
    exit;
}

// Logic to handle form submissions and manage taxonomies
$editing_taxonomy = isset($_GET['edit_taxonomy']) ? sanitize_text_field($_GET['edit_taxonomy']) : null;
$custom_taxonomies = get_option('custom_taxonomies', []);

// Check if editing a taxonomy
if ($editing_taxonomy && isset($custom_taxonomies[$editing_taxonomy])) {
    $taxonomy_data = $custom_taxonomies[$editing_taxonomy]; // Retrieve the taxonomy data
} else {
    $taxonomy_data = [
        'slug' => '',
        'singular' => '',
        'plural' => '',
        'associated_post_type' => '',
    ];
}

// Render the page
?>
<div class="wrap">
    <h1>Manage Taxonomies</h1>
    <h2><?php echo $editing_taxonomy ? 'Edit' : 'Create'; ?> Taxonomy</h2>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="<?php echo isset($taxonomy_to_edit) ? 'edit_taxonomy' : 'save_taxonomy'; ?>">
        <?php wp_nonce_field('save_taxonomy_action', 'save_taxonomy_nonce'); ?>
        <input type="hidden" name="original_taxonomy_slug" value="<?php echo esc_attr($taxonomy_to_edit['slug'] ?? ''); ?>">

        <table class="form-table">
            <tr>
                <th scope="row">Taxonomy Slug</th>
                <td><input type="text" name="taxonomy_slug" value="<?php echo esc_attr($taxonomy_data['slug']); ?>" required></td>
            </tr>
            <tr>
                <th scope="row">Singular Label</th>
                <td><input type="text" name="taxonomy_singular" value="<?php echo esc_attr($taxonomy_data['singular']); ?>" required></td>
            </tr>
            <tr>
                <th scope="row">Plural Label</th>
                <td><input type="text" name="taxonomy_plural" value="<?php echo esc_attr($taxonomy_data['plural']); ?>" required></td>
            </tr>
            <tr>
                <th scope="row">Associated Post Type</th>
                <td>
                    <select name="associated_post_type" required>
                        <option value="">Select Post Type</option>
                        <?php
                        $custom_post_types = get_option('custom_post_types_table_view', array());

                        foreach ($custom_post_types as $slug => $details) {
                            $selected = (isset($taxonomy_to_edit['associated_post_type']) && $taxonomy_to_edit['associated_post_type'] === $slug) ? 'selected' : '';
                            echo '<option value="' . esc_attr($slug) . '" ' . $selected . '>' . esc_html($details['plural']) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <br>
        <div class="d-flex">
            <button type="submit" class="btn btn-primary me-2">
                <?php echo isset($taxonomy_to_edit) ? 'Update Taxonomy' : 'Save Taxonomy'; ?>
            </button>
            <a href="<?php echo esc_url(admin_url('admin.php?page=taxonomies')); ?>" class="btn btn-danger">
                Cancel
            </a>
        </div>
    </form>
    <h2>Existing Taxonomies</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th>Taxonomy Slug</th>
            <th>Singular Label</th>
            <th>Plural Label</th>
            <th>Associated Post Type</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $custom_taxonomies = get_option('custom_taxonomies', array());

        if (is_array($custom_taxonomies) && !empty($custom_taxonomies)) {
            foreach ($custom_taxonomies as $slug => $details) {
                ?>
                <tr>
                    <td><?php echo esc_html($slug); ?></td>
                    <td><?php echo esc_html($details['singular']); ?></td>
                    <td><?php echo esc_html($details['plural']); ?></td>
                    <td><?php echo esc_html($details['associated_post_type']); ?></td>
                    <td><a href="<?php echo wp_nonce_url(
                            admin_url('admin.php?page=taxonomies&edit_taxonomy=' . urlencode($slug)),
                            'edit_taxonomy_' . $slug
                        ); ?>">Edit</a>
                    </td>
                    <td><a href="<?php echo wp_nonce_url(
                            admin_url('admin-post.php?action=delete_custom_taxonomy&slug=' . urlencode($slug)),
                            'delete_custom_taxonomy_' . $slug
                        ); ?>" onclick="return confirm('Are you sure you want to delete this taxonomy?');">Delete</a>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="6">No taxonomies found.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
<?php
