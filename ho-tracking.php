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
            add_action('admin_notices', array($this, 'admin_notices'));
            add_action('wp_ajax_ho_tracking_upload', array($this, 'handle_upload'));
        }
        
        // Frontend hooks
        add_shortcode('ho_tracking_table', array($this, 'tracking_table_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        add_action('wp_ajax_ho_tracking_search', array($this, 'ajax_search'));
        add_action('wp_ajax_nopriv_ho_tracking_search', array($this, 'ajax_search'));
    }
    
    /**
     * Display admin notices
     */
    public function admin_notices() {
        // Only show on plugin page
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'toplevel_page_ho-tracking') {
            return;
        }
        
        // Check if Excel support is available
        $excel_supported = class_exists('PhpOffice\PhpSpreadsheet\IOFactory') || 
                          file_exists(HO_TRACKING_PLUGIN_DIR . 'vendor/autoload.php');
        
        if (!$excel_supported) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php _e('HO Tracking:', 'ho-tracking'); ?></strong>
                    <?php _e('Excel file support is not available. To enable Excel (.xls, .xlsx) file uploads, please install Composer dependencies by running:', 'ho-tracking'); ?>
                    <code>composer install</code>
                    <?php _e('in the plugin directory. CSV files will work without additional dependencies.', 'ho-tracking'); ?>
                </p>
            </div>
            <?php
        }
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
        
        // Check if table already exists
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
        
        if ($table_exists) {
            error_log('HO Tracking: Database table already exists');
            return;
        }
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            tracking_code varchar(255) NOT NULL,
            recipient_name varchar(255) DEFAULT NULL,
            status varchar(100) DEFAULT NULL,
            date_sent datetime DEFAULT NULL,
            date_delivered datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY tracking_code (tracking_code),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Verify table was created
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
        
        if ($table_exists) {
            error_log('HO Tracking: Database table created successfully');
        } else {
            error_log('HO Tracking: Failed to create database table');
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load plugin text domain
        load_plugin_textdomain('ho-tracking', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Verify database table exists, create if missing
        $this->verify_database();
    }
    
    /**
     * Verify database table exists
     */
    private function verify_database() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ho_tracking';
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
        
        if (!$table_exists) {
            error_log('HO Tracking: Table not found, creating...');
            $this->create_table();
        }
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
            return;
        }
        
        if (!isset($_FILES['tracking_file']) || empty($_FILES['tracking_file']['name'])) {
            wp_send_json_error(array('message' => __('No file uploaded. Please select a file.', 'ho-tracking')));
            return;
        }
        
        $file = $_FILES['tracking_file'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_message = $this->get_upload_error_message($file['error']);
            wp_send_json_error(array('message' => $error_message));
            return;
        }
        
        // Validate file size (max 10MB)
        $max_size = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $max_size) {
            wp_send_json_error(array('message' => __('File size exceeds maximum allowed size (10MB).', 'ho-tracking')));
            return;
        }
        
        if ($file['size'] === 0) {
            wp_send_json_error(array('message' => __('The uploaded file is empty.', 'ho-tracking')));
            return;
        }
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate file extension
        if (!in_array($file_ext, array('csv', 'xls', 'xlsx'))) {
            wp_send_json_error(array('message' => __('Invalid file format. Please upload CSV, XLS, or XLSX file.', 'ho-tracking')));
            return;
        }
        
        // Validate MIME type
        $allowed_mime_types = array(
            'text/csv',
            'text/plain',
            'application/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            error_log('HO Tracking: Failed to initialize finfo');
            // Continue without MIME type validation if finfo fails
        } else {
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if ($mime_type && !in_array($mime_type, $allowed_mime_types) && !in_array($file_ext, array('csv', 'xls', 'xlsx'))) {
                wp_send_json_error(array('message' => __('Invalid file type detected. Please upload a valid CSV or Excel file.', 'ho-tracking')));
                return;
            }
        }
        
        // Check if Excel support is available for xls/xlsx files
        $excel_supported = class_exists('PhpOffice\PhpSpreadsheet\IOFactory') || 
                          file_exists(HO_TRACKING_PLUGIN_DIR . 'vendor/autoload.php');
        
        if (!$excel_supported && in_array($file_ext, array('xls', 'xlsx'))) {
            wp_send_json_error(array('message' => __('Excel support is not available. Please install Composer dependencies or use CSV format.', 'ho-tracking')));
            return;
        }
        
        // Use WordPress file handling
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        // Override upload mimes filter temporarily
        add_filter('upload_mimes', array($this, 'allow_tracking_file_mimes'));
        
        $upload_overrides = array(
            'test_form' => false,
            'mimes' => array(
                'csv' => 'text/csv',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            )
        );
        
        $uploaded_file = wp_handle_upload($file, $upload_overrides);
        
        remove_filter('upload_mimes', array($this, 'allow_tracking_file_mimes'));
        
        if (isset($uploaded_file['error'])) {
            wp_send_json_error(array('message' => $uploaded_file['error']));
            return;
        }
        
        $file_path = $uploaded_file['file'];
        
        // Parse the file
        $data = $this->parse_file($file_path, $file_ext);
        
        // Clean up uploaded file after parsing
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        
        if (is_wp_error($data)) {
            wp_send_json_error(array('message' => $data->get_error_message()));
            return;
        }
        
        if (empty($data)) {
            wp_send_json_error(array('message' => __('No valid data found in the uploaded file.', 'ho-tracking')));
            return;
        }
        
        // Clear existing data if requested
        if (isset($_POST['clear_existing']) && $_POST['clear_existing'] === 'true') {
            $this->clear_tracking_data();
        }
        
        // Insert data
        $inserted = $this->insert_tracking_data($data);
        
        if ($inserted === 0) {
            wp_send_json_error(array('message' => __('No records were imported. Please check your file format and data.', 'ho-tracking')));
            return;
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('%d records imported successfully', 'ho-tracking'), $inserted)
        ));
    }
    
    /**
     * Get user-friendly upload error message
     */
    private function get_upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return __('The uploaded file exceeds the maximum allowed size.', 'ho-tracking');
            case UPLOAD_ERR_PARTIAL:
                return __('The file was only partially uploaded. Please try again.', 'ho-tracking');
            case UPLOAD_ERR_NO_FILE:
                return __('No file was uploaded.', 'ho-tracking');
            case UPLOAD_ERR_NO_TMP_DIR:
                return __('Missing temporary folder. Please contact the administrator.', 'ho-tracking');
            case UPLOAD_ERR_CANT_WRITE:
                return __('Failed to write file to disk. Please contact the administrator.', 'ho-tracking');
            case UPLOAD_ERR_EXTENSION:
                return __('File upload stopped by extension. Please contact the administrator.', 'ho-tracking');
            default:
                return __('Unknown upload error occurred.', 'ho-tracking');
        }
    }
    
    /**
     * Allow tracking file MIME types
     */
    public function allow_tracking_file_mimes($mimes) {
        $mimes['csv'] = 'text/csv';
        $mimes['xls'] = 'application/vnd.ms-excel';
        $mimes['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        return $mimes;
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
        
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return new WP_Error('file_error', __('Cannot read the uploaded file.', 'ho-tracking'));
        }
        
        $handle = fopen($file_path, 'r');
        
        if ($handle === false) {
            return new WP_Error('file_error', __('Could not open file for reading.', 'ho-tracking'));
        }
        
        // Detect file encoding and handle BOM
        $bom = fread($handle, 3);
        if ($bom === "\xEF\xBB\xBF") {
            // UTF-8 BOM detected, continue from current position
        } else {
            // No BOM, rewind to start
            rewind($handle);
        }
        
        $headers = fgetcsv($handle, 0, ',');
        $delimiter = ',';
        
        // Try semicolon delimiter if comma fails
        if ($headers === false || count($headers) <= 1) {
            rewind($handle);
            // Skip BOM again if present
            if ($bom === "\xEF\xBB\xBF") {
                fread($handle, 3);
            }
            $headers = fgetcsv($handle, 0, ';');
            $delimiter = ';';
        }
        
        if (!$headers || count($headers) === 0) {
            fclose($handle);
            return new WP_Error('parse_error', __('Invalid CSV format. Could not read headers.', 'ho-tracking'));
        }
        
        // Normalize headers
        $headers = array_map('trim', $headers);
        $headers = array_map('strtolower', $headers);
        
        // Remove empty headers
        $headers = array_filter($headers, function($h) { return !empty($h); });
        
        if (empty($headers)) {
            fclose($handle);
            return new WP_Error('parse_error', __('CSV file has no valid headers.', 'ho-tracking'));
        }
        
        // Re-index headers array
        $headers = array_values($headers);
        
        $row_number = 1; // Start from 1 after header
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row_number++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Trim all values
            $row = array_map('trim', $row);
            
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
        
        return $data;
    }
    
    /**
     * Parse Excel file
     */
    private function parse_excel($file_path) {
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return new WP_Error('file_error', __('Cannot read the uploaded file.', 'ho-tracking'));
        }
        
        // Check if PhpSpreadsheet is available
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            require_once HO_TRACKING_PLUGIN_DIR . 'includes/phpspreadsheet-loader.php';
        }
        
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            return new WP_Error('library_error', __('PhpSpreadsheet library is not available. Please install Composer dependencies or use CSV format.', 'ho-tracking'));
        }
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, true);
            
            if (empty($rows)) {
                return new WP_Error('parse_error', __('Empty Excel file', 'ho-tracking'));
            }
            
            // Get the first row as headers
            $header_row = array_shift($rows);
            $headers = array();
            
            // Extract and normalize headers
            foreach ($header_row as $cell) {
                if ($cell !== null && trim($cell) !== '') {
                    $headers[] = strtolower(trim($cell));
                }
            }
            
            if (empty($headers)) {
                return new WP_Error('parse_error', __('Excel file has no valid headers.', 'ho-tracking'));
            }
            
            $data = array();
            $skipped_rows = 0;
            $row_number = 1; // Start from 1 after header
            
            foreach ($rows as $row) {
                $row_number++;
                
                // Convert row to array of values
                $row_values = array_values($row);
                
                // Skip empty rows
                if (empty(array_filter($row_values, function($v) { return $v !== null && trim($v) !== ''; }))) {
                    continue;
                }
                
                // Trim all values and take only the number of columns we have headers for
                $row_data = array();
                for ($i = 0; $i < count($headers); $i++) {
                    $value = isset($row_values[$i]) && $row_values[$i] !== null ? trim($row_values[$i]) : '';
                    $row_data[] = $value;
                }
                
                if (count($row_data) === count($headers)) {
                    $data[] = array_combine($headers, $row_data);
                } else {
                    $skipped_rows++;
                    error_log(sprintf('HO Tracking: Skipped row %d due to column count mismatch (expected %d, got %d)', 
                        $row_number, count($headers), count($row_data)));
                }
            }
            
            if ($skipped_rows > 0) {
                error_log(sprintf('HO Tracking: Skipped %d rows due to data quality issues', $skipped_rows));
            }
            
            return $data;
        } catch (Exception $e) {
            error_log('HO Tracking Excel parsing error: ' . $e->getMessage());
            return new WP_Error('parse_error', sprintf(__('Error parsing Excel file: %s', 'ho-tracking'), $e->getMessage()));
        }
    }
    
    /**
     * Clear tracking data
     */
    private function clear_tracking_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ho_tracking';
        
        // Use DELETE for better compatibility
        $wpdb->query("DELETE FROM `{$table_name}`");
        
        error_log('HO Tracking: Cleared all tracking data');
    }
    
    /**
     * Insert tracking data
     */
    private function insert_tracking_data($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ho_tracking';
        $inserted = 0;
        $skipped = 0;
        
        foreach ($data as $row_index => $row) {
            // Map common column names
            $tracking_code = '';
            $recipient_name = '';
            $status = '';
            $date_sent = null;
            $date_delivered = null;
            $notes = '';
            
            // Try to find tracking code
            foreach (array('tracking_code', 'tracking', 'code', 'کد رهگیری', 'کد') as $key) {
                if (isset($row[$key]) && !empty(trim($row[$key]))) {
                    $tracking_code = sanitize_text_field(trim($row[$key]));
                    break;
                }
            }
            
            // Skip rows without tracking code
            if (empty($tracking_code)) {
                $skipped++;
                error_log(sprintf('HO Tracking: Skipped data row %d - no tracking code found', $row_index + 2)); // +2 for header and 0-index
                continue;
            }
            
            // Try to find recipient name
            foreach (array('recipient_name', 'recipient', 'name', 'نام', 'گیرنده') as $key) {
                if (isset($row[$key]) && !empty(trim($row[$key]))) {
                    $recipient_name = sanitize_text_field(trim($row[$key]));
                    break;
                }
            }
            
            // Try to find status
            foreach (array('status', 'وضعیت') as $key) {
                if (isset($row[$key]) && !empty(trim($row[$key]))) {
                    $status = sanitize_text_field(trim($row[$key]));
                    break;
                }
            }
            
            // Try to find dates
            foreach (array('date_sent', 'sent_date', 'تاریخ ارسال') as $key) {
                if (isset($row[$key]) && !empty(trim($row[$key]))) {
                    $date_sent = sanitize_text_field(trim($row[$key]));
                    break;
                }
            }
            
            foreach (array('date_delivered', 'delivered_date', 'تاریخ تحویل') as $key) {
                if (isset($row[$key]) && !empty(trim($row[$key]))) {
                    $date_delivered = sanitize_text_field(trim($row[$key]));
                    break;
                }
            }
            
            // Try to find notes
            foreach (array('notes', 'note', 'توضیحات') as $key) {
                if (isset($row[$key]) && !empty(trim($row[$key]))) {
                    $notes = sanitize_textarea_field(trim($row[$key]));
                    break;
                }
            }
            
            // Insert into database
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
            } else {
                error_log(sprintf('HO Tracking: Failed to insert tracking code %s - Error: %s', $tracking_code, $wpdb->last_error));
            }
        }
        
        if ($skipped > 0) {
            error_log(sprintf('HO Tracking: Skipped %d rows due to missing tracking codes', $skipped));
        }
        
        return $inserted;
    }
    
    /**
     * AJAX search handler
     */
    public function ajax_search() {
        check_ajax_referer('ho_tracking_search_nonce', 'nonce');
        
        $search = isset($_POST['search']) ? sanitize_text_field(trim($_POST['search'])) : '';
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ho_tracking';
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
        
        if (!$table_exists) {
            wp_send_json_error(array('message' => __('Database table not found. Please reactivate the plugin.', 'ho-tracking')));
            return;
        }
        
        if (!empty($search)) {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM `{$table_name}` WHERE tracking_code LIKE %s OR recipient_name LIKE %s ORDER BY created_at DESC LIMIT 200",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            ));
        } else {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM `{$table_name}` ORDER BY created_at DESC LIMIT %d",
                100
            ));
        }
        
        if ($wpdb->last_error) {
            error_log('HO Tracking search error: ' . $wpdb->last_error);
            wp_send_json_error(array('message' => __('Database error occurred. Please try again.', 'ho-tracking')));
            return;
        }
        
        // Ensure results is an array
        if (!is_array($results)) {
            $results = array();
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
}

// Initialize plugin
new HO_Tracking();
