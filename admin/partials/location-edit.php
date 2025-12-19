<?php
/**
 * Location add/edit admin page
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$manager = new CTM_Database_Manager();
$location = null;
if ( isset( $_GET['id'] ) ) {
    $location = $manager->get_location( intval( $_GET['id'] ) );
}

?>
<div class="wrap">
    <h1><?php echo $location ? 'Edit Location' : 'Add Location'; ?></h1>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'ctm_location_save', 'ctm_location_nonce' ); ?>
        <input type="hidden" name="action" value="ctm_save_location" />
        <?php if ( $location ): ?>
            <input type="hidden" name="id" value="<?php echo esc_attr( $location['id'] ); ?>" />
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th><label for="name">Name</label></th>
                <td><input type="text" name="name" id="name" value="<?php echo $location ? esc_attr( $location['name'] ) : ''; ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="latitude">Latitude</label></th>
                <td><input type="text" name="latitude" id="latitude" value="<?php echo $location ? esc_attr( $location['latitude'] ) : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="longitude">Longitude</label></th>
                <td><input type="text" name="longitude" id="longitude" value="<?php echo $location ? esc_attr( $location['longitude'] ) : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="description">Description</label></th>
                <td><textarea name="description" id="description" rows="6" class="large-text"><?php echo $location ? esc_textarea( $location['description'] ) : ''; ?></textarea></td>
            </tr>
            <tr>
                <th><label for="featured_image">Featured Image (URL or Attachment ID)</label></th>
                <td>
                    <input type="text" name="featured_image" id="featured_image" value="<?php echo $location ? esc_attr( $location['featured_image'] ) : ''; ?>" class="regular-text">
                    <p class="description">Paste image URL or attachment ID. You can also use the Media Library to upload and copy the URL.</p>
                </td>
            </tr>
            <tr>
                <th><label for="other_images">Other Images (comma-separated URLs or IDs)</label></th>
                <td>
                    <input type="text" name="other_images" id="other_images" value="<?php echo $location ? esc_attr( is_serialized( $location['other_images'] ) ? implode(',', (array) maybe_unserialize( $location['other_images'] ) ) : $location['other_images'] ) : ''; ?>" class="regular-text">
                    <p class="description">Comma-separated image URLs or attachment IDs.</p>
                </td>
            </tr>
            <tr>
                <th><label for="status">Status</label></th>
                <td>
                    <select name="status" id="status">
                        <option value="published" <?php selected( $location && $location['status'] === 'published' ); ?>>Published</option>
                        <option value="draft" <?php selected( $location && $location['status'] === 'draft' ); ?>>Draft</option>
                    </select>
                </td>
            </tr>
        </table>

        <?php submit_button( $location ? 'Update Location' : 'Create Location' ); ?>
    </form>
</div>
