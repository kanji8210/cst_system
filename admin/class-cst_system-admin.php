<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://github.com/kanji8210/cst_system
 * @since      1.0.0
 *
 * @package    Cst_system
 * @subpackage Cst_system/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cst_system
 * @subpackage Cst_system/admin
 * @author     Dennis <denisdekemet@gmail.com>
 */
class Cst_system_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register admin menus and pages.
	 */
	public function register_admin_menu() {

		$capability = 'manage_options';
		$parent_slug = 'cst_system';

		add_menu_page( 'CST System', 'CST System', $capability, $parent_slug, array( $this, 'render_packager_list' ), 'dashicons-admin-generic', 6 );

		// Packagers list and add/edit
		add_submenu_page( $parent_slug, 'Packagers', 'Packagers', $capability, 'cst_packagers', array( $this, 'render_packager_list' ) );
		add_submenu_page( $parent_slug, 'Add Packager', 'Add New', $capability, 'cst_packager_add', array( $this, 'render_packager_edit' ) );

		// Packages management (CPT-based)
		add_submenu_page( $parent_slug, 'Packages', 'Packages', $capability, 'cst_packages', array( $this, 'render_packages_list' ) );
		add_submenu_page( $parent_slug, 'Add Package', 'Add Package', $capability, 'cst_package_add', array( $this, 'render_package_edit' ) );

		// Locations
		add_submenu_page( $parent_slug, 'Locations', 'Locations', $capability, 'cst_locations', array( $this, 'render_locations_list' ) );
		add_submenu_page( $parent_slug, 'Add Location', 'Add Location', $capability, 'cst_location_add', array( $this, 'render_location_edit' ) );

		// Settings
		add_submenu_page( $parent_slug, 'Settings', 'Settings', $capability, 'cst_settings', array( $this, 'render_settings_page' ) );

	}

	/**
	 * Render Packager list page.
	 */
	public function render_packager_list() {
		include plugin_dir_path( __FILE__ ) . 'partials/packager-list.php';
	}

	/**
	 * Render Packager add/edit page.
	 */
	public function render_packager_edit() {
		include plugin_dir_path( __FILE__ ) . 'partials/packager-edit.php';
	}

	/**
	 * Render Locations list page.
	 */
	public function render_locations_list() {
		include plugin_dir_path( __FILE__ ) . 'partials/locations-list.php';
	}

	public function render_packages_list() {
		// Simple redirect to CPT list screen for now
		$screen_url = admin_url( 'edit.php?post_type=ctm_package' );
		echo '<script>location.href="' . esc_js( $screen_url ) . '";</script>';
	}

	public function render_package_edit() {
		include plugin_dir_path( __FILE__ ) . 'partials/package-edit.php';
	}

	public function render_location_edit() {
		include plugin_dir_path( __FILE__ ) . 'partials/location-edit.php';
	}

	/**
	 * Handle saving a location from admin form (admin_post)
	 */
	public function handle_save_location() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		check_admin_referer( 'ctm_location_save', 'ctm_location_nonce' );

		$manager = new CTM_Database_Manager();
		$data = array();
		$data['name'] = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$data['latitude'] = isset( $_POST['latitude'] ) ? floatval( $_POST['latitude'] ) : null;
		$data['longitude'] = isset( $_POST['longitude'] ) ? floatval( $_POST['longitude'] ) : null;
		$data['description'] = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
		// featured_image may be URL or ID; store raw value (sanitized in manager)
		$data['featured_image'] = isset( $_POST['featured_image'] ) ? wp_unslash( $_POST['featured_image'] ) : null;
		// other_images: accept comma-separated list of URLs or IDs
		if ( isset( $_POST['other_images'] ) ) {
			$raw = wp_unslash( $_POST['other_images'] );
			$parts = array_map( 'trim', explode( ',', $raw ) );
			$data['other_images'] = array_filter( $parts );
		} else {
			$data['other_images'] = null;
		}
		$data['status'] = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'draft';

		if ( ! empty( $_POST['id'] ) ) {
			$manager->update_location( intval( $_POST['id'] ), $data );
			$redirect = admin_url( 'admin.php?page=cst_locations&updated=1' );
		} else {
			$manager->insert_location( $data );
			$redirect = admin_url( 'admin.php?page=cst_locations&added=1' );
		}

		wp_redirect( $redirect );
		exit;
	}

	/**
	 * Handle deleting a location (admin_post)
	 */
	public function handle_delete_location() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		check_admin_referer( 'ctm_location_delete', 'ctm_location_delete_nonce' );

		$manager = new CTM_Database_Manager();
		if ( isset( $_POST['id'] ) ) {
			$manager->delete_location( intval( $_POST['id'] ) );
		}

		wp_redirect( admin_url( 'admin.php?page=cst_locations&deleted=1' ) );
		exit;
	}

	/**
	 * Handle creating missing CTM tables from settings page.
	 */
	public function handle_create_missing_tables() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		check_admin_referer( 'ctm_create_missing_tables', 'ctm_create_missing_tables_nonce' );

		$manager_file = dirname( dirname( __FILE__ ) ) . '/includes/class-ctm-database-manager.php';
		if ( file_exists( $manager_file ) ) {
			require_once $manager_file;
			$manager = new CTM_Database_Manager();
		} else {
			wp_die( 'CTM Database manager missing: ' . esc_html( $manager_file ) );
		}

		$check = $manager->check_tables();
		$missing = $check['missing_tables']; // full table names like wp_ctm_locations

		$to_create = array();
		$sqls = $manager->get_table_sql();
		foreach ( $sqls as $key => $sql ) {
			foreach ( $missing as $missing_table ) {
				if ( strpos( $sql, $missing_table ) !== false ) {
					$to_create[] = $key;
				}
			}
		}

		$results = array();
		if ( ! empty( $to_create ) ) {
			$results = $manager->create_missing_tables( $to_create );
		}

		$redirect = admin_url( 'admin.php?page=cst_settings' );
		$redirect = add_query_arg( 'created', empty( $results ) ? 0 : 1, $redirect );

		wp_redirect( $redirect );
		exit;
	}

	/**
	 * Handle creating/updating a package from the custom admin UI.
	 */
	public function handle_save_package() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		check_admin_referer( 'ctm_save_package', 'ctm_package_nonce' );

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';

		$post_args = array(
			'post_title' => $title,
			'post_content' => $content,
			'post_status' => 'publish',
			'post_type' => 'ctm_package',
		);

		if ( $post_id ) {
			$post_args['ID'] = $post_id;
			$post_id = wp_update_post( $post_args );
		} else {
			$post_id = wp_insert_post( $post_args );
		}

		if ( is_wp_error( $post_id ) ) {
			wp_die( 'Error creating package: ' . $post_id->get_error_message() );
		}

		// Save meta fields
		$meta_map = array(
			'ctm_tagline' => 'ctm_tagline',
			'ctm_package_type' => 'ctm_package_type',
			'ctm_duration_type' => 'ctm_duration_type',
			'ctm_duration_value' => 'ctm_duration_value',
			'ctm_gallery' => 'ctm_gallery',
			'ctm_base_price' => 'ctm_base_price',
		);

		foreach ( $meta_map as $field => $meta_key ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, $meta_key, wp_unslash( $_POST[ $field ] ) );
			}
		}

		// Save itinerary JSON if present
		if ( isset( $_POST['ctm_itinerary'] ) ) {
			update_post_meta( $post_id, 'ctm_itinerary', wp_unslash( $_POST['ctm_itinerary'] ) );
		}

		// Redirect back to edit screen
		$redirect = admin_url( 'post.php?post=' . intval( $post_id ) . '&action=edit' );
		wp_redirect( $redirect );
		exit;
	}

	/**
	 * Render Settings page.
	 */
	public function render_settings_page() {
		include plugin_dir_path( __FILE__ ) . 'partials/settings.php';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cst_system_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cst_system_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cst_system-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cst_system_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cst_system_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cst_system-admin.js', array( 'jquery' ), $this->version, false );

		// Itinerary drag-and-drop script and SortableJS
		wp_enqueue_script( 'ctm-sortable', 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js', array(), '1.15.0', true );
		wp_enqueue_script( $this->plugin_name . '-itinerary', plugin_dir_url( __FILE__ ) . 'js/package-itinerary.js', array( 'jquery', 'ctm-sortable' ), $this->version, true );

	}

}
