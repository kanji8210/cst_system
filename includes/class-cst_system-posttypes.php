<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register custom post types for CST System
 */
class Cst_system_Post_Types {

    public function __construct() {
        add_action( 'init', array( $this, 'register_post_types' ) );
        add_action( 'init', array( $this, 'register_meta' ) );
        add_action( 'save_post_ctm_package', array( $this, 'maybe_generate_sku' ), 10, 3 );
    }

    public function register_post_types() {
        $labels = array(
            'name' => 'Packages',
            'singular_name' => 'Package',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Package',
            'edit_item' => 'Edit Package',
            'new_item' => 'New Package',
            'view_item' => 'View Package',
            'search_items' => 'Search Packages',
            'not_found' => 'No packages found',
            'not_found_in_trash' => 'No packages found in Trash',
            'all_items' => 'All Packages',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
            'rewrite' => array( 'slug' => 'packages' ),
            'capability_type' => 'post',
            'menu_icon' => 'dashicons-palmtree',
        );

        register_post_type( 'ctm_package', $args );
    }

    public function register_meta() {
        // Register essential meta fields for packages
        register_post_meta( 'ctm_package', 'ctm_package_code', array( 'single' => true, 'type' => 'string', 'show_in_rest' => true ) );
        register_post_meta( 'ctm_package', 'ctm_tagline', array( 'single' => true, 'type' => 'string', 'show_in_rest' => true ) );
        register_post_meta( 'ctm_package', 'ctm_package_type', array( 'single' => true, 'type' => 'string', 'show_in_rest' => true ) );
        register_post_meta( 'ctm_package', 'ctm_duration_type', array( 'single' => true, 'type' => 'string', 'show_in_rest' => true ) );
        register_post_meta( 'ctm_package', 'ctm_duration_value', array( 'single' => true, 'type' => 'string', 'show_in_rest' => true ) );
        register_post_meta( 'ctm_package', 'ctm_min_age', array( 'single' => true, 'type' => 'integer', 'show_in_rest' => true ) );
        register_post_meta( 'ctm_package', 'ctm_difficulty_level', array( 'single' => true, 'type' => 'string', 'show_in_rest' => true ) );
        register_post_meta( 'ctm_package', 'ctm_gallery', array( 'single' => true, 'type' => 'string', 'show_in_rest' => true ) );
    }

    public function maybe_generate_sku( $post_ID, $post, $update ) {
        // Auto-generate a SKU/package code if not present
        $code = get_post_meta( $post_ID, 'ctm_package_code', true );
        if ( empty( $code ) ) {
            $sku = strtoupper( wp_generate_password( 6, false, false ) );
            update_post_meta( $post_ID, 'ctm_package_code', $sku );
        }
    }

}

// Instantiate so it registers hooks when included
new Cst_system_Post_Types();
