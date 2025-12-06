<?php
/**
 * Admin page template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap ho-tracking-admin">
    <h1 class="wp-heading-inline"><?php _e('Upload Tracking Data', 'ho-tracking'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=ho-tracking-manage'); ?>" class="page-title-action">
        <?php _e('Manage Records', 'ho-tracking'); ?>
    </a>
    <a href="<?php echo admin_url('admin.php?page=ho-tracking-settings'); ?>" class="page-title-action">
        <?php _e('Settings', 'ho-tracking'); ?>
    </a>
    <hr class="wp-header-end">
    
    <div class="ho-tracking-upload-section">
        <div class="card">
            <h2><?php _e('Upload Tracking File', 'ho-tracking'); ?></h2>
            <p><?php _e('Upload a CSV or Excel file containing tracking information. The file should have columns for tracking code, recipient name, status, dates, and notes.', 'ho-tracking'); ?></p>
            
            <form id="ho-tracking-upload-form" enctype="multipart/form-data">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="tracking_file"><?php _e('Select File', 'ho-tracking'); ?></label>
                        </th>
                        <td>
                            <input type="file" name="tracking_file" id="tracking_file" accept=".csv,.xls,.xlsx" required>
                            <p class="description">
                                <?php _e('Accepted formats: CSV, XLS, XLSX', 'ho-tracking'); ?><br>
                                <?php _e('Maximum file size: 10MB', 'ho-tracking'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="clear_existing"><?php _e('Clear Existing Data', 'ho-tracking'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="clear_existing" id="clear_existing" value="1">
                                <?php _e('Delete all existing tracking records before import', 'ho-tracking'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary" id="upload-button">
                        <?php _e('Upload and Import', 'ho-tracking'); ?>
                    </button>
                    <span class="spinner"></span>
                </p>
            </form>
            
            <div id="upload-message" style="display: none;"></div>
        </div>
        
        <div class="card">
            <h2><?php _e('Expected File Format', 'ho-tracking'); ?></h2>
            <p><?php _e('Your file should contain the following columns (case-insensitive):', 'ho-tracking'); ?></p>
            <ul>
                <li><strong>tracking_code</strong> <?php _e('or', 'ho-tracking'); ?> <strong>tracking</strong> <?php _e('or', 'ho-tracking'); ?> <strong>code</strong> <?php _e('or', 'ho-tracking'); ?> <strong>کد رهگیری</strong> - <?php _e('(Required) The tracking code', 'ho-tracking'); ?></li>
                <li><strong>recipient_name</strong> <?php _e('or', 'ho-tracking'); ?> <strong>recipient</strong> <?php _e('or', 'ho-tracking'); ?> <strong>name</strong> <?php _e('or', 'ho-tracking'); ?> <strong>نام</strong> - <?php _e('(Optional) Recipient name', 'ho-tracking'); ?></li>
                <li><strong>status</strong> <?php _e('or', 'ho-tracking'); ?> <strong>وضعیت</strong> - <?php _e('(Optional) Delivery status', 'ho-tracking'); ?></li>
                <li><strong>date_sent</strong> <?php _e('or', 'ho-tracking'); ?> <strong>تاریخ ارسال</strong> - <?php _e('(Optional) Date sent', 'ho-tracking'); ?></li>
                <li><strong>date_delivered</strong> <?php _e('or', 'ho-tracking'); ?> <strong>تاریخ تحویل</strong> - <?php _e('(Optional) Date delivered', 'ho-tracking'); ?></li>
                <li><strong>notes</strong> <?php _e('or', 'ho-tracking'); ?> <strong>توضیحات</strong> - <?php _e('(Optional) Additional notes', 'ho-tracking'); ?></li>
            </ul>
        </div>
        
        <div class="card">
            <h2><?php _e('Usage Instructions', 'ho-tracking'); ?></h2>
            <p><?php _e('To display the tracking table on your website, use the following shortcode:', 'ho-tracking'); ?></p>
            <code>[ho_tracking_table]</code>
            <p><?php _e('Add this shortcode to any page or post where you want the tracking search interface to appear.', 'ho-tracking'); ?></p>
        </div>
    </div>
</div>
