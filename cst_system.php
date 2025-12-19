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
