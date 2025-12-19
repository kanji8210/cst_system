<?php
/**
 * Settings admin page with DB audit
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$manager_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/includes/class-ctm-database-manager.php';
if ( file_exists( $manager_file ) ) {
	require_once $manager_file;
	$manager = new CTM_Database_Manager();
} else {
	echo '<div class="notice notice-error"><p>CTM Database manager file not found: ' . esc_html( $manager_file ) . '</p></div>';
	return;
}
$check = $manager->check_tables();

?>
<div class="wrap">
	<h1>CST System Settings</h1>

	<h2>Database audit</h2>

	<?php if ( ! $check['all_tables_exist'] ): ?>
		<div class="notice notice-warning"><p>Some CTM tables are missing.</p></div>
	<?php else: ?>
		<div class="notice notice-success"><p>All CTM tables exist.</p></div>
	<?php endif; ?>

	<table class="widefat fixed striped">
		<thead>
			<tr>
				<th>Key</th>
				<th>Table Name</th>
				<th>Exists</th>
				<th>Rows</th>
				<th>Structure OK</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $check['table_details'] as $key => $detail ): ?>
				<tr>
					<td><?php echo esc_html( $key ); ?></td>
					<td><?php echo esc_html( $detail['table_name'] ); ?></td>
					<td><?php echo $detail['exists'] ? 'Yes' : 'No'; ?></td>
					<td><?php echo intval( $detail['row_count'] ); ?></td>
					<td><?php echo $detail['structure_ok'] ? 'Yes' : 'No'; ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( ! empty( $check['missing_tables'] ) ): ?>
		<h3>Missing tables</h3>
		<ul>
			<?php foreach ( $check['missing_tables'] as $t ): ?>
				<li><?php echo esc_html( $t ); ?></li>
			<?php endforeach; ?>
		</ul>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ctm_create_missing_tables', 'ctm_create_missing_tables_nonce' ); ?>
			<input type="hidden" name="action" value="ctm_create_missing_tables" />
			<?php submit_button( 'Create missing tables', 'primary' ); ?>
		</form>
	<?php endif; ?>

	<?php if ( isset( $_GET['created'] ) ): ?>
		<?php if ( intval( $_GET['created'] ) === 1 ): ?>
			<div class="notice notice-success is-dismissible"><p>Attempted to create missing tables. Check the audit again.</p></div>
		<?php else: ?>
			<div class="notice notice-error is-dismissible"><p>No tables were created.</p></div>
		<?php endif; ?>
	<?php endif; ?>

</div>
