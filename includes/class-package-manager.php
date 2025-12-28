<?php
// File: includes/class-package-manager.php

if (!defined('ABSPATH')) {
    exit;
}

class CTM_Package_Manager {
    
    private static $instance = null;
    private $package_cpt = 'ctm_package';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'register_package_cpt'));
        add_action('init', array($this, 'register_client_cpt'));
        add_action('init', array($this, 'remove_package_editor'));
        add_action('add_meta_boxes', array($this, 'add_package_meta_boxes'));
        add_action('add_meta_boxes', array($this, 'add_client_meta_boxes'));
        // Client list columns and row actions
        add_filter('manage_ctm_client_posts_columns', array($this, 'add_client_columns'));
        add_action('manage_ctm_client_posts_custom_column', array($this, 'render_client_columns'), 10, 2);
        add_filter('post_row_actions', array($this, 'client_row_actions'), 10, 2);
        add_action('save_post_' . $this->package_cpt, array($this, 'save_package_meta'), 10, 3);
        add_action('save_post_ctm_client', array($this, 'save_client_meta'), 10, 3);
        add_filter('manage_' . $this->package_cpt . '_posts_columns', array($this, 'add_package_columns'));
        add_action('manage_' . $this->package_cpt . '_posts_custom_column', array($this, 'render_package_columns'), 10, 2);
        add_action('admin_menu', array($this, 'add_package_admin_pages'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        // Frontend template loader for single package view
        add_filter('single_template', array($this, 'load_single_template'));
        // one-time migration for itinerary meta
        add_action('admin_init', array($this, 'maybe_migrate_itinerary_meta'));
    }
    
    /**
     * Remove the default WordPress editor for packages
     */
    public function remove_package_editor() {
        remove_post_type_support($this->package_cpt, 'editor');
        remove_post_type_support($this->package_cpt, 'excerpt');
    }
    
    public function register_package_cpt() {
        $labels = array(
            'name'               => __('Tour Packages', 'cayman-tours-manager'),
            'singular_name'      => __('Tour Package', 'cayman-tours-manager'),
            'menu_name'          => __('Cayman Tours', 'cayman-tours-manager'),
            'add_new'            => __('Add New Package', 'cayman-tours-manager'),
            'add_new_item'       => __('Add New Tour Package', 'cayman-tours-manager'),
            'edit_item'          => __('Edit Tour Package', 'cayman-tours-manager'),
            'new_item'           => __('New Tour Package', 'cayman-tours-manager'),
            'view_item'          => __('View Package', 'cayman-tours-manager'),
            'search_items'       => __('Search Packages', 'cayman-tours-manager'),
            'not_found'          => __('No packages found', 'cayman-tours-manager'),
            'not_found_in_trash' => __('No packages found in trash', 'cayman-tours-manager'),
        );
        
        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => false, // We'll add to our custom menu
            'query_var'           => true,
            'rewrite'             => array('slug' => 'tour-packages'),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 30,
            'supports'            => array('thumbnail'),
            'show_in_rest'        => true,
            'rest_base'           => 'packages',
        );
        
        register_post_type($this->package_cpt, $args);
        
        // Register Package Type Taxonomy
        $tax_labels = array(
            'name'              => __('Package Types', 'cayman-tours-manager'),
            'singular_name'     => __('Package Type', 'cayman-tours-manager'),
            'search_items'      => __('Search Package Types', 'cayman-tours-manager'),
            'all_items'         => __('All Package Types', 'cayman-tours-manager'),
            'parent_item'       => __('Parent Package Type', 'cayman-tours-manager'),
            'parent_item_colon' => __('Parent Package Type:', 'cayman-tours-manager'),
            'edit_item'         => __('Edit Package Type', 'cayman-tours-manager'),
            'update_item'       => __('Update Package Type', 'cayman-tours-manager'),
            'add_new_item'      => __('Add New Package Type', 'cayman-tours-manager'),
            'new_item_name'     => __('New Package Type Name', 'cayman-tours-manager'),
            'menu_name'         => __('Package Types', 'cayman-tours-manager'),
        );
        
        $tax_args = array(
            'hierarchical'      => true,
            'labels'            => $tax_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'package-type'),
            'show_in_rest'      => true,
        );
        
        register_taxonomy('package_type', array($this->package_cpt), $tax_args);
        
        // Register Activity Taxonomy
        $activity_labels = array(
            'name' => __('Activities', 'cayman-tours-manager'),
            'singular_name' => __('Activity', 'cayman-tours-manager'),
        );
        
        register_taxonomy('activity', array($this->package_cpt), array(
            'labels' => $activity_labels,
            'hierarchical' => false,
            'show_admin_column' => true,
            'show_in_rest' => true,
        ));
    }

    /**
     * Register client CPT
     */
    public function register_client_cpt() {
        $labels = array(
            'name' => __('Clients','cayman-tours-manager'),
            'singular_name' => __('Client','cayman-tours-manager'),
            'menu_name' => __('Clients','cayman-tours-manager'),
        );
        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'supports' => array('title'),
        );
        register_post_type( 'ctm_client', $args );
        // add submenu to our main menu
        add_submenu_page(
            'cayman-tours',
            __('Clients','cayman-tours-manager'),
            __('Clients','cayman-tours-manager'),
            'manage_options',
            'edit.php?post_type=ctm_client'
        );
    }
    
    public function add_package_admin_pages() {
        // Main menu
        add_menu_page(
            __('Cayman Tours', 'cayman-tours-manager'),
            __('Cayman Tours', 'cayman-tours-manager'),
            'manage_options',
            'cayman-tours',
            array($this, 'render_dashboard'),
            'dashicons-palmtree',
            30
        );
        
        // Packages submenu
        add_submenu_page(
            'cayman-tours',
            __('Tour Packages', 'cayman-tours-manager'),
            __('Packages', 'cayman-tours-manager'),
            'manage_options',
            'edit.php?post_type=ctm_package'
        );
        
        // Add New Package
        add_submenu_page(
            'cayman-tours',
            __('Add New Package', 'cayman-tours-manager'),
            __('Add New', 'cayman-tours-manager'),
            'manage_options',
            'post-new.php?post_type=ctm_package'
        );
        
        // Package Settings
        add_submenu_page(
            'cayman-tours',
            __('Package Settings', 'cayman-tours-manager'),
            __('Settings', 'cayman-tours-manager'),
            'manage_options',
            'ctm-package-settings',
            array($this, 'render_package_settings')
        );
    }
    
    public function render_dashboard() {
        include_once plugin_dir_path(__FILE__) . '../admin/views/dashboard.php';
    }
    
    public function render_package_settings() {
        include_once plugin_dir_path(__FILE__) . '../admin/views/package-settings.php';
    }
    
    public function add_package_meta_boxes($post_type) {
        if ($this->package_cpt !== $post_type) {
            return;
        }
        
        // Title and Description (replaces default editor)
        add_meta_box(
            'ctm_package_title_description',
            __('Package Title & Description', 'cayman-tours-manager'),
            array($this, 'render_title_description_meta_box'),
            $this->package_cpt,
            'normal',
            'high'
        );
        
        // Basic Information
        add_meta_box(
            'ctm_package_basic',
            __('Package Details', 'cayman-tours-manager'),
            array($this, 'render_basic_meta_box'),
            $this->package_cpt,
            'normal',
            'high'
        );
        
        // Pricing
        add_meta_box(
            'ctm_package_pricing',
            __('Pricing & Inventory', 'cayman-tours-manager'),
            array($this, 'render_pricing_meta_box'),
            $this->package_cpt,
            'normal',
            'high'
        );
        
        // Itinerary
        add_meta_box(
            'ctm_package_itinerary',
            __('Itinerary Builder', 'cayman-tours-manager'),
            array($this, 'render_itinerary_meta_box'),
            $this->package_cpt,
            'normal',
            'high'
        );
        
        // Inclusions & Exclusions
        add_meta_box(
            'ctm_package_inclusions',
            __('Inclusions & Add-ons', 'cayman-tours-manager'),
            array($this, 'render_inclusions_meta_box'),
            $this->package_cpt,
            'normal',
            'high'
        );
        
        // Schedule Template
        add_meta_box(
            'ctm_package_schedule',
            __('Schedule Template', 'cayman-tours-manager'),
            array($this, 'render_schedule_meta_box'),
            $this->package_cpt,
            'normal',
            'high'
        );
        
        // Requirements & Policies
        add_meta_box(
            'ctm_package_policies',
            __('Requirements & Policies', 'cayman-tours-manager'),
            array($this, 'render_policies_meta_box'),
            $this->package_cpt,
            'normal',
            'high'
        );
        
        // Call to Action
        add_meta_box(
            'ctm_package_cta',
            __('Call to Action Settings', 'cayman-tours-manager'),
            array($this, 'render_cta_meta_box'),
            $this->package_cpt,
            'normal',
            'high'
        );
        
        // Quick Stats
        add_meta_box(
            'ctm_package_stats',
            __('Package Statistics', 'cayman-tours-manager'),
            array($this, 'render_stats_meta_box'),
            $this->package_cpt,
            'side',
            'default'
        );
        // Clients linked to this package
        add_meta_box(
            'ctm_package_clients',
            __('Clients', 'cayman-tours-manager'),
            array($this, 'render_package_clients_metabox'),
            $this->package_cpt,
            'side',
            'default'
        );
    }
    
    public function render_title_description_meta_box($post) {
        wp_nonce_field('ctm_package_meta', 'ctm_package_meta_nonce');
        
        $package_title = $post->post_title;
        $package_description = $post->post_content;
        
        $partial = plugin_dir_path(__FILE__) . '../admin/partials/package-title-description-meta.php';
        if ( is_file( $partial ) && is_readable( $partial ) ) {
            include $partial;
        } else {
            echo '<p>Package title/description partial missing.</p>';
        }
    }
    
    public function render_basic_meta_box($post) {
        wp_nonce_field('ctm_package_meta', 'ctm_package_meta_nonce');
        
        $package_code = get_post_meta($post->ID, '_package_code', true);
        $duration_type = get_post_meta($post->ID, '_duration_type', true);
        $duration_value = get_post_meta($post->ID, '_duration_value', true);
        $difficulty = get_post_meta($post->ID, '_difficulty', true);
        $min_age = get_post_meta($post->ID, '_min_age', true);
        $max_participants = get_post_meta($post->ID, '_max_participants', true);
        $meeting_point = get_post_meta($post->ID, '_meeting_point', true);
        
        $partial = plugin_dir_path(__FILE__) . '../admin/partials/package-basic-meta.php';
        if ( is_file( $partial ) && is_readable( $partial ) ) {
            include $partial;
        } else {
            echo '<p>Package basic partial missing.</p>';
        }
    }

    /**
     * Render clients metabox on package editor
     */
    public function render_package_clients_metabox( $post ) {
        wp_nonce_field( 'ctm_package_clients', 'ctm_package_clients_nonce' );
        $linked = get_post_meta( $post->ID, '_clients', true );
        if ( ! is_array( $linked ) ) $linked = array();

        $clients = get_posts( array( 'post_type' => 'ctm_client', 'posts_per_page' => -1 ) );
        echo '<p><select id="ctm-clients-select" name="_clients[]" multiple style="width:100%;min-height:120px">';
        foreach ( $clients as $c ) {
            $sel = in_array( $c->ID, $linked ) ? 'selected' : '';
            printf( '<option value="%d" %s>%s</option>', $c->ID, $sel, esc_html( $c->post_title ) );
        }
        echo '</select></p>';
        // Quick add client UI
        echo '<div style="margin-top:8px">';
        echo '<input type="text" id="ctm-new-client-name" placeholder="'.esc_attr__( 'Name', 'cayman-tours-manager' ).'" style="width:48%;margin-right:4%" />';
        echo '<input type="email" id="ctm-new-client-email" placeholder="'.esc_attr__( 'Email', 'cayman-tours-manager' ).'" style="width:48%" />';
        echo '<p><button type="button" id="ctm-add-client" class="button">'.esc_html__( 'Create client', 'cayman-tours-manager' ).'</button> <span id="ctm-add-client-status" style="margin-left:8px"></span></p>';
        echo '</div>';
        if ( empty( $clients ) ) echo '<p>' . __( 'No clients yet. Clients are created from interest submissions.', 'cayman-tours-manager' ) . '</p>';
    }

    /**
     * Add client meta boxes (for ctm_client post type)
     */
    public function add_client_meta_boxes( $post_type ) {
        if ( 'ctm_client' !== $post_type ) return;
        add_meta_box( 'ctm_client_details', __('Client Details','cayman-tours-manager'), array( $this, 'render_client_meta_box'), 'ctm_client', 'normal', 'high' );
    }

    public function render_client_meta_box( $post ) {
        wp_nonce_field( 'ctm_client_meta', 'ctm_client_meta_nonce' );
        $age = get_post_meta( $post->ID, '_client_age', true );
        $email = get_post_meta( $post->ID, '_client_email', true );
        $phone = get_post_meta( $post->ID, '_client_phone', true );
        $travelling_with_children = get_post_meta( $post->ID, '_client_travelling_with_children', true );
        $num_children = get_post_meta( $post->ID, '_client_num_children', true );
        $children_ages = get_post_meta( $post->ID, '_client_children_ages', true );
        $traveller_names = get_post_meta( $post->ID, '_client_traveller_names', true );

        echo '<p><label>'.__('Name','cayman-tours-manager').'<br/><input type="text" name="client_name" value="'.esc_attr( $post->post_title ).'" style="width:100%" /></label></p>';
        echo '<p><label>'.__('Age','cayman-tours-manager').'<br/><input type="number" name="_client_age" value="'.esc_attr( $age ).'" /></label></p>';
        echo '<p><label>'.__('Email','cayman-tours-manager').'<br/><input type="email" name="_client_email" value="'.esc_attr( $email ).'" style="width:100%" /></label></p>';
        echo '<p><label>'.__('WhatsApp / Phone','cayman-tours-manager').'<br/><input type="text" name="_client_phone" value="'.esc_attr( $phone ).'" style="width:100%" /></label></p>';
        echo '<p><label>'.__('Travelling with children?','cayman-tours-manager').'<br/>';
        echo '<select name="_client_travelling_with_children"><option value="0" '.selected( $travelling_with_children, '0', false ).'>No</option><option value="1" '.selected( $travelling_with_children, '1', false ).'>Yes</option></select></label></p>';
        echo '<p><label>'.__('Number of children','cayman-tours-manager').'<br/><input type="number" name="_client_num_children" value="'.esc_attr( $num_children ).'" /></label></p>';
        echo '<p><label>'.__('Children ages (comma-separated)','cayman-tours-manager').'<br/><input type="text" name="_client_children_ages" value="'.esc_attr( $children_ages ).'" style="width:100%" /></label></p>';
        echo '<p><label>'.__('Names of travellers (comma-separated)','cayman-tours-manager').'<br/><input type="text" name="_client_traveller_names" value="'.esc_attr( $traveller_names ).'" style="width:100%" /></label></p>';
    }

    public function save_client_meta( $post_id, $post, $update ) {
        if ( ! isset( $_POST['ctm_client_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ctm_client_meta_nonce'] ), 'ctm_client_meta' ) ) return;
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['client_name'] ) ) {
            $title = sanitize_text_field( wp_unslash( $_POST['client_name'] ) );
            wp_update_post( array( 'ID' => $post_id, 'post_title' => $title ) );
        }

        $fields = array( '_client_age', '_client_email', '_client_phone', '_client_travelling_with_children', '_client_num_children', '_client_children_ages', '_client_traveller_names' );
        foreach ( $fields as $f ) {
            if ( isset( $_POST[ $f ] ) ) {
                update_post_meta( $post_id, $f, sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) );
            }
        }
    }
    
    public function render_pricing_meta_box($post) {
        $base_price = get_post_meta($post->ID, '_base_price', true);
        $child_price = get_post_meta($post->ID, '_child_price', true);
        $child_age_range = get_post_meta($post->ID, '_child_age_range', true);
        $infant_price = get_post_meta($post->ID, '_infant_price', true);
        $single_supplement = get_post_meta($post->ID, '_single_supplement', true);
        $seasonal_pricing = get_post_meta($post->ID, '_seasonal_pricing', true);
        $commission_rate = get_post_meta($post->ID, '_commission_rate', true);
        
        $partial = plugin_dir_path(__FILE__) . '../admin/partials/package-pricing-meta.php';
        if ( is_file( $partial ) && is_readable( $partial ) ) {
            include $partial;
        } else {
            echo '<p>Package pricing partial missing.</p>';
        }
    }
    
    public function render_itinerary_meta_box($post) {
        $itinerary = get_post_meta($post->ID, '_itinerary', true);
        if (empty($itinerary)) {
            $itinerary = array();
        }
        
        $partial = plugin_dir_path(__FILE__) . '../admin/partials/package-itinerary-meta.php';
        if ( is_file( $partial ) && is_readable( $partial ) ) {
            include $partial;
        } else {
            echo '<p>Package itinerary partial missing.</p>';
        }
    }
    
    public function render_inclusions_meta_box($post) {
        $inclusions = get_post_meta($post->ID, '_inclusions', true);
        $exclusions = get_post_meta($post->ID, '_exclusions', true);
        $addons = get_post_meta($post->ID, '_addons', true);
        
        if (empty($inclusions)) $inclusions = array();
        if (empty($exclusions)) $exclusions = array();
        if (empty($addons)) $addons = array();
        
        $partial = plugin_dir_path(__FILE__) . '../admin/partials/package-inclusions-meta.php';
        if ( is_file( $partial ) && is_readable( $partial ) ) {
            include $partial;
        } else {
            echo '<p>Package inclusions partial missing.</p>';
        }
    }
    
    public function render_schedule_meta_box($post) {
        $schedule_template = get_post_meta($post->ID, '_schedule_template', true);
        $start_time = get_post_meta($post->ID, '_start_time', true);
        $end_time = get_post_meta($post->ID, '_end_time', true);
        $recurring_pattern = get_post_meta($post->ID, '_recurring_pattern', true);
        
        $partial = plugin_dir_path(__FILE__) . '../admin/partials/package-schedule-meta.php';
        if ( is_file( $partial ) && is_readable( $partial ) ) {
            include $partial;
        } else {
            echo '<p>Package schedule partial missing.</p>';
        }
    }
    
    public function render_policies_meta_box($post) {
        $cancellation_policy = get_post_meta($post->ID, '_cancellation_policy', true);
        $requirements = get_post_meta($post->ID, '_requirements', true);
        $what_to_bring = get_post_meta($post->ID, '_what_to_bring', true);
        
        $partial = plugin_dir_path(__FILE__) . '../admin/partials/package-policies-meta.php';
        if ( is_file( $partial ) && is_readable( $partial ) ) {
            include $partial;
        } else {
            echo '<p>Package policies partial missing.</p>';
        }
    }
    
    public function render_cta_meta_box($post) {
        $cta_primary_text = get_post_meta($post->ID, '_cta_primary_text', true);
        $cta_primary_type = get_post_meta($post->ID, '_cta_primary_type', true);
        $cta_primary_value = get_post_meta($post->ID, '_cta_primary_value', true);
        $cta_secondary_text = get_post_meta($post->ID, '_cta_secondary_text', true);
        $cta_secondary_type = get_post_meta($post->ID, '_cta_secondary_type', true);
        $cta_secondary_value = get_post_meta($post->ID, '_cta_secondary_value', true);
        $cta_message = get_post_meta($post->ID, '_cta_message', true);
        $cta_show_availability = get_post_meta($post->ID, '_cta_show_availability', true);
        
        // Set defaults if empty
        if (empty($cta_primary_text)) $cta_primary_text = 'Express Interest';
        if (empty($cta_primary_type)) $cta_primary_type = 'form';
        if (empty($cta_secondary_text)) $cta_secondary_text = 'Contact Us to Book';
        if (empty($cta_secondary_type)) $cta_secondary_type = 'email';
        
        $partial = plugin_dir_path(__FILE__) . '../admin/partials/package-cta-meta.php';
        if ( is_file( $partial ) && is_readable( $partial ) ) {
            include $partial;
        } else {
            echo '<p>Package CTA partial missing.</p>';
        }
    }
    
    public function render_stats_meta_box($post) {
        $booking_count = $this->get_booking_count($post->ID);
        $revenue = $this->get_package_revenue($post->ID);
        $rating = $this->get_average_rating($post->ID);
        
        $partial = plugin_dir_path(__FILE__) . '../admin/partials/package-stats-meta.php';
        if ( is_file( $partial ) && is_readable( $partial ) ) {
            include $partial;
        } else {
            echo '<p>Package stats partial missing.</p>';
        }
    }

    /**
     * Columns for ctm_client list table
     */
    public function add_client_columns( $columns ) {
        $new = array();
        foreach ( $columns as $key => $val ) {
            $new[ $key ] = $val;
            if ( 'title' === $key ) {
                $new['client_email'] = __( 'Email', 'cayman-tours-manager' );
                $new['client_phone'] = __( 'Phone', 'cayman-tours-manager' );
                $new['client_travellers'] = __( 'Travellers', 'cayman-tours-manager' );
                $new['client_status'] = __( 'Status', 'cayman-tours-manager' );
            }
        }
        return $new;
    }

    public function render_client_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'client_email':
                $email = get_post_meta( $post_id, '_client_email', true );
                if ( $email ) echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
                break;
            case 'client_phone':
                $phone = get_post_meta( $post_id, '_client_phone', true );
                echo esc_html( $phone );
                break;
            case 'client_travellers':
                $trav = get_post_meta( $post_id, '_client_traveller_names', true );
                if ( is_string( $trav ) && strlen( trim( $trav ) ) > 0 ) {
                    $count = count( array_filter( array_map( 'trim', explode( ',', $trav ) ) ) );
                    echo intval( $count );
                } else {
                    echo '&#8212;';
                }
                break;
            case 'client_status':
                $s = get_post_meta( $post_id, '_client_status', true );
                if ( ! $s ) $s = 'new';
                $labels = array(
                    'new' => __( 'New', 'cayman-tours-manager' ),
                    'contacted' => __( 'Contacted', 'cayman-tours-manager' ),
                    'scheduled' => __( 'Scheduled', 'cayman-tours-manager' ),
                    'cold' => __( 'Gone cold', 'cayman-tours-manager' ),
                );
                echo isset( $labels[ $s ] ) ? esc_html( $labels[ $s ] ) : esc_html( $s );
                break;
        }
    }

    /**
     * Add quick row actions for client posts (status change + delete/edit are already present)
     */
    public function client_row_actions( $actions, $post ) {
        if ( isset( $post ) && $post->post_type === 'ctm_client' ) {
            $id = $post->ID;
            $base = admin_url( 'admin-post.php' );
            $nonce_contact = wp_create_nonce( 'ctm_client_status_' . $id );
            $actions['mark_contacted'] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'ctm_client_update_status', 'client_id' => $id, 'status' => 'contacted', '_wpnonce' => $nonce_contact ), $base ) ) . '">' . esc_html__( 'Mark Contacted', 'cayman-tours-manager' ) . '</a>';
            $actions['mark_scheduled'] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'ctm_client_update_status', 'client_id' => $id, 'status' => 'scheduled', '_wpnonce' => $nonce_contact ), $base ) ) . '">' . esc_html__( 'Mark Scheduled', 'cayman-tours-manager' ) . '</a>';
            $actions['mark_cold'] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'ctm_client_update_status', 'client_id' => $id, 'status' => 'cold', '_wpnonce' => $nonce_contact ), $base ) ) . '">' . esc_html__( 'Mark Gone cold', 'cayman-tours-manager' ) . '</a>';
        }
        return $actions;
    }
    
    public function save_package_meta($post_id, $post, $update) {
        // Check nonce
        if (!isset($_POST['ctm_package_meta_nonce']) || 
            !wp_verify_nonce($_POST['ctm_package_meta_nonce'], 'ctm_package_meta')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Update post title and content if provided
        if (isset($_POST['_package_title']) || isset($_POST['_package_description'])) {
            $update_post = array('ID' => $post_id);
            
            if (isset($_POST['_package_title'])) {
                $update_post['post_title'] = sanitize_text_field($_POST['_package_title']);
            }
            
            if (isset($_POST['_package_description'])) {
                $update_post['post_content'] = wp_kses_post($_POST['_package_description']);
            }
            
            // Unhook to prevent infinite loop
            remove_action('save_post_' . $this->package_cpt, array($this, 'save_package_meta'), 10);
            wp_update_post($update_post);
            add_action('save_post_' . $this->package_cpt, array($this, 'save_package_meta'), 10, 3);
        }
        
        // Save basic fields
        $fields = array(
            '_package_code',
            '_duration_type',
            '_duration_value',
            '_difficulty',
            '_min_age',
            '_max_participants',
            '_meeting_point',
            '_base_price',
            '_child_price',
            '_child_age_range',
            '_infant_price',
            '_single_supplement',
            '_commission_rate',
            '_start_time',
            '_end_time',
            '_recurring_pattern',
            '_cancellation_policy',
            '_requirements',
            '_what_to_bring',
            '_cta_primary_text',
            '_cta_primary_type',
            '_cta_primary_value',
            '_cta_secondary_text',
            '_cta_secondary_type',
            '_cta_secondary_value',
            '_cta_message',
        );
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        // Save inclusions (array from multiple inputs)
        if (isset($_POST['_inclusions_items'])) {
            $inclusions = array_filter(array_map('sanitize_text_field', $_POST['_inclusions_items']), 'strlen');
            update_post_meta($post_id, '_inclusions', $inclusions);
        }
        
        // Save exclusions (array from multiple inputs)
        if (isset($_POST['_exclusions_items'])) {
            $exclusions = array_filter(array_map('sanitize_text_field', $_POST['_exclusions_items']), 'strlen');
            update_post_meta($post_id, '_exclusions', $exclusions);
        }
        
        // Save add-ons (array of objects with name and price)
        if (isset($_POST['_addons_name']) && isset($_POST['_addons_price'])) {
            $addon_names = array_map('sanitize_text_field', $_POST['_addons_name']);
            $addon_prices = array_map('sanitize_text_field', $_POST['_addons_price']);
            $addons = array();
            
            foreach ($addon_names as $index => $name) {
                if (!empty($name)) {
                    $addons[] = array(
                        'name' => $name,
                        'price' => isset($addon_prices[$index]) ? floatval($addon_prices[$index]) : 0
                    );
                }
            }
            
            update_post_meta($post_id, '_addons', $addons);
        }
        
        // Save seasonal pricing (array of objects)
        if (isset($_POST['_seasonal_name']) && isset($_POST['_seasonal_start']) && 
            isset($_POST['_seasonal_end']) && isset($_POST['_seasonal_price'])) {
            
            $seasonal_names = array_map('sanitize_text_field', $_POST['_seasonal_name']);
            $seasonal_starts = array_map('sanitize_text_field', $_POST['_seasonal_start']);
            $seasonal_ends = array_map('sanitize_text_field', $_POST['_seasonal_end']);
            $seasonal_prices = array_map('sanitize_text_field', $_POST['_seasonal_price']);
            $seasonal_pricing = array();
            
            foreach ($seasonal_names as $index => $name) {
                if (!empty($name) && !empty($seasonal_starts[$index]) && 
                    !empty($seasonal_ends[$index]) && !empty($seasonal_prices[$index])) {
                    $seasonal_pricing[] = array(
                        'name' => $name,
                        'start_date' => $seasonal_starts[$index],
                        'end_date' => $seasonal_ends[$index],
                        'price' => floatval($seasonal_prices[$index])
                    );
                }
            }
            
            update_post_meta($post_id, '_seasonal_pricing', $seasonal_pricing);
        }
        
        // Save JSON/array fields (for itinerary and schedule template which still use JSON editor)
        $array_fields = array(
            '_itinerary',
            '_schedule_template',
        );
        
        foreach ($array_fields as $field) {
            if (isset($_POST[$field])) {
                $data = json_decode(stripslashes($_POST[$field]), true);
                if (is_array($data)) {
                    update_post_meta($post_id, $field, $data);
                }
            }
        }
        
        // Save checkbox fields
        $checkbox_fields = array(
            '_cta_show_availability',
        );
        
        foreach ($checkbox_fields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] == '1') {
                update_post_meta($post_id, $field, '1');
            } else {
                update_post_meta($post_id, $field, '0');
            }
        }

        // Save linked clients (array of client post IDs)
        if (isset($_POST['_clients']) && is_array($_POST['_clients'])) {
            $clients = array_map('intval', wp_unslash($_POST['_clients']));
            update_post_meta($post_id, '_clients', $clients);
        } else {
            // If none provided, ensure meta is removed
            delete_post_meta($post_id, '_clients');
        }
        
        // Auto-generate package code if empty
        if (empty(get_post_meta($post_id, '_package_code', true))) {
            $code = 'CTM-' . strtoupper(substr(md5($post_id . time()), 0, 8));
            update_post_meta($post_id, '_package_code', $code);
        }
    }
    
    public function add_package_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ('title' === $key) {
                $new_columns['package_code'] = __('Package Code', 'cayman-tours-manager');
                $new_columns['price'] = __('Price', 'cayman-tours-manager');
                $new_columns['bookings'] = __('Bookings', 'cayman-tours-manager');
                $new_columns['status'] = __('Status', 'cayman-tours-manager');
            }
        }
        
        return $new_columns;
    }
    
    public function render_package_columns($column, $post_id) {
        switch ($column) {
            case 'package_code':
                $code = get_post_meta($post_id, '_package_code', true);
                echo '<code>' . esc_html($code) . '</code>';
                break;
                
            case 'price':
                $price = get_post_meta($post_id, '_base_price', true);
                echo '$' . number_format(floatval($price), 2);
                break;
                
            case 'bookings':
                $count = $this->get_booking_count($post_id);
                echo '<strong>' . intval($count) . '</strong>';
                break;
                
            case 'status':
                $status = get_post_status($post_id);
                $status_labels = array(
                    'publish' => '<span class="ctm-status status-published">' . __('Published', 'cayman-tours-manager') . '</span>',
                    'draft' => '<span class="ctm-status status-draft">' . __('Draft', 'cayman-tours-manager') . '</span>',
                    'private' => '<span class="ctm-status status-private">' . __('Private', 'cayman-tours-manager') . '</span>',
                );
                
                echo isset($status_labels[$status]) ? $status_labels[$status] : $status;
                break;
        }
    }
    
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        if ($this->package_cpt !== $post_type && 
            strpos($hook, 'cayman-tours') === false) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'ctm-package-admin',
            plugin_dir_url(__FILE__) . '../admin/css/package-admin.css',
            array(),
            CTM_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'ctm-package-builder',
            plugin_dir_url(__FILE__) . '../admin/js/package-builder.js',
            array('jquery', 'jquery-ui-sortable', 'flatpickr'),
            CTM_VERSION,
            true
        );
        // Quick add client script
        wp_enqueue_script(
            'ctm-client-quick-add',
            plugin_dir_url(__FILE__) . '../admin/js/client-quick-add.js',
            array('jquery'),
            CTM_VERSION,
            true
        );
        wp_localize_script('ctm-client-quick-add', 'ctm_client_quick', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ctm_create_client'),
            'texts' => array(
                'creating' => __('Creating...', 'cayman-tours-manager'),
                'create_failed' => __('Create failed', 'cayman-tours-manager'),
            )
        ));
        
        // Localize script
        wp_localize_script('ctm-package-builder', 'ctm_package', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ctm_package_nonce'),
            'post_id' => get_the_ID(),
            'texts' => array(
                'add_item' => __('Add Item', 'cayman-tours-manager'),
                'remove_item' => __('Remove', 'cayman-tours-manager'),
                'saving' => __('Saving...', 'cayman-tours-manager'),
                'saved' => __('Saved!', 'cayman-tours-manager'),
            )
        ));
        
        // Flatpickr for date/time
        wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js', array(), null, true);
    }

    /**
     * Provide a frontend single template for the `ctm_package` CPT when available
     */
    public function load_single_template( $template ) {
        if ( is_singular( $this->package_cpt ) ) {
            $custom = plugin_dir_path( __FILE__ ) . '../public/templates/single-ctm_package.php';
            if ( is_file( $custom ) ) {
                return $custom;
            }
        }

        return $template;
    }

    /**
     * One-time migration: copy legacy 'ctm_itinerary' meta into '_itinerary'
     */
    public function maybe_migrate_itinerary_meta() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $flag = get_option( 'ctm_itinerary_migrated', '0' );
        if ( '1' === $flag ) return;

        // find posts that have ctm_itinerary meta
        global $wpdb;
        $meta_key = 'ctm_itinerary';
        $rows = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s", $meta_key ) );
        if ( ! empty( $rows ) ) {
            foreach ( $rows as $r ) {
                $post_id = intval( $r->post_id );
                $raw = $r->meta_value;
                $decoded = json_decode( $raw, true );
                if ( is_array( $decoded ) ) {
                    update_post_meta( $post_id, '_itinerary', $decoded );
                } else {
                    // if non-json, store as string anyway
                    update_post_meta( $post_id, '_itinerary', $raw );
                }
            }
        }

        // mark done
        update_option( 'ctm_itinerary_migrated', '1' );
    }
    
    private function get_booking_count($package_id) {
        // This will be implemented with booking system
        return 0;
    }
    
    private function get_package_revenue($package_id) {
        // This will be implemented with booking system
        return 0;
    }
    
    private function get_average_rating($package_id) {
        // This will be implemented with review system
        return 0;
    }
}

// Auto-initialize package manager singleton
if ( class_exists( 'CTM_Package_Manager' ) ) {
    CTM_Package_Manager::get_instance();
}