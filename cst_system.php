<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://github.com/kanji8210/cst_system
 * @since             1.0.0
 * @package           Cst_system
 *
 * @wordpress-plugin
 * Plugin Name:       Cayman signature
 * Plugin URI:        https://https://github.com/kanji8210/cst_system
 * Description:       manage and market nich tour company
 * Version:           1.0.0
 * Author:            Dennis
 * Author URI:        https://https://github.com/kanji8210/cst_system/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cst_system
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CST_SYSTEM_VERSION', '1.0.0' );
// Backwards-compatible version constant used by some modules
// Other AJAX handlers...
if ( ! defined( 'CTM_VERSION' ) ) {
	define( 'CTM_VERSION', CST_SYSTEM_VERSION );
}
/**
 * Database schema version for CTM tables
 */
define( 'CTM_DB_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cst_system-activator.php
 */
function activate_cst_system() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cst_system-activator.php';
	Cst_system_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cst_system-deactivator.php
 */
function deactivate_cst_system() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cst_system-deactivator.php';
	Cst_system_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cst_system' );
register_deactivation_hook( __FILE__, 'deactivate_cst_system' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cst_system.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cst_system() {

	$plugin = new Cst_system();
	$plugin->run();

}
run_cst_system();

/**
 * Optional: include package manager and model, initialize and register AJAX handlers.
 * Use safe checks to avoid redeclare or missing class issues.
 */
// Include if available
$pm_file = plugin_dir_path( __FILE__ ) . 'includes/class-package-manager.php';
$model_file = plugin_dir_path( __FILE__ ) . 'includes/models/class-package.php';
if ( file_exists( $pm_file ) ) {
	require_once $pm_file;
}
if ( file_exists( $model_file ) ) {
	require_once $model_file;
}

// Initialize on plugins_loaded if class exists
add_action( 'plugins_loaded', function() {
	if ( class_exists( 'CTM_Package_Manager' ) ) {
		if ( method_exists( 'CTM_Package_Manager', 'get_instance' ) ) {
			CTM_Package_Manager::get_instance();
		}
	}
} );

// Secure AJAX handler for duplicating packages
add_action( 'wp_ajax_ctm_duplicate_package', function() {
	// nonce expected in POST['nonce']
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ctm_package_nonce' ) ) {
		wp_send_json_error( 'Invalid nonce', 403 );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( 'Unauthorized', 403 );
	}

	$package_id = isset( $_POST['package_id'] ) ? intval( wp_unslash( $_POST['package_id'] ) ) : 0;
	if ( ! $package_id ) {
		wp_send_json_error( 'Missing package_id', 400 );
	}

	if ( ! class_exists( 'CTM_Package' ) ) {
		wp_send_json_error( 'Package class not available', 500 );
	}

	$package = new CTM_Package( $package_id );
	$new_id = $package->duplicate();
	if ( $new_id ) {
		wp_send_json_success( array( 'edit_url' => get_edit_post_link( $new_id, 'raw' ) ) );
	}

	wp_send_json_error( 'Failed to duplicate package', 500 );
} );

// Frontend AJAX to handle 'Express Interest' submissions
/**
 * Helper: get existing client by email or create a new `ctm_client` post.
 * Returns client post ID on success, 0 on failure.
 */
function ctm_get_or_create_client_from_interest( $email, $name = '', $travellers = 0 ) {
	if ( empty( $email ) || ! is_email( $email ) ) return 0;

	$found = get_posts( array(
		'post_type' => 'ctm_client',
		'posts_per_page' => 1,
		'meta_key' => '_client_email',
		'meta_value' => $email,
		'fields' => 'ids',
	) );

	if ( ! empty( $found ) ) {
		return intval( $found[0] );
	}

	$title = ! empty( $name ) ? sanitize_text_field( $name ) : sanitize_email( $email );
	$postarr = array(
		'post_title' => $title,
		'post_status' => 'publish',
		'post_type' => 'ctm_client',
	);

	$new_id = wp_insert_post( $postarr );
	if ( is_wp_error( $new_id ) || ! $new_id ) return 0;

	update_post_meta( $new_id, '_client_email', sanitize_email( $email ) );
	if ( ! empty( $name ) ) {
		update_post_meta( $new_id, '_client_traveller_names', sanitize_text_field( $name ) );
	}

	return intval( $new_id );
}

add_action( 'wp_ajax_ctm_submit_interest', function() {
	// logged-in submissions
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ctm_interest_nonce' ) ) {
		wp_send_json_error( 'Invalid nonce', 403 );
	}

	$post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : 0;
	$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$travellers = isset( $_POST['travellers'] ) ? intval( wp_unslash( $_POST['travellers'] ) ) : 0;
	$dates = isset( $_POST['dates'] ) ? sanitize_text_field( wp_unslash( $_POST['dates'] ) ) : '';

	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( 'Invalid email', 400 );
	}

	$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
	$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

	$entry = array(
		'time' => current_time( 'mysql' ),
		'name' => $name,
		'email' => $email,
		'travellers' => $travellers,
		'dates' => $dates,
		'start_date' => $start_date,
		'end_date' => $end_date,
	);

	if ( $post_id ) {
		$meta = get_post_meta( $post_id, '_ctm_interest_submissions', true );
		if ( ! is_array( $meta ) ) $meta = array();
		$meta[] = $entry;
		update_post_meta( $post_id, '_ctm_interest_submissions', $meta );
	}

	// Create or link client from this interest submission
	$client_id = ctm_get_or_create_client_from_interest( $email, $name, $travellers );
	if ( $client_id && $post_id ) {
		$clients = get_post_meta( $post_id, '_clients', true );
		if ( ! is_array( $clients ) ) $clients = array();
		if ( ! in_array( $client_id, $clients ) ) {
			$clients[] = $client_id;
			update_post_meta( $post_id, '_clients', $clients );
		}
	}

	// send admin email
	$to = get_option( 'admin_email' );
	$subject = sprintf( 'Interest: %s', $post_id ? get_the_title( $post_id ) : 'Package interest' );
	$message = "New interest submission:\n\n";
	foreach ( $entry as $k => $v ) {
		$message .= ucfirst( $k ) . ": " . $v . "\n";
	}

	wp_mail( $to, $subject, $message );

	wp_send_json_success( array( 'saved' => 1 ) );
} );

add_action( 'wp_ajax_nopriv_ctm_submit_interest', function() {
	// non-logged submissions
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ctm_interest_nonce' ) ) {
		wp_send_json_error( 'Invalid nonce', 403 );
	}

	$post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : 0;
	$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$travellers = isset( $_POST['travellers'] ) ? intval( wp_unslash( $_POST['travellers'] ) ) : 0;
	$dates = isset( $_POST['dates'] ) ? sanitize_text_field( wp_unslash( $_POST['dates'] ) ) : '';

	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( 'Invalid email', 400 );
	}

	$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
	$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

	$entry = array(
		'time' => current_time( 'mysql' ),
		'name' => $name,
		'email' => $email,
		'travellers' => $travellers,
		'dates' => $dates,
		'start_date' => $start_date,
		'end_date' => $end_date,
	);

	if ( $post_id ) {
		$meta = get_post_meta( $post_id, '_ctm_interest_submissions', true );
		if ( ! is_array( $meta ) ) $meta = array();
		$meta[] = $entry;
		update_post_meta( $post_id, '_ctm_interest_submissions', $meta );
	}

	// Create or link client from this interest submission
	$client_id = ctm_get_or_create_client_from_interest( $email, $name, $travellers );
	if ( $client_id && $post_id ) {
		$clients = get_post_meta( $post_id, '_clients', true );
		if ( ! is_array( $clients ) ) $clients = array();
		if ( ! in_array( $client_id, $clients ) ) {
			$clients[] = $client_id;
			update_post_meta( $post_id, '_clients', $clients );
		}
	}

	// send admin email
	$to = get_option( 'admin_email' );
	$subject = sprintf( 'Interest: %s', $post_id ? get_the_title( $post_id ) : 'Package interest' );
	$message = "New interest submission:\n\n";
	foreach ( $entry as $k => $v ) {
		$message .= ucfirst( $k ) . ": " . $v . "\n";
	}

	wp_mail( $to, $subject, $message );

	wp_send_json_success( array( 'saved' => 1 ) );
} );

// Admin action to update client status from row action links
add_action( 'admin_post_ctm_client_update_status', function() {
	$client_id = isset( $_REQUEST['client_id'] ) ? intval( wp_unslash( $_REQUEST['client_id'] ) ) : 0;
	$status = isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : '';
	$nonce = isset( $_REQUEST['_wpnonce'] ) ? wp_unslash( $_REQUEST['_wpnonce'] ) : '';

	if ( ! $client_id || ! in_array( $status, array( 'contacted', 'scheduled', 'cold' ), true ) ) {
		wp_die( __( 'Invalid parameters', 'cayman-tours-manager' ) );
	}

	if ( ! wp_verify_nonce( $nonce, 'ctm_client_status_' . $client_id ) ) {
		wp_die( __( 'Invalid nonce', 'cayman-tours-manager' ) );
	}

	if ( ! current_user_can( 'edit_post', $client_id ) ) {
		wp_die( __( 'Unauthorized', 'cayman-tours-manager' ) );
	}

	update_post_meta( $client_id, '_client_status', $status );

	$redirect = wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=ctm_client' );
	wp_safe_redirect( $redirect );
	exit;
} );
