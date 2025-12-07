<?php
/**
 * Manage Records page template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get pagination parameters
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$per_page = get_option('ho_tracking_records_per_page', 20);

// Get records from database
global $wpdb;
$table_name = $wpdb->prefix . 'ho_tracking';

// Build queries based on search parameter
if (!empty($search)) {
    $search_term = '%' . $wpdb->esc_like($search) . '%';
    $total_records = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE tracking_code LIKE %s OR recipient_name LIKE %s OR status LIKE %s",
        $search_term,
        $search_term,
        $search_term
    ));
    $total_pages = ceil($total_records / $per_page);
    $offset = ($paged - 1) * $per_page;
    
    $records = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE tracking_code LIKE %s OR recipient_name LIKE %s OR status LIKE %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $search_term,
        $search_term,
        $search_term,
        $per_page,
        $offset
    ));
} else {
    $total_records = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_records / $per_page);
    $offset = ($paged - 1) * $per_page;
    
    $records = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));
}
?>

<div class="wrap ho-tracking-admin ho-tracking-manage">
    <h1 class="wp-heading-inline"><?php _e('Manage Tracking Records', 'ho-tracking'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=ho-tracking'); ?>" class="page-title-action">
        <?php _e('Upload New Data', 'ho-tracking'); ?>
    </a>
    <hr class="wp-header-end">

    <div class="ho-tracking-manage-header">
        <form method="get" class="search-box-form">
            <input type="hidden" name="page" value="ho-tracking-manage">
            <p class="search-box">
                <input type="search" 
                       id="record-search-input" 
                       name="s" 
                       value="<?php echo esc_attr($search); ?>" 
                       placeholder="<?php _e('Search records...', 'ho-tracking'); ?>">
                <button type="submit" class="button"><?php _e('Search', 'ho-tracking'); ?></button>
            </p>
        </form>

        <div class="bulk-actions-box">
            <select id="bulk-action-selector">
                <option value=""><?php _e('Bulk Actions', 'ho-tracking'); ?></option>
                <option value="delete"><?php _e('Delete', 'ho-tracking'); ?></option>
            </select>
            <button type="button" id="bulk-action-apply" class="button"><?php _e('Apply', 'ho-tracking'); ?></button>
        </div>
    </div>

    <div class="ho-tracking-stats">
        <div class="stat-box">
            <span class="stat-number"><?php echo number_format($total_records); ?></span>
            <span class="stat-label"><?php _e('Total Records', 'ho-tracking'); ?></span>
        </div>
        <?php if (!empty($search)): ?>
        <div class="stat-box">
            <span class="stat-number"><?php echo count($records); ?></span>
            <span class="stat-label"><?php _e('Search Results', 'ho-tracking'); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <?php if (empty($records)): ?>
        <div class="no-records-message">
            <p><?php _e('No tracking records found.', 'ho-tracking'); ?></p>
            <?php if (!empty($search)): ?>
                <a href="<?php echo admin_url('admin.php?page=ho-tracking-manage'); ?>" class="button">
                    <?php _e('Clear Search', 'ho-tracking'); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <form id="records-form">
            <table class="wp-list-table widefat fixed striped ho-tracking-table">
                <thead>
                    <tr>
                        <td class="check-column">
                            <input type="checkbox" id="select-all-records">
                        </td>
                        <th><?php _e('Tracking Code', 'ho-tracking'); ?></th>
                        <th><?php _e('Recipient Name', 'ho-tracking'); ?></th>
                        <th><?php _e('Status', 'ho-tracking'); ?></th>
                        <th><?php _e('Date Sent', 'ho-tracking'); ?></th>
                        <th><?php _e('Date Delivered', 'ho-tracking'); ?></th>
                        <th><?php _e('Created', 'ho-tracking'); ?></th>
                        <th><?php _e('Actions', 'ho-tracking'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                    <tr data-record-id="<?php echo esc_attr($record->id); ?>">
                        <th class="check-column">
                            <input type="checkbox" class="record-checkbox" value="<?php echo esc_attr($record->id); ?>">
                        </th>
                        <td class="tracking-code">
                            <strong><?php echo esc_html($record->tracking_code); ?></strong>
                        </td>
                        <td><?php echo esc_html($record->recipient_name); ?></td>
                        <td>
                            <?php if ($record->status): ?>
                                <span class="status-badge status-<?php echo esc_attr(strtolower(str_replace(' ', '-', $record->status))); ?>">
                                    <?php echo esc_html($record->status); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $record->date_sent ? esc_html($record->date_sent) : '—'; ?></td>
                        <td><?php echo $record->date_delivered ? esc_html($record->date_delivered) : '—'; ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($record->created_at))); ?></td>
                        <td class="actions">
                            <button type="button" class="button button-small edit-record" data-record-id="<?php echo esc_attr($record->id); ?>">
                                <?php _e('Edit', 'ho-tracking'); ?>
                            </button>
                            <button type="button" class="button button-small delete-record" data-record-id="<?php echo esc_attr($record->id); ?>">
                                <?php _e('Delete', 'ho-tracking'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>

        <?php if ($total_pages > 1): ?>
        <div class="tablenav">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php printf(__('%s items', 'ho-tracking'), number_format($total_records)); ?>
                </span>
                <?php
                $page_links = paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $paged,
                    'add_args' => !empty($search) ? array('s' => urlencode($search)) : array()
                ));
                echo $page_links;
                ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Edit Record Modal -->
<div id="edit-record-modal" class="ho-tracking-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><?php _e('Edit Tracking Record', 'ho-tracking'); ?></h2>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <form id="edit-record-form">
            <input type="hidden" id="edit-record-id" name="record_id">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="edit-tracking-code"><?php _e('Tracking Code', 'ho-tracking'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="edit-tracking-code" name="tracking_code" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="edit-recipient-name"><?php _e('Recipient Name', 'ho-tracking'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="edit-recipient-name" name="recipient_name" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="edit-status"><?php _e('Status', 'ho-tracking'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="edit-status" name="status" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="edit-date-sent"><?php _e('Date Sent', 'ho-tracking'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="edit-date-sent" name="date_sent" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="edit-date-delivered"><?php _e('Date Delivered', 'ho-tracking'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="edit-date-delivered" name="date_delivered" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="edit-notes"><?php _e('Notes', 'ho-tracking'); ?></label>
                    </th>
                    <td>
                        <textarea id="edit-notes" name="notes" class="large-text" rows="4"></textarea>
                    </td>
                </tr>
            </table>
            <div class="modal-footer">
                <button type="submit" class="button button-primary"><?php _e('Update Record', 'ho-tracking'); ?></button>
                <button type="button" class="button modal-close"><?php _e('Cancel', 'ho-tracking'); ?></button>
                <span class="spinner"></span>
            </div>
        </form>
    </div>
</div>

<div id="message-container"></div>
