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
        }
        
        // Frontend hooks
        add_shortcode('ho_tracking_table', array($this, 'tracking_table_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        add_action('wp_ajax_ho_tracking_search', array($this, 'ajax_search'));
        add_action('wp_ajax_nopriv_ho_tracking_search', array($this, 'ajax_search'));
        
        // Elementor widget hooks
        add_action('elementor/widgets/register', array($this, 'register_elementor_widgets'));
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'elementor_enqueue_styles'));
        add_action('elementor/frontend/after_enqueue_scripts', array($this, 'elementor_enqueue_scripts'));
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
        add_menu_page(
            __('HO Tracking', 'ho-tracking'),
            __('HO Tracking', 'ho-tracking'),
            'manage_options',
            'ho-tracking',
            array($this, 'admin_page'),
            'dashicons-upload',
            30
        );
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        include HO_TRACKING_PLUGIN_DIR . 'admin/admin-page.php';
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_ho-tracking') {
            return;
        }
        
        wp_enqueue_style('ho-tracking-admin', HO_TRACKING_PLUGIN_URL . 'assets/css/admin.css', array(), HO_TRACKING_VERSION);
        wp_enqueue_script('ho-tracking-admin', HO_TRACKING_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), HO_TRACKING_VERSION, true);
        
        wp_localize_script('ho-tracking-admin', 'hoTracking', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ho_tracking_nonce')
        ));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_enqueue_scripts() {
        wp_enqueue_style('ho-tracking-frontend', HO_TRACKING_PLUGIN_URL . 'assets/css/frontend.css', array(), HO_TRACKING_VERSION);
        wp_enqueue_script('ho-tracking-frontend', HO_TRACKING_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), HO_TRACKING_VERSION, true);
        
        $this->localize_frontend_script();
    }
    
    /**
     * Localize frontend script with AJAX data
     */
    protected function localize_frontend_script() {
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
     * Register Elementor widgets
     */
    public function register_elementor_widgets($widgets_manager) {
        // Check if Elementor widget base class is available
        if (!class_exists('\Elementor\Widget_Base')) {
            return;
        }
        
        // Include widget file
        require_once HO_TRACKING_PLUGIN_DIR . 'includes/elementor-widget.php';
        
        // Register widget
        $widgets_manager->register(new \HO_Tracking_Elementor_Widget());
    }
    
    /**
     * Enqueue styles for Elementor frontend
     */
    public function elementor_enqueue_styles() {
        // Only enqueue if not already enqueued
        if (!wp_style_is('ho-tracking-frontend', 'enqueued')) {
            wp_enqueue_style('ho-tracking-frontend', HO_TRACKING_PLUGIN_URL . 'assets/css/frontend.css', array(), HO_TRACKING_VERSION);
        }
    }
    
    /**
     * Enqueue scripts for Elementor frontend
     */
    public function elementor_enqueue_scripts() {
        // Only enqueue if not already enqueued
        if (!wp_script_is('ho-tracking-frontend', 'enqueued')) {
            wp_enqueue_script('ho-tracking-frontend', HO_TRACKING_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), HO_TRACKING_VERSION, true);
            $this->localize_frontend_script();
        }
    }
}

// Initialize plugin
new HO_Tracking();
