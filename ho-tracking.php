<?php
/**
 * Plugin Name: HO Tracking
 * Plugin URI: https://github.com/MehdiSabourii/ho-tracking
 * Description: A WordPress plugin to upload and display postal tracking information with AJAX search functionality
 * Version: 1.0.0
 * Author: Mehdi Sabouri
 * Text Domain: ho-tracking
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HO_TRACKING_VERSION', '1.0.0');
define('HO_TRACKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HO_TRACKING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HO_TRACKING_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class HO_Tracking {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            add_action('wp_ajax_ho_tracking_upload', array($this, 'handle_upload'));
            add_action('wp_ajax_ho_tracking_delete_record', array($this, 'ajax_delete_record'));
            add_action('wp_ajax_ho_tracking_bulk_delete', array($this, 'ajax_bulk_delete'));
            add_action('wp_ajax_ho_tracking_update_record', array($this, 'ajax_update_record'));
            add_action('admin_init', array($this, 'register_settings'));
        }
        
        // Frontend hooks
        add_shortcode('ho_tracking_table', array($this, 'tracking_table_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        add_action('wp_ajax_ho_tracking_search', array($this, 'ajax_search'));
        add_action('wp_ajax_nopriv_ho_tracking_search', array($this, 'ajax_search'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->create_table();
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Create database table
     */
    private function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ho_tracking';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            tracking_code varchar(255) NOT NULL,
            recipient_name varchar(255) DEFAULT NULL,
            status varchar(100) DEFAULT NULL,
            date_sent datetime DEFAULT NULL,
            date_delivered datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY tracking_code (tracking_code)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load plugin text domain
        load_plugin_textdomain('ho-tracking', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('HO Tracking', 'ho-tracking'),
            __('HO Tracking', 'ho-tracking'),
            'manage_options',
            'ho-tracking',
            array($this, 'admin_page'),
            'dashicons-location',
            30
        );
        
        // Submenu - Upload
        add_submenu_page(
            'ho-tracking',
            __('Upload Data', 'ho-tracking'),
            __('Upload Data', 'ho-tracking'),
            'manage_options',
            'ho-tracking',
            array($this, 'admin_page')
        );
        
        // Submenu - Manage Records
        add_submenu_page(
            'ho-tracking',
            __('Manage Records', 'ho-tracking'),
            __('Manage Records', 'ho-tracking'),
            'manage_options',
            'ho-tracking-manage',
            array($this, 'manage_page')
        );
        
        // Submenu - Settings
        add_submenu_page(
            'ho-tracking',
            __('Settings', 'ho-tracking'),
            __('Settings', 'ho-tracking'),
            'manage_options',
            'ho-tracking-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Admin page content (Upload)
     */
    public function admin_page() {
        include HO_TRACKING_PLUGIN_DIR . 'admin/admin-page.php';
    }
    
    /**
     * Manage records page
     */
    public function manage_page() {
        include HO_TRACKING_PLUGIN_DIR . 'admin/manage-page.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        include HO_TRACKING_PLUGIN_DIR . 'admin/settings-page.php';
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on our plugin pages
        if (!in_array($hook, array('toplevel_page_ho-tracking', 'ho-tracking_page_ho-tracking-manage', 'ho-tracking_page_ho-tracking-settings'))) {
            return;
        }
        
        wp_enqueue_style('ho-tracking-admin', HO_TRACKING_PLUGIN_URL . 'assets/css/admin.css', array(), HO_TRACKING_VERSION);
        wp_enqueue_script('ho-tracking-admin', HO_TRACKING_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), HO_TRACKING_VERSION, true);
        
        wp_localize_script('ho-tracking-admin', 'hoTracking', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ho_tracking_nonce'),
            'confirm_delete' => __('Are you sure you want to delete this record?', 'ho-tracking'),
            'confirm_bulk_delete' => __('Are you sure you want to delete the selected records?', 'ho-tracking')
        ));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_enqueue_scripts() {
        wp_enqueue_style('ho-tracking-frontend', HO_TRACKING_PLUGIN_URL . 'assets/css/frontend.css', array(), HO_TRACKING_VERSION);
        wp_enqueue_script('ho-tracking-frontend', HO_TRACKING_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), HO_TRACKING_VERSION, true);
        
        wp_localize_script('ho-tracking-frontend', 'hoTracking', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ho_tracking_search_nonce')
        ));
    }
    
    /**
     * Handle file upload
     */
    public function handle_upload() {
        check_ajax_referer('ho_tracking_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ho-tracking')));
        }
        
        if (!isset($_FILES['tracking_file'])) {
            wp_send_json_error(array('message' => __('No file uploaded', 'ho-tracking')));
        }
        
        $file = $_FILES['tracking_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Check if Excel support is available
        $excel_supported = class_exists('PhpOffice\PhpSpreadsheet\IOFactory') || 
                          file_exists(HO_TRACKING_PLUGIN_DIR . 'vendor/autoload.php');
        
        if (!$excel_supported && in_array($file_ext, array('xls', 'xlsx'))) {
            wp_send_json_error(array('message' => __('Excel support is not available. Please install Composer dependencies or use CSV format.', 'ho-tracking')));
        }
        
        if (!in_array($file_ext, array('csv', 'xls', 'xlsx'))) {
            wp_send_json_error(array('message' => __('Invalid file format. Please upload CSV, XLS, or XLSX file.', 'ho-tracking')));
        }
        
        // Parse the file
        $data = $this->parse_file($file['tmp_name'], $file_ext);
        
        if (is_wp_error($data)) {
            wp_send_json_error(array('message' => $data->get_error_message()));
        }
        
        // Clear existing data if requested
        if (isset($_POST['clear_existing']) && $_POST['clear_existing'] === 'true') {
            $this->clear_tracking_data();
        }
        
        // Insert data
        $inserted = $this->insert_tracking_data($data);
        
        wp_send_json_success(array(
            'message' => sprintf(__('%d records imported successfully', 'ho-tracking'), $inserted)
        ));
    }
    
    /**
     * Parse uploaded file
     */
    private function parse_file($file_path, $file_ext) {
        if ($file_ext === 'csv') {
            return $this->parse_csv($file_path);
        } else {
            return $this->parse_excel($file_path);
        }
    }
    
    /**
     * Parse CSV file
     */
    private function parse_csv($file_path) {
        $data = array();
        $skipped_rows = 0;
        
        if (($handle = fopen($file_path, 'r')) !== false) {
            $headers = fgetcsv($handle);
            
            if (!$headers) {
                fclose($handle);
                return new WP_Error('parse_error', __('Invalid CSV format', 'ho-tracking'));
            }
            
            // Normalize headers
            $headers = array_map('trim', $headers);
            $headers = array_map('strtolower', $headers);
            
            $row_number = 1; // Start from 1 after header
            while (($row = fgetcsv($handle)) !== false) {
                $row_number++;
                if (count($row) === count($headers)) {
                    $data[] = array_combine($headers, $row);
                } else {
                    $skipped_rows++;
                    error_log(sprintf('HO Tracking: Skipped row %d due to column count mismatch (expected %d, got %d)', 
                        $row_number, count($headers), count($row)));
                }
            }
            
            fclose($handle);
            
            if ($skipped_rows > 0) {
                error_log(sprintf('HO Tracking: Skipped %d rows due to data quality issues', $skipped_rows));
            }
        } else {
            return new WP_Error('file_error', __('Could not read file', 'ho-tracking'));
        }
        
        return $data;
    }
    
    /**
     * Parse Excel file
     */
    private function parse_excel($file_path) {
        // Check if PhpSpreadsheet is available
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            require_once HO_TRACKING_PLUGIN_DIR . 'includes/phpspreadsheet-loader.php';
        }
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            if (empty($rows)) {
                return new WP_Error('parse_error', __('Empty Excel file', 'ho-tracking'));
            }
            
            $headers = array_shift($rows);
            $headers = array_map('trim', $headers);
            $headers = array_map('strtolower', $headers);
            
            $data = array();
            $skipped_rows = 0;
            $row_number = 1; // Start from 1 after header
            
            foreach ($rows as $row) {
                $row_number++;
                if (!empty(array_filter($row))) {
                    if (count($row) === count($headers)) {
                        $data[] = array_combine($headers, $row);
                    } else {
                        $skipped_rows++;
                        error_log(sprintf('HO Tracking: Skipped row %d due to column count mismatch (expected %d, got %d)', 
                            $row_number, count($headers), count($row)));
                    }
                }
            }
            
            if ($skipped_rows > 0) {
                error_log(sprintf('HO Tracking: Skipped %d rows due to data quality issues', $skipped_rows));
            }
            
            return $data;
        } catch (Exception $e) {
            return new WP_Error('parse_error', $e->getMessage());
        }
    }
    
    /**
     * Clear tracking data
     */
    private function clear_tracking_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ho_tracking';
        // Sanitize table name and use DELETE for safety
        $table_name = esc_sql($table_name);
        $wpdb->query("DELETE FROM `$table_name`");
    }
    
    /**
     * Insert tracking data
     */
    private function insert_tracking_data($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ho_tracking';
        $inserted = 0;
        
        foreach ($data as $row) {
            // Map common column names
            $tracking_code = '';
            $recipient_name = '';
            $status = '';
            $date_sent = null;
            $date_delivered = null;
            $notes = '';
            
            // Try to find tracking code
            foreach (array('tracking_code', 'tracking', 'code', 'کد رهگیری', 'کد') as $key) {
                if (isset($row[$key]) && !empty($row[$key])) {
                    $tracking_code = sanitize_text_field($row[$key]);
                    break;
                }
            }
            
            // Try to find recipient name
            foreach (array('recipient_name', 'recipient', 'name', 'نام', 'گیرنده') as $key) {
                if (isset($row[$key]) && !empty($row[$key])) {
                    $recipient_name = sanitize_text_field($row[$key]);
                    break;
                }
            }
            
            // Try to find status
            foreach (array('status', 'وضعیت') as $key) {
                if (isset($row[$key]) && !empty($row[$key])) {
                    $status = sanitize_text_field($row[$key]);
                    break;
                }
            }
            
            // Try to find dates
            foreach (array('date_sent', 'sent_date', 'تاریخ ارسال') as $key) {
                if (isset($row[$key]) && !empty($row[$key])) {
                    $date_sent = sanitize_text_field($row[$key]);
                    break;
                }
            }
            
            foreach (array('date_delivered', 'delivered_date', 'تاریخ تحویل') as $key) {
                if (isset($row[$key]) && !empty($row[$key])) {
                    $date_delivered = sanitize_text_field($row[$key]);
                    break;
                }
            }
            
            // Try to find notes
            foreach (array('notes', 'note', 'توضیحات') as $key) {
                if (isset($row[$key]) && !empty($row[$key])) {
                    $notes = sanitize_textarea_field($row[$key]);
                    break;
                }
            }
            
            if (!empty($tracking_code)) {
                $result = $wpdb->insert(
                    $table_name,
                    array(
                        'tracking_code' => $tracking_code,
                        'recipient_name' => $recipient_name,
                        'status' => $status,
                        'date_sent' => $date_sent,
                        'date_delivered' => $date_delivered,
                        'notes' => $notes
                    ),
                    array('%s', '%s', '%s', '%s', '%s', '%s')
                );
                
                if ($result) {
                    $inserted++;
                }
            }
        }
        
        return $inserted;
    }
    
    /**
     * AJAX search handler
     */
    public function ajax_search() {
        check_ajax_referer('ho_tracking_search_nonce', 'nonce');
        
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ho_tracking';
        
        if (!empty($search)) {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE tracking_code LIKE %s OR recipient_name LIKE %s ORDER BY created_at DESC",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            ));
        } else {
            $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 100");
        }
        
        wp_send_json_success(array('data' => $results));
    }
    
    /**
     * Tracking table shortcode
     */
    public function tracking_table_shortcode($atts) {
        ob_start();
        include HO_TRACKING_PLUGIN_DIR . 'templates/tracking-table.php';
        return ob_get_clean();
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('ho_tracking_settings', 'ho_tracking_records_per_page');
        register_setting('ho_tracking_settings', 'ho_tracking_date_format');
        register_setting('ho_tracking_settings', 'ho_tracking_enable_export');
    }
    
    /**
     * AJAX handler for deleting a record
     */
    public function ajax_delete_record() {
        check_ajax_referer('ho_tracking_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ho-tracking')));
        }
        
        $record_id = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;
        
        if (!$record_id) {
            wp_send_json_error(array('message' => __('Invalid record ID', 'ho-tracking')));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ho_tracking';
        
        $deleted = $wpdb->delete($table_name, array('id' => $record_id), array('%d'));
        
        if ($deleted) {
            wp_send_json_success(array('message' => __('Record deleted successfully', 'ho-tracking')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete record', 'ho-tracking')));
        }
    }
    
    /**
     * AJAX handler for bulk delete
     */
    public function ajax_bulk_delete() {
        check_ajax_referer('ho_tracking_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ho-tracking')));
        }
        
        $record_ids = isset($_POST['record_ids']) ? array_map('intval', $_POST['record_ids']) : array();
        
        if (empty($record_ids)) {
            wp_send_json_error(array('message' => __('No records selected', 'ho-tracking')));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ho_tracking';
        
        $placeholders = implode(',', array_fill(0, count($record_ids), '%d'));
        $query = $wpdb->prepare("DELETE FROM $table_name WHERE id IN ($placeholders)", $record_ids);
        $deleted = $wpdb->query($query);
        
        if ($deleted) {
            wp_send_json_success(array('message' => sprintf(__('%d records deleted successfully', 'ho-tracking'), $deleted)));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete records', 'ho-tracking')));
        }
    }
    
    /**
     * AJAX handler for updating a record
     */
    public function ajax_update_record() {
        check_ajax_referer('ho_tracking_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ho-tracking')));
        }
        
        $record_id = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;
        
        if (!$record_id) {
            wp_send_json_error(array('message' => __('Invalid record ID', 'ho-tracking')));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ho_tracking';
        
        $updated = $wpdb->update(
            $table_name,
            array(
                'tracking_code' => sanitize_text_field($_POST['tracking_code']),
                'recipient_name' => sanitize_text_field($_POST['recipient_name']),
                'status' => sanitize_text_field($_POST['status']),
                'date_sent' => sanitize_text_field($_POST['date_sent']),
                'date_delivered' => sanitize_text_field($_POST['date_delivered']),
                'notes' => sanitize_textarea_field($_POST['notes'])
            ),
            array('id' => $record_id),
            array('%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($updated !== false) {
            wp_send_json_success(array('message' => __('Record updated successfully', 'ho-tracking')));
        } else {
            wp_send_json_error(array('message' => __('Failed to update record', 'ho-tracking')));
        }
    }
}

// Initialize plugin
new HO_Tracking();
