<?php
/**
 * Locations list admin page
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$manager = new CTM_Database_Manager();
$locations = $manager->get_locations( array( 'limit' => 200 ) );

?>
<div class="wrap">
	<h1>Locations <a href="<?php echo admin_url( 'admin.php?page=cst_location_add' ); ?>" class="page-title-action">Add New</a></h1>

	<?php if ( isset( $_GET['added'] ) ): ?>
		<div class="notice notice-success is-dismissible"><p>Location added.</p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['updated'] ) ): ?>
		<div class="notice notice-success is-dismissible"><p>Location updated.</p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ): ?>
		<div class="notice notice-success is-dismissible"><p>Location deleted.</p></div>
	<?php endif; ?>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Lat</th>
				<th>Lng</th>
				<th>Status</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $locations ) ): foreach ( $locations as $loc ): ?>
				<tr>
					<td><?php echo esc_html( $loc['id'] ); ?></td>
					<td><?php echo esc_html( $loc['name'] ); ?></td>
					<td><?php echo esc_html( $loc['latitude'] ); ?></td>
					<td><?php echo esc_html( $loc['longitude'] ); ?></td>
					<td><?php echo esc_html( $loc['status'] ); ?></td>
					<td>
						<a href="<?php echo admin_url( 'admin.php?page=cst_location_add&id=' . intval( $loc['id'] ) ); ?>">Edit</a>
						|
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
							<?php wp_nonce_field( 'ctm_location_delete', 'ctm_location_delete_nonce' ); ?>
							<input type="hidden" name="action" value="ctm_delete_location" />
							<input type="hidden" name="id" value="<?php echo intval( $loc['id'] ); ?>" />
							<button type="submit" class="button-link delete-link" onclick="return confirm('Delete this location?');">Delete</button>
						</form>
					</td>
				</tr>
			<?php endforeach; else: ?>
				<tr><td colspan="6">No locations found.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
