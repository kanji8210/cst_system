<?php
// File: includes/models/class-package.php

if (!defined('ABSPATH')) {
    exit;
}

class CTM_Package {
    
    private $post_id;
    private $data = array();
    
    public function __construct($post_id = null) {
        if ($post_id) {
            $this->post_id = $post_id;
            $this->load_data();
        }
    }
    
    private function load_data() {
        $this->data = array(
            'title' => get_the_title($this->post_id),
            'content' => get_post_field('post_content', $this->post_id),
            'excerpt' => get_post_field('post_excerpt', $this->post_id),
            'status' => get_post_status($this->post_id),
            'package_code' => get_post_meta($this->post_id, '_package_code', true),
            'duration_type' => get_post_meta($this->post_id, '_duration_type', true),
            'duration_value' => get_post_meta($this->post_id, '_duration_value', true),
            'difficulty' => get_post_meta($this->post_id, '_difficulty', true),
            'min_age' => get_post_meta($this->post_id, '_min_age', true),
            'max_participants' => get_post_meta($this->post_id, '_max_participants', true),
            'base_price' => get_post_meta($this->post_id, '_base_price', true),
            'child_price' => get_post_meta($this->post_id, '_child_price', true),
            'child_age_range' => get_post_meta($this->post_id, '_child_age_range', true),
            'infant_price' => get_post_meta($this->post_id, '_infant_price', true),
            'single_supplement' => get_post_meta($this->post_id, '_single_supplement', true),
            'seasonal_pricing' => get_post_meta($this->post_id, '_seasonal_pricing', true),
            'itinerary' => get_post_meta($this->post_id, '_itinerary', true),
            'inclusions' => get_post_meta($this->post_id, '_inclusions', true),
            'exclusions' => get_post_meta($this->post_id, '_exclusions', true),
            'addons' => get_post_meta($this->post_id, '_addons', true),
            'start_time' => get_post_meta($this->post_id, '_start_time', true),
            'end_time' => get_post_meta($this->post_id, '_end_time', true),
            'recurring_pattern' => get_post_meta($this->post_id, '_recurring_pattern', true),
            'cancellation_policy' => get_post_meta($this->post_id, '_cancellation_policy', true),
            'requirements' => get_post_meta($this->post_id, '_requirements', true),
            'what_to_bring' => get_post_meta($this->post_id, '_what_to_bring', true),
            'featured_image' => get_post_thumbnail_id($this->post_id),
        );
    }
    
    public function create($data) {
        $post_data = array(
            'post_title' => sanitize_text_field($data['title']),
            'post_content' => wp_kses_post($data['content']),
            'post_excerpt' => sanitize_textarea_field($data['excerpt']),
            'post_type' => 'ctm_package',
            'post_status' => isset($data['status']) ? $data['status'] : 'draft',
        );
        
        $this->post_id = wp_insert_post($post_data);
        
        if (is_wp_error($this->post_id)) {
            return false;
        }
        
        // Save meta fields
        $this->update_meta($data);
        
        // Set featured image
        if (isset($data['featured_image']) && !empty($data['featured_image'])) {
            set_post_thumbnail($this->post_id, $data['featured_image']);
        }
        
        // Set taxonomies
        if (isset($data['package_type']) && !empty($data['package_type'])) {
            wp_set_post_terms($this->post_id, $data['package_type'], 'package_type');
        }
        
        if (isset($data['activities']) && !empty($data['activities'])) {
            wp_set_post_terms($this->post_id, $data['activities'], 'activity');
        }
        
        $this->load_data();
        return $this->post_id;
    }
    
    public function update($data) {
        $post_data = array(
            'ID' => $this->post_id,
            'post_title' => sanitize_text_field($data['title']),
            'post_content' => wp_kses_post($data['content']),
            'post_excerpt' => sanitize_textarea_field($data['excerpt']),
            'post_status' => isset($data['status']) ? $data['status'] : $this->data['status'],
        );
        
        wp_update_post($post_data);
        
        // Update meta fields
        $this->update_meta($data);
        
        // Update featured image
        if (isset($data['featured_image'])) {
            set_post_thumbnail($this->post_id, $data['featured_image']);
        }
        
        // Update taxonomies
        if (isset($data['package_type'])) {
            wp_set_post_terms($this->post_id, $data['package_type'], 'package_type');
        }
        
        if (isset($data['activities'])) {
            wp_set_post_terms($this->post_id, $data['activities'], 'activity');
        }
        
        $this->load_data();
        return true;
    }
    
    private function update_meta($data) {
        $meta_fields = array(
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
        
        foreach ($meta_fields as $field) {
            $key = substr($field, 1); // Remove underscore
            if (isset($data[$key])) {
                update_post_meta($this->post_id, $field, sanitize_text_field($data[$key]));
            }
        }
        
        // Handle array/JSON fields
        $array_fields = array(
            '_seasonal_pricing',
            '_itinerary',
            '_inclusions',
            '_exclusions',
            '_addons',
            '_requirements',
            '_what_to_bring',
        );
        
        foreach ($array_fields as $field) {
            $key = substr($field, 1);
            if (isset($data[$key]) && is_array($data[$key])) {
                update_post_meta($this->post_id, $field, $data[$key]);
            }
        }
    }
    
    public function duplicate() {
        $new_post_id = $this->create($this->data);
        
        if ($new_post_id) {
            // Generate new package code
            $new_code = 'CTM-' . strtoupper(substr(md5($new_post_id . time()), 0, 8));
            update_post_meta($new_post_id, '_package_code', $new_code);
            
            // Append " (Copy)" to title
            $new_title = $this->data['title'] . ' (Copy)';
            wp_update_post(array(
                'ID' => $new_post_id,
                'post_title' => $new_title,
                'post_status' => 'draft'
            ));
            
            return $new_post_id;
        }
        
        return false;
    }
    
    public function archive() {
        wp_update_post(array(
            'ID' => $this->post_id,
            'post_status' => 'draft'
        ));
        
        update_post_meta($this->post_id, '_archived_at', current_time('mysql'));
        return true;
    }
    
    public function publish() {
        wp_update_post(array(
            'ID' => $this->post_id,
            'post_status' => 'publish'
        ));
        
        return true;
    }
    
    public function get_price_for_date($date = null, $participants = 1) {
        if (!$date) {
            $date = current_time('Y-m-d');
        }
        
        $base_price = floatval($this->data['base_price']);
        
        // Check seasonal pricing
        if (!empty($this->data['seasonal_pricing']) && is_array($this->data['seasonal_pricing'])) {
            foreach ($this->data['seasonal_pricing'] as $season) {
                if (isset($season['start_date']) && isset($season['end_date']) && isset($season['price'])) {
                    if ($date >= $season['start_date'] && $date <= $season['end_date']) {
                        $base_price = floatval($season['price']);
                        break;
                    }
                }
            }
        }
        
        return $base_price * $participants;
    }
    
    public function is_available_on_date($date) {
        // Check if date is in the future
        if (strtotime($date) < current_time('timestamp')) {
            return false;
        }
        
        // Check recurring pattern
        if (!empty($this->data['recurring_pattern'])) {
            $pattern = $this->data['recurring_pattern'];
            $day_of_week = date('N', strtotime($date)); // 1-7 (Monday-Sunday)
            
            switch ($pattern) {
                case 'daily':
                    return true;
                case 'weekdays':
                    return $day_of_week <= 5; // Monday-Friday
                case 'weekends':
                    return $day_of_week >= 6; // Saturday-Sunday
                case 'monday':
                    return $day_of_week == 1;
                case 'tuesday':
                    return $day_of_week == 2;
                case 'wednesday':
                    return $day_of_week == 3;
                case 'thursday':
                    return $day_of_week == 4;
                case 'friday':
                    return $day_of_week == 5;
                case 'saturday':
                    return $day_of_week == 6;
                case 'sunday':
                    return $day_of_week == 7;
                default:
                    return true;
            }
        }
        
        return true;
    }
    
    public function get_data() {
        return $this->data;
    }
    
    public function get_id() {
        return $this->post_id;
    }
    
    public static function get_all($args = array()) {
        $default_args = array(
            'post_type' => 'ctm_package',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        $args = wp_parse_args($args, $default_args);
        
        $posts = get_posts($args);
        $packages = array();
        
        foreach ($posts as $post) {
            $packages[] = new self($post->ID);
        }
        
        return $packages;
    }
}