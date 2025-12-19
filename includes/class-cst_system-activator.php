<?php

/**
 * Fired during plugin activation
 *
 * @link       https://https://github.com/kanji8210/cst_system
 * @since      1.0.0
 *
 * @package    Cst_system
 * @subpackage Cst_system/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cst_system
 * @subpackage Cst_system/includes
 * @author     Dennis <denisdekemet@gmail.com>
 */
class Cst_system_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;

		// Path to SQL file created for table creation. If empty or missing, skip.
		$sql_file = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/create_tables.sql';

		if ( file_exists( $sql_file ) && filesize( $sql_file ) > 0 ) {
			$sql = file_get_contents( $sql_file );
			if ( $sql !== false && trim( $sql ) !== '' ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				// Use dbDelta to create/upgrade tables from SQL file.
				dbDelta( $sql );
			}
		}

		// Also attempt to create CTM-managed tables via CTM_Database_Manager
		$ctm_manager_file = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ctm-database-manager.php';
		if ( file_exists( $ctm_manager_file ) ) {
			require_once $ctm_manager_file;
			if ( class_exists( 'CTM_Database_Manager' ) ) {
				$manager = new CTM_Database_Manager();
				$manager->install_tables();
			}
		}

	}

}
