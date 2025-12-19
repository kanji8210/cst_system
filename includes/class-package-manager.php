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
        add_action('add_meta_boxes', array($this, 'add_package_meta_boxes'));
        add_action('save_post_' . $this->package_cpt, array($this, 'save_package_meta'), 10, 3);
        add_filter('manage_' . $this->package_cpt . '_posts_columns', array($this, 'add_package_columns'));
        add_action('manage_' . $this->package_cpt . '_posts_custom_column', array($this, 'render_package_columns'), 10, 2);
        add_action('admin_menu', array($this, 'add_package_admin_pages'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        // Frontend template loader for single package view
        add_filter('single_template', array($this, 'load_single_template'));
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
            'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
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
            'side',
            'default'
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
        );
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        // Save JSON/array fields
        $array_fields = array(
            '_seasonal_pricing',
            '_itinerary',
            '_schedule_template',
            '_inclusions',
            '_exclusions',
            '_addons',
            '_requirements',
            '_what_to_bring',
        );
        
        foreach ($array_fields as $field) {
            if (isset($_POST[$field])) {
                $data = json_decode(stripslashes($_POST[$field]), true);
                if (is_array($data)) {
                    update_post_meta($post_id, $field, $data);
                }
            }
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