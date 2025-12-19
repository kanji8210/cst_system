<?php
// File: includes/class-ctm-database-manager.php

if (!defined('ABSPATH')) {
    exit;
}

class CTM_Database_Manager {
    
    private $tables = array();
    private $table_prefix;
    private $charset_collate;
    
    public function __construct() {
        global $wpdb;
        $this->table_prefix = $wpdb->prefix . 'ctm_';
        $this->charset_collate = $wpdb->get_charset_collate();
        $this->define_tables();
    }
    
    private function define_tables() {
        $this->tables = array(
            'locations' => $this->table_prefix . 'locations',
            'packages' => $this->table_prefix . 'packages',
            'pricing' => $this->table_prefix . 'pricing',
            'itinerary' => $this->table_prefix . 'itinerary',
            'inclusions' => $this->table_prefix . 'inclusions',
            'schedule_templates' => $this->table_prefix . 'schedule_templates',
            'tours' => $this->table_prefix . 'tours',
            'schedules' => $this->table_prefix . 'schedules',
            'bookings' => $this->table_prefix . 'bookings',
            'coupons' => $this->table_prefix . 'coupons',
            'payments' => $this->table_prefix . 'payments',
            'waitlist' => $this->table_prefix . 'waitlist',
        );
    }
    
    public function get_table_sql() {
        return array(
            'locations' => "
                CREATE TABLE {$this->tables['locations']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    latitude DECIMAL(10,6) DEFAULT NULL,
                    longitude DECIMAL(10,6) DEFAULT NULL,
                    description TEXT,
                    featured_image VARCHAR(255) DEFAULT NULL,
                    other_images LONGTEXT DEFAULT NULL,
                    status ENUM('published','draft') DEFAULT 'draft',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_name (name),
                    INDEX idx_status (status)
                ) {$this->charset_collate};
            ",
            'packages' => "
                CREATE TABLE {$this->tables['packages']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) NOT NULL,
                    description LONGTEXT,
                    package_type VARCHAR(50),
                    duration_type VARCHAR(20),
                    duration_value VARCHAR(50),
                    min_age INT DEFAULT 0,
                    difficulty_level VARCHAR(50),
                    featured_image VARCHAR(255),
                    status ENUM('draft','published','archived') DEFAULT 'draft',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_slug (slug),
                    INDEX idx_status (status)
                ) {$this->charset_collate};
            ",
            'pricing' => "
                CREATE TABLE {$this->tables['pricing']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    package_id INT NOT NULL,
                    base_price_adult DECIMAL(10,2) DEFAULT 0.00,
                    price_child TEXT,
                    single_supplement DECIMAL(10,2) DEFAULT 0.00,
                    seasonal_prices LONGTEXT,
                    commission_rate DECIMAL(5,2) DEFAULT 0.00,
                    profit_margin DECIMAL(5,2) DEFAULT 0.00,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (package_id) REFERENCES {$this->tables['packages']}(id) ON DELETE CASCADE,
                    INDEX idx_package_id (package_id)
                ) {$this->charset_collate};
            ",
            'itinerary' => "
                CREATE TABLE {$this->tables['itinerary']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    package_id INT NOT NULL,
                    day_number INT DEFAULT 1,
                    time_slot VARCHAR(50),
                    activity_title VARCHAR(255),
                    activity_desc LONGTEXT,
                    location VARCHAR(255),
                    included_items LONGTEXT,
                    sort_order INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (package_id) REFERENCES {$this->tables['packages']}(id) ON DELETE CASCADE,
                    INDEX idx_package_day (package_id, day_number)
                ) {$this->charset_collate};
            ",
            'inclusions' => "
                CREATE TABLE {$this->tables['inclusions']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    package_id INT NOT NULL,
                    item_type ENUM('inclusion','exclusion','addon') DEFAULT 'inclusion',
                    item_name VARCHAR(255),
                    description LONGTEXT,
                    addon_price DECIMAL(10,2) DEFAULT NULL,
                    sort_order INT DEFAULT 0,
                    FOREIGN KEY (package_id) REFERENCES {$this->tables['packages']}(id) ON DELETE CASCADE,
                    INDEX idx_package (package_id)
                ) {$this->charset_collate};
            ",
            'schedule_templates' => "
                CREATE TABLE {$this->tables['schedule_templates']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    package_id INT NOT NULL,
                    start_time TIME,
                    end_time TIME,
                    recurring_pattern VARCHAR(255),
                    max_participants INT DEFAULT 0,
                    guide_requirements VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (package_id) REFERENCES {$this->tables['packages']}(id) ON DELETE CASCADE,
                    INDEX idx_package (package_id)
                ) {$this->charset_collate};
            ",
            'tours' => "
                CREATE TABLE {$this->tables['tours']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id BIGINT(20) UNSIGNED,
                    base_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    max_participants INT DEFAULT 20,
                    duration_days INT DEFAULT 1,
                    duration_hours INT DEFAULT 0,
                    difficulty ENUM('easy', 'medium', 'hard', 'extreme') DEFAULT 'medium',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_post_id (post_id)
                ) {$this->charset_collate};
            ",
            'schedules' => "
                CREATE TABLE {$this->tables['schedules']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tour_id INT NOT NULL,
                    schedule_date DATE NOT NULL,
                    start_time TIME,
                    end_time TIME,
                    available_slots INT NOT NULL,
                    booked_slots INT DEFAULT 0,
                    guide_name VARCHAR(100),
                    status ENUM('active', 'full', 'cancelled', 'completed') DEFAULT 'active',
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (tour_id) REFERENCES {$this->tables['tours']}(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_tour_schedule (tour_id, schedule_date, start_time),
                    INDEX idx_date_status (schedule_date, status)
                ) {$this->charset_collate};
            ",
            'bookings' => "
                CREATE TABLE {$this->tables['bookings']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    schedule_id INT NOT NULL,
                    customer_id BIGINT(20) UNSIGNED,
                    first_name VARCHAR(100) NOT NULL,
                    last_name VARCHAR(100) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    phone VARCHAR(50),
                    participants INT NOT NULL DEFAULT 1,
                    total_amount DECIMAL(10,2) NOT NULL,
                    deposit_paid DECIMAL(10,2) DEFAULT 0.00,
                    payment_status ENUM('pending', 'partial', 'paid', 'refunded', 'cancelled') DEFAULT 'pending',
                    booking_status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
                    coupon_code VARCHAR(50),
                    discount_amount DECIMAL(10,2) DEFAULT 0.00,
                    special_requests TEXT,
                    payment_method VARCHAR(50),
                    transaction_id VARCHAR(255),
                    ip_address VARCHAR(45),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (schedule_id) REFERENCES {$this->tables['schedules']}(id) ON DELETE CASCADE,
                    INDEX idx_customer_email (email),
                    INDEX idx_booking_status (booking_status),
                    INDEX idx_payment_status (payment_status),
                    INDEX idx_created_at (created_at)
                ) {$this->charset_collate};
            ",
            'coupons' => "
                CREATE TABLE {$this->tables['coupons']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    code VARCHAR(50) UNIQUE NOT NULL,
                    discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
                    discount_value DECIMAL(10,2) NOT NULL,
                    min_amount DECIMAL(10,2) DEFAULT 0.00,
                    max_uses INT DEFAULT NULL,
                    used_count INT DEFAULT 0,
                    valid_from DATE,
                    valid_until DATE,
                    applicable_tours TEXT,
                    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_code_status (code, status),
                    INDEX idx_valid_dates (valid_from, valid_until)
                ) {$this->charset_collate};
            ",
            'payments' => "
                CREATE TABLE {$this->tables['payments']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    booking_id INT NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    payment_method VARCHAR(50) NOT NULL,
                    transaction_id VARCHAR(255),
                    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
                    gateway_response TEXT,
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (booking_id) REFERENCES {$this->tables['bookings']}(id) ON DELETE CASCADE,
                    INDEX idx_booking_id (booking_id),
                    INDEX idx_status (status),
                    INDEX idx_transaction_id (transaction_id)
                ) {$this->charset_collate};
            ",
            'waitlist' => "
                CREATE TABLE {$this->tables['waitlist']} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tour_id INT NOT NULL,
                    customer_email VARCHAR(255) NOT NULL,
                    customer_name VARCHAR(100),
                    participants INT DEFAULT 1,
                    preferred_date DATE,
                    status ENUM('active', 'notified', 'booked', 'cancelled') DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (tour_id) REFERENCES {$this->tables['tours']}(id) ON DELETE CASCADE,
                    INDEX idx_tour_status (tour_id, status),
                    INDEX idx_email (customer_email)
                ) {$this->charset_collate};
            "
        );
    }
    
    public function install_tables() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        global $wpdb;
        $installed = array();
        $errors = array();
        
        // Disable foreign key checks temporarily
        $wpdb->query('SET foreign_key_checks = 0');
        
        foreach ($this->get_table_sql() as $table_name => $sql) {
            $result = dbDelta($sql);
            
            if (empty($wpdb->last_error)) {
                $installed[$table_name] = true;
            } else {
                $errors[$table_name] = $wpdb->last_error;
            }
        }
        
        // Re-enable foreign key checks
        $wpdb->query('SET foreign_key_checks = 1');
        
        // Store installed version
        if (empty($errors)) {
            update_option('ctm_db_version', CTM_DB_VERSION);
            update_option('ctm_tables_installed', $installed);
        }
        
        return array(
            'installed' => $installed,
            'errors' => $errors
        );
    }

    /**
     * CRUD helpers for locations
     */
    public function get_locations( $args = array() ) {
        global $wpdb;
        $table = $this->tables['locations'];

        $defaults = array(
            'limit' => 100,
            'offset' => 0,
            'status' => null,
        );
        $args = wp_parse_args( $args, $defaults );

        $where = '';
        if ( ! empty( $args['status'] ) ) {
            $where = $wpdb->prepare( " WHERE status = %s", $args['status'] );
            $sql = "SELECT * FROM {$table} " . $where . " ORDER BY id DESC LIMIT %d OFFSET %d";
            return $wpdb->get_results( $wpdb->prepare( $sql, $args['limit'], $args['offset'] ), ARRAY_A );
        }

        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d", $args['limit'], $args['offset'] ), ARRAY_A );
    }

    public function get_location( $id ) {
        global $wpdb;
        $table = $this->tables['locations'];
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A );
    }

    public function insert_location( $data ) {
        global $wpdb;
        $table = $this->tables['locations'];

        $insert = array(
            'name' => sanitize_text_field( $data['name'] ),
            'latitude' => isset( $data['latitude'] ) ? floatval( $data['latitude'] ) : null,
            'longitude' => isset( $data['longitude'] ) ? floatval( $data['longitude'] ) : null,
            'description' => isset( $data['description'] ) ? wp_kses_post( $data['description'] ) : '',
            // featured_image may be a URL or an attachment ID — store as string
            'featured_image' => isset( $data['featured_image'] ) ? esc_url_raw( (string) $data['featured_image'] ) : null,
            // other_images can be array of URLs or IDs — serialize for storage
            'other_images' => isset( $data['other_images'] ) ? maybe_serialize( $data['other_images'] ) : null,
            'status' => isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'draft',
        );

        $result = $wpdb->insert( $table, $insert );
        if ( $result === false ) {
            return new WP_Error( 'db_insert_error', $wpdb->last_error );
        }

        return $wpdb->insert_id;
    }

    public function update_location( $id, $data ) {
        global $wpdb;
        $table = $this->tables['locations'];

        $update = array();
        if ( isset( $data['name'] ) ) $update['name'] = sanitize_text_field( $data['name'] );
        if ( isset( $data['latitude'] ) ) $update['latitude'] = floatval( $data['latitude'] );
        if ( isset( $data['longitude'] ) ) $update['longitude'] = floatval( $data['longitude'] );
        if ( isset( $data['description'] ) ) $update['description'] = wp_kses_post( $data['description'] );
        if ( isset( $data['featured_image'] ) ) $update['featured_image'] = esc_url_raw( (string) $data['featured_image'] );
        if ( isset( $data['other_images'] ) ) $update['other_images'] = maybe_serialize( $data['other_images'] );
        if ( isset( $data['status'] ) ) $update['status'] = sanitize_text_field( $data['status'] );

        $where = array( 'id' => intval( $id ) );

        $result = $wpdb->update( $table, $update, $where );
        if ( $result === false ) {
            return new WP_Error( 'db_update_error', $wpdb->last_error );
        }

        return true;
    }

    public function delete_location( $id ) {
        global $wpdb;
        $table = $this->tables['locations'];

        $result = $wpdb->delete( $table, array( 'id' => intval( $id ) ) );
        if ( $result === false ) {
            return new WP_Error( 'db_delete_error', $wpdb->last_error );
        }

        return true;
    }
    
    public function check_tables() {
        global $wpdb;
        
        $results = array();
        $all_tables_exist = true;
        $missing_tables = array();
        
        foreach ($this->tables as $key => $table_name) {
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
            $results[$key] = array(
                'table_name' => $table_name,
                'exists' => $table_exists,
                'row_count' => $table_exists ? intval($wpdb->get_var("SELECT COUNT(*) FROM {$table_name}")) : 0,
                'structure_ok' => $table_exists ? $this->check_table_structure($table_name, $key) : false
            );
            
            if (!$table_exists) {
                $all_tables_exist = false;
                $missing_tables[] = $table_name;
            }
        }
        
        return array(
            'all_tables_exist' => $all_tables_exist,
            'missing_tables' => $missing_tables,
            'table_details' => $results
        );
    }
    
    private function check_table_structure($table_name, $table_key) {
        global $wpdb;
        
        // Get actual columns
        $actual_columns = $wpdb->get_results("DESCRIBE {$table_name}", ARRAY_A);
        $actual_col_names = array_column($actual_columns, 'Field');
        
        // Expected columns (simplified check - in production, check exact structure)
        $expected_columns = array(
            'packages' => ['id', 'title', 'slug', 'status'],
            'locations' => ['id', 'name', 'latitude', 'longitude', 'featured_image'],
            'tours' => ['id', 'post_id', 'base_price', 'max_participants'],
            'schedules' => ['id', 'tour_id', 'schedule_date', 'available_slots'],
            'bookings' => ['id', 'schedule_id', 'first_name', 'email', 'participants'],
            'coupons' => ['id', 'code', 'discount_type', 'discount_value'],
            'payments' => ['id', 'booking_id', 'amount', 'payment_method'],
            'waitlist' => ['id', 'tour_id', 'customer_email', 'status']
        );
        
        if (!isset($expected_columns[$table_key])) {
            return false;
        }
        
        // Check if all expected columns exist
        foreach ($expected_columns[$table_key] as $expected_col) {
            if (!in_array($expected_col, $actual_col_names)) {
                return false;
            }
        }
        
        return true;
    }
    
    public function repair_table($table_name) {
        global $wpdb;
        
        if (!in_array($table_name, $this->tables)) {
            return new WP_Error('invalid_table', 'Invalid table name');
        }
        
        $wpdb->query("REPAIR TABLE {$table_name}");
        
        if ($wpdb->last_error) {
            return new WP_Error('repair_failed', $wpdb->last_error);
        }
        
        return true;
    }
    
    public function create_missing_tables($table_keys = array()) {
        global $wpdb;
        $results = array();

        // Ensure dbDelta is available
        if ( ! function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $wpdb->query('SET foreign_key_checks = 0');

        $all_sql = $this->get_table_sql();

        foreach ($table_keys as $table_key) {
            if (isset($all_sql[$table_key])) {
                $sql = $all_sql[$table_key];
                $result = dbDelta($sql);

                $results[$table_key] = array(
                    'success' => empty($wpdb->last_error),
                    'message' => $wpdb->last_error ?: 'Table created successfully',
                    'dbDelta_result' => $result,
                );
            }
        }

        $wpdb->query('SET foreign_key_checks = 1');

        return $results;
    }
    
    public function reset_all_tables() {
        global $wpdb;
        
        $wpdb->query('SET foreign_key_checks = 0');
        
        foreach (array_reverse($this->tables) as $table_name) {
            $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        }
        
        $wpdb->query('SET foreign_key_checks = 1');
        
        return $this->install_tables();
    }
    
    public function get_table_stats() {
        global $wpdb;
        
        $stats = array();
        foreach ($this->tables as $key => $table_name) {
            if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name) {
                $stats[$key] = array(
                    'rows' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}"),
                    'size' => $this->get_table_size($table_name)
                );
            }
        }
        
        return $stats;
    }
    
    private function get_table_size($table_name) {
        global $wpdb;
        
        $result = $wpdb->get_row("
            SELECT 
                ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                data_length,
                index_length
            FROM information_schema.TABLES 
            WHERE table_schema = DATABASE()
            AND table_name = '{$table_name}'
        ", ARRAY_A);
        
        return $result ? $result['size_mb'] . ' MB' : 'N/A';
    }
}