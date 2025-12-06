<?php
/**
 * Settings page template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['ho_tracking_settings_submit'])) {
    check_admin_referer('ho_tracking_settings_nonce');
    
    update_option('ho_tracking_records_per_page', intval($_POST['ho_tracking_records_per_page']));
    update_option('ho_tracking_date_format', sanitize_text_field($_POST['ho_tracking_date_format']));
    update_option('ho_tracking_enable_export', isset($_POST['ho_tracking_enable_export']) ? '1' : '0');
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully.', 'ho-tracking') . '</p></div>';
}

// Get current settings
$records_per_page = get_option('ho_tracking_records_per_page', 20);
$date_format = get_option('ho_tracking_date_format', 'Y-m-d');
$enable_export = get_option('ho_tracking_enable_export', '1');

// Get database statistics
global $wpdb;
$table_name = $wpdb->prefix . 'ho_tracking';
$total_records = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$table_size = $wpdb->get_var($wpdb->prepare(
    "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
     FROM information_schema.TABLES 
     WHERE table_schema = %s AND table_name = %s",
    DB_NAME,
    $table_name
));
?>

<div class="wrap ho-tracking-admin ho-tracking-settings">
    <h1><?php _e('HO Tracking Settings', 'ho-tracking'); ?></h1>

    <div class="ho-tracking-settings-content">
        <div class="settings-main">
            <div class="card">
                <h2><?php _e('General Settings', 'ho-tracking'); ?></h2>
                
                <form method="post" action="">
                    <?php wp_nonce_field('ho_tracking_settings_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ho_tracking_records_per_page">
                                    <?php _e('Records Per Page', 'ho-tracking'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="ho_tracking_records_per_page" 
                                       name="ho_tracking_records_per_page" 
                                       value="<?php echo esc_attr($records_per_page); ?>" 
                                       min="5" 
                                       max="100" 
                                       class="small-text">
                                <p class="description">
                                    <?php _e('Number of records to display per page in the management area.', 'ho-tracking'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ho_tracking_date_format">
                                    <?php _e('Date Format', 'ho-tracking'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="ho_tracking_date_format" name="ho_tracking_date_format">
                                    <option value="Y-m-d" <?php selected($date_format, 'Y-m-d'); ?>>
                                        <?php echo date('Y-m-d'); ?> (Y-m-d)
                                    </option>
                                    <option value="d/m/Y" <?php selected($date_format, 'd/m/Y'); ?>>
                                        <?php echo date('d/m/Y'); ?> (d/m/Y)
                                    </option>
                                    <option value="m/d/Y" <?php selected($date_format, 'm/d/Y'); ?>>
                                        <?php echo date('m/d/Y'); ?> (m/d/Y)
                                    </option>
                                    <option value="F j, Y" <?php selected($date_format, 'F j, Y'); ?>>
                                        <?php echo date('F j, Y'); ?> (F j, Y)
                                    </option>
                                </select>
                                <p class="description">
                                    <?php _e('Date format for displaying dates in the frontend.', 'ho-tracking'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <?php _e('Features', 'ho-tracking'); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" 
                                               name="ho_tracking_enable_export" 
                                               value="1" 
                                               <?php checked($enable_export, '1'); ?>>
                                        <?php _e('Enable data export functionality', 'ho-tracking'); ?>
                                    </label>
                                    <p class="description">
                                        <?php _e('Allow administrators to export tracking data to CSV.', 'ho-tracking'); ?>
                                    </p>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" name="ho_tracking_settings_submit" class="button button-primary">
                            <?php _e('Save Settings', 'ho-tracking'); ?>
                        </button>
                    </p>
                </form>
            </div>

            <div class="card">
                <h2><?php _e('Database Management', 'ho-tracking'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Total Records', 'ho-tracking'); ?></th>
                        <td>
                            <strong><?php echo number_format($total_records); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Database Size', 'ho-tracking'); ?></th>
                        <td>
                            <strong><?php echo $table_size ? $table_size . ' MB' : __('N/A', 'ho-tracking'); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Table Name', 'ho-tracking'); ?></th>
                        <td>
                            <code><?php echo esc_html($table_name); ?></code>
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="button" id="clear-all-data" class="button button-danger">
                        <?php _e('Clear All Data', 'ho-tracking'); ?>
                    </button>
                </p>
                <p class="description">
                    <?php _e('Warning: This will permanently delete all tracking records from the database.', 'ho-tracking'); ?>
                </p>
            </div>
        </div>

        <div class="settings-sidebar">
            <div class="card">
                <h3><?php _e('Plugin Information', 'ho-tracking'); ?></h3>
                <table class="info-table">
                    <tr>
                        <th><?php _e('Version', 'ho-tracking'); ?>:</th>
                        <td><?php echo HO_TRACKING_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Status', 'ho-tracking'); ?>:</th>
                        <td><span class="status-active"><?php _e('Active', 'ho-tracking'); ?></span></td>
                    </tr>
                </table>
            </div>

            <div class="card">
                <h3><?php _e('Quick Links', 'ho-tracking'); ?></h3>
                <ul class="quick-links">
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=ho-tracking'); ?>">
                            📤 <?php _e('Upload Data', 'ho-tracking'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=ho-tracking-manage'); ?>">
                            📋 <?php _e('Manage Records', 'ho-tracking'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="https://github.com/MehdiSabourii/ho-tracking" target="_blank">
                            📖 <?php _e('Documentation', 'ho-tracking'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="https://github.com/MehdiSabourii/ho-tracking/issues" target="_blank">
                            🐛 <?php _e('Report Issue', 'ho-tracking'); ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card">
                <h3><?php _e('Shortcode', 'ho-tracking'); ?></h3>
                <p><?php _e('Use this shortcode to display the tracking table:', 'ho-tracking'); ?></p>
                <div class="shortcode-box">
                    <code>[ho_tracking_table]</code>
                    <button type="button" class="button-copy" data-clipboard-text="[ho_tracking_table]">
                        <?php _e('Copy', 'ho-tracking'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
