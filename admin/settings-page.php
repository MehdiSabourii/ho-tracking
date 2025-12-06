<?php
/**
 * Settings page template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$ho_tracking = new HO_Tracking();
$visible_columns = $ho_tracking->get_visible_columns();

// Define available columns with Persian labels
$available_columns = array(
    'tracking_code' => __('Tracking Code', 'ho-tracking') . ' / ' . 'کد رهگیری',
    'recipient_name' => __('Recipient Name', 'ho-tracking') . ' / ' . 'نام گیرنده',
    'status' => __('Status', 'ho-tracking') . ' / ' . 'وضعیت',
    'date_sent' => __('Date Sent', 'ho-tracking') . ' / ' . 'تاریخ ارسال',
    'date_delivered' => __('Date Delivered', 'ho-tracking') . ' / ' . 'تاریخ تحویل',
    'notes' => __('Notes', 'ho-tracking') . ' / ' . 'توضیحات'
);
?>

<div class="wrap ho-tracking-settings">
    <h1><?php _e('Display Settings', 'ho-tracking'); ?> / تنظیمات نمایش</h1>
    
    <div class="ho-tracking-settings-section">
        <div class="card">
            <h2><?php _e('Visible Columns', 'ho-tracking'); ?> / ستون‌های قابل نمایش</h2>
            <p><?php _e('Select which columns should be visible in the tracking table on the frontend.', 'ho-tracking'); ?></p>
            <p>مشخص کنید کدام ستون‌ها در جدول ردیابی نمایش داده شوند.</p>
            
            <form id="ho-tracking-settings-form">
                <table class="form-table">
                    <tbody>
                        <?php foreach ($available_columns as $column_key => $column_label): ?>
                        <tr>
                            <th scope="row">
                                <label for="column_<?php echo esc_attr($column_key); ?>">
                                    <?php echo esc_html($column_label); ?>
                                </label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="visible_columns[]" 
                                           id="column_<?php echo esc_attr($column_key); ?>"
                                           value="<?php echo esc_attr($column_key); ?>"
                                           <?php checked(in_array($column_key, $visible_columns)); ?>>
                                    <?php _e('Show this column', 'ho-tracking'); ?> / نمایش این ستون
                                </label>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary" id="save-settings-button">
                        <?php _e('Save Settings', 'ho-tracking'); ?> / ذخیره تنظیمات
                    </button>
                    <span class="spinner"></span>
                </p>
            </form>
            
            <div id="settings-message" style="display: none;"></div>
        </div>
        
        <div class="card">
            <h2><?php _e('Right-to-Left (RTL) Support', 'ho-tracking'); ?> / پشتیبانی راست به چپ</h2>
            <p><?php _e('The table automatically supports Persian (Farsi) language with right-to-left text direction.', 'ho-tracking'); ?></p>
            <p>جدول به صورت خودکار از زبان فارسی با جهت راست به چپ پشتیبانی می‌کند.</p>
            <ul>
                <li><?php _e('Column headers are displayed in both English and Persian', 'ho-tracking'); ?> / عنوان ستون‌ها به دو زبان انگلیسی و فارسی نمایش داده می‌شوند</li>
                <li><?php _e('Text direction is set to RTL for Persian content', 'ho-tracking'); ?> / جهت متن برای محتوای فارسی راست به چپ تنظیم شده است</li>
                <li><?php _e('Table columns are right-aligned', 'ho-tracking'); ?> / ستون‌های جدول راست‌چین هستند</li>
            </ul>
        </div>
    </div>
</div>
