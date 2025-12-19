<?php
// File: admin/class-ctm-database-widget.php

if (!defined('ABSPATH')) {
    exit;
}

class CTM_Database_Widget {
    
    private $db_manager;
    
    public function __construct($db_manager) {
        $this->db_manager = $db_manager;
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_ctm_manage_database', array($this, 'handle_ajax_requests'));
    }
    
    public function enqueue_scripts($hook) {
        if ('index.php' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            'ctm-database-widget',
            plugin_dir_url(__FILE__) . 'css/database-widget.css',
            array(),
            CTM_VERSION
        );
        
        wp_enqueue_script(
            'ctm-database-widget',
            plugin_dir_url(__FILE__) . 'js/database-widget.js',
            array('jquery'),
            CTM_VERSION,
            true
        );
        
        wp_localize_script('ctm-database-widget', 'ctm_db_widget', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ctm_database_management'),
            'texts' => array(
                'creating' => __('Creating tables...', 'cayman-tours-manager'),
                'success' => __('Operation completed successfully!', 'cayman-tours-manager'),
                'error' => __('An error occurred:', 'cayman-tours-manager'),
                'confirm_reset' => __('WARNING: This will delete ALL tour data. Are you sure?', 'cayman-tours-manager')
            )
        ));
    }
    
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'ctm_database_widget',
            __('Cayman Tours - Database Status', 'cayman-tours-manager'),
            array($this, 'render_widget')
        );
    }
    
    public function render_widget() {
        $table_status = $this->db_manager->check_tables();
        $table_stats = $this->db_manager->get_table_stats();
        ?>
        
        <div class="ctm-database-widget">
            <!-- Status Overview -->
            <div class="ctm-db-status-header">
                <h3><?php _e('Database Health Check', 'cayman-tours-manager'); ?></h3>
                <div class="ctm-db-overall-status">
                    <span class="ctm-status-indicator <?php echo $table_status['all_tables_exist'] ? 'status-good' : 'status-bad'; ?>"></span>
                    <?php if ($table_status['all_tables_exist']): ?>
                        <span class="ctm-status-text"><?php _e('All tables are installed', 'cayman-tours-manager'); ?></span>
                    <?php else: ?>
                        <span class="ctm-status-text"><?php _e('Missing tables detected', 'cayman-tours-manager'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Table Details -->
            <div class="ctm-db-tables-list">
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Table', 'cayman-tours-manager'); ?></th>
                            <th><?php _e('Status', 'cayman-tours-manager'); ?></th>
                            <th><?php _e('Rows', 'cayman-tours-manager'); ?></th>
                            <th><?php _e('Size', 'cayman-tours-manager'); ?></th>
                            <th><?php _e('Actions', 'cayman-tours-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($table_status['table_details'] as $key => $table): ?>
                        <tr>
                            <td><code><?php echo esc_html($table['table_name']); ?></code></td>
                            <td>
                                <?php if ($table['exists']): ?>
                                    <?php if ($table['structure_ok']): ?>
                                        <span class="ctm-table-status status-good">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                            <?php _e('OK', 'cayman-tours-manager'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="ctm-table-status status-warning">
                                            <span class="dashicons dashicons-warning"></span>
                                            <?php _e('Structure Issue', 'cayman-tours-manager'); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="ctm-table-status status-bad">
                                        <span class="dashicons dashicons-dismiss"></span>
                                        <?php _e('Missing', 'cayman-tours-manager'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($table['row_count']); ?></td>
                            <td>
                                <?php echo isset($table_stats[$key]['size']) ? esc_html($table_stats[$key]['size']) : 'N/A'; ?>
                            </td>
                            <td>
                                <?php if (!$table['exists']): ?>
                                    <button class="button button-small ctm-create-table" data-table="<?php echo esc_attr($key); ?>">
                                        <?php _e('Create', 'cayman-tours-manager'); ?>
                                    </button>
                                <?php elseif (!$table['structure_ok']): ?>
                                    <button class="button button-small ctm-repair-table" data-table="<?php echo esc_attr($table['table_name']); ?>">
                                        <?php _e('Repair', 'cayman-tours-manager'); ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Action Buttons -->
            <div class="ctm-db-actions">
                <button class="button button-secondary ctm-create-missing-tables">
                    <span class="dashicons dashicons-plus"></span>
                    <?php _e('Create All Missing Tables', 'cayman-tours-manager'); ?>
                </button>
                
                <button class="button button-secondary ctm-check-again">
                    <span class="dashicons dashicons-update"></span>
                    <?php _e('Check Again', 'cayman-tours-manager'); ?>
                </button>
                
                <?php if (current_user_can('manage_options')): ?>
                <button class="button button-danger ctm-reset-database" style="float: right;">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Reset All Data', 'cayman-tours-manager'); ?>
                </button>
                <?php endif; ?>
            </div>
            
            <!-- Messages Area -->
            <div id="ctm-db-messages" class="ctm-db-messages"></div>
            
            <!-- Progress Bar -->
            <div id="ctm-db-progress" class="ctm-db-progress" style="display: none;">
                <div class="ctm-progress-bar">
                    <div class="ctm-progress-fill"></div>
                </div>
                <span class="ctm-progress-text">0%</span>
            </div>
        </div>
        
        <?php
    }
    
    public function handle_ajax_requests() {
        // Verify nonce
        if (!check_ajax_referer('ctm_database_management', 'nonce', false)) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $action = sanitize_text_field($_POST['action_type'] ?? '');
        
        switch ($action) {
            case 'check_tables':
                $result = $this->db_manager->check_tables();
                wp_send_json_success($result);
                break;
                
            case 'create_table':
                $table_key = sanitize_text_field($_POST['table_key'] ?? '');
                $result = $this->db_manager->create_missing_tables(array($table_key));
                wp_send_json_success($result);
                break;
                
            case 'create_missing':
                $table_status = $this->db_manager->check_tables();
                $missing_keys = array();
                
                foreach ($table_status['table_details'] as $key => $table) {
                    if (!$table['exists']) {
                        $missing_keys[] = $key;
                    }
                }
                
                $result = $this->db_manager->create_missing_tables($missing_keys);
                wp_send_json_success($result);
                break;
                
            case 'repair_table':
                $table_name = sanitize_text_field($_POST['table_name'] ?? '');
                $result = $this->db_manager->repair_table($table_name);
                
                if (is_wp_error($result)) {
                    wp_send_json_error($result->get_error_message());
                } else {
                    wp_send_json_success(array('message' => 'Table repaired successfully'));
                }
                break;
                
            case 'reset_database':
                // Double-check user wants this
                $confirm = isset($_POST['confirmed']) && $_POST['confirmed'] === 'true';
                
                if (!$confirm) {
                    wp_send_json_error(array(
                        'needs_confirmation' => true,
                        'message' => 'Please confirm you want to reset all data'
                    ));
                }
                
                $result = $this->db_manager->reset_all_tables();
                wp_send_json_success($result);
                break;
                
            default:
                wp_send_json_error('Invalid action');
        }
        
        wp_die();
    }
}