# HO Tracking - Developer Guide

This guide is for developers who want to understand, modify, or extend the HO Tracking plugin.

## Architecture Overview

The plugin follows WordPress best practices and uses an object-oriented approach with a single main class.

### File Structure

```
ho-tracking/
├── ho-tracking.php              # Main plugin file (entry point)
├── admin/
│   └── admin-page.php           # Admin interface template
├── templates/
│   └── tracking-table.php       # Frontend shortcode template
├── assets/
│   ├── css/
│   │   ├── admin.css           # Admin styles
│   │   └── frontend.css        # Frontend styles
│   └── js/
│       ├── admin.js            # Admin AJAX handlers
│       └── frontend.js         # Frontend search functionality
├── includes/
│   └── phpspreadsheet-loader.php # Excel library loader
├── composer.json                # PHP dependencies
├── sample-data.csv             # Example data
└── Documentation files (.md)
```

## Core Components

### 1. Main Plugin Class (`HO_Tracking`)

Located in `ho-tracking.php`, this class handles:

- **Activation/Deactivation**: Database table creation
- **Admin Interface**: Menu registration, file upload handling
- **Frontend**: Shortcode registration, AJAX search
- **File Parsing**: CSV and Excel file processing
- **Data Management**: Insert, search, and clear operations

### 2. Database Schema

Table: `{prefix}_ho_tracking`

```sql
CREATE TABLE {prefix}_ho_tracking (
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
);
```

### 3. AJAX Endpoints

**Upload Handler**: `ho_tracking_upload`
- Action: Admin file upload
- Nonce: `ho_tracking_nonce`
- Capability: `manage_options`
- Response: JSON with success/error and message

**Search Handler**: `ho_tracking_search`
- Action: Frontend search
- Nonce: `ho_tracking_search_nonce`
- Public: Yes (nopriv)
- Response: JSON with matching records

### 4. Shortcode

**`[ho_tracking_table]`**
- Template: `templates/tracking-table.php`
- Output: Search interface + results container
- AJAX: Real-time search functionality

## Key Functions

### File Parsing

```php
parse_file($file_path, $file_ext)
├── parse_csv($file_path)      # CSV parsing
└── parse_excel($file_path)    # Excel parsing
```

**Features:**
- Column name normalization (lowercase, trim)
- Multi-language column detection
- Row validation (count matching)
- Error logging for data quality issues

### Data Operations

```php
insert_tracking_data($data)
├── Column mapping (flexible names)
├── Data sanitization
└── Batch insert with count

clear_tracking_data()
├── Table name sanitization
└── DELETE operation (not TRUNCATE)

ajax_search()
├── Nonce verification
├── Search term sanitization
├── LIKE query on tracking_code and recipient_name
└── JSON response with results
```

## Customization Points

### 1. Add Custom Columns

Edit `ho-tracking.php`:

```php
// In create_table():
$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    ...
    custom_field varchar(255) DEFAULT NULL,
    ...
) $charset_collate;";

// In insert_tracking_data():
$wpdb->insert(
    $table_name,
    array(
        ...
        'custom_field' => $custom_value,
    ),
    array('%s', ..., '%s')
);
```

### 2. Modify Search Behavior

Edit `ho-tracking.php` in `ajax_search()`:

```php
// Add more search fields
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name 
     WHERE tracking_code LIKE %s 
     OR recipient_name LIKE %s 
     OR custom_field LIKE %s 
     ORDER BY created_at DESC",
    '%' . $wpdb->esc_like($search) . '%',
    '%' . $wpdb->esc_like($search) . '%',
    '%' . $wpdb->esc_like($search) . '%'
));
```

### 3. Custom Styling

Add custom CSS in your theme:

```css
/* Override default styles */
.ho-tracking-wrapper {
    max-width: 100%;
    padding: 0;
}

.tracking-table thead {
    background: #your-color;
}
```

### 4. Add Column Name Variations

Edit `ho-tracking.php` in `insert_tracking_data()`:

```php
// Add to tracking code detection
foreach (array('tracking_code', 'tracking', 'code', 'کد رهگیری', 'your_custom_name') as $key) {
    if (isset($row[$key]) && !empty($row[$key])) {
        $tracking_code = sanitize_text_field($row[$key]);
        break;
    }
}
```

## Extending Functionality

### Add Export Feature

```php
// In HO_Tracking class
public function export_csv() {
    check_ajax_referer('ho_tracking_export_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Permission denied', 'ho-tracking'));
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'ho_tracking';
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="tracking-export.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array_keys($results[0]));
    
    foreach ($results as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}
```

### Add Pagination

```php
// In ajax_search()
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name 
     WHERE tracking_code LIKE %s 
     OR recipient_name LIKE %s 
     ORDER BY created_at DESC 
     LIMIT %d OFFSET %d",
    '%' . $wpdb->esc_like($search) . '%',
    '%' . $wpdb->esc_like($search) . '%',
    $per_page,
    $offset
));

$total = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table_name 
     WHERE tracking_code LIKE %s 
     OR recipient_name LIKE %s",
    '%' . $wpdb->esc_like($search) . '%',
    '%' . $wpdb->esc_like($search) . '%'
));

wp_send_json_success(array(
    'data' => $results,
    'total' => $total,
    'pages' => ceil($total / $per_page),
    'current_page' => $page
));
```

## Hooks and Filters

### Custom Hooks You Can Add

```php
// After successful upload
do_action('ho_tracking_after_upload', $inserted, $data);

// Before insert
apply_filters('ho_tracking_before_insert', $row_data);

// Modify search results
apply_filters('ho_tracking_search_results', $results, $search_term);
```

### Example Usage in Theme

```php
// functions.php
add_action('ho_tracking_after_upload', function($count, $data) {
    // Send notification email
    wp_mail(
        get_option('admin_email'),
        'Tracking Data Uploaded',
        "$count records imported"
    );
}, 10, 2);
```

## JavaScript API

### Admin AJAX

```javascript
jQuery.ajax({
    url: hoTracking.ajaxurl,
    type: 'POST',
    data: {
        action: 'ho_tracking_upload',
        nonce: hoTracking.nonce,
        // other data
    },
    success: function(response) {
        if (response.success) {
            console.log(response.data.message);
        }
    }
});
```

### Frontend Search

```javascript
jQuery.ajax({
    url: hoTracking.ajaxurl,
    type: 'POST',
    data: {
        action: 'ho_tracking_search',
        nonce: hoTracking.nonce,
        search: 'tracking_code'
    },
    success: function(response) {
        if (response.success) {
            // response.data.data contains array of results
        }
    }
});
```

## Testing

### Manual Testing Checklist

- [ ] Upload CSV file with valid data
- [ ] Upload Excel file (.xlsx)
- [ ] Upload Excel file (.xls)
- [ ] Upload file with mismatched columns
- [ ] Clear existing data before import
- [ ] Search with valid tracking code
- [ ] Search with recipient name
- [ ] Search with partial match
- [ ] Search with no results
- [ ] Test mobile responsive design
- [ ] Test with Persian column headers
- [ ] Verify security (try without nonce)
- [ ] Check error logging

### Unit Test Example

```php
class Test_HO_Tracking extends WP_UnitTestCase {
    
    public function test_csv_parsing() {
        $plugin = new HO_Tracking();
        $file_path = __DIR__ . '/fixtures/sample.csv';
        
        $reflection = new ReflectionClass($plugin);
        $method = $reflection->getMethod('parse_csv');
        $method->setAccessible(true);
        
        $result = $method->invokeArgs($plugin, array($file_path));
        
        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
    }
}
```

## Performance Optimization

### Database Indexing

The plugin creates an index on `tracking_code` for fast lookups:

```sql
KEY tracking_code (tracking_code)
```

### AJAX Debouncing

Frontend search uses 500ms debounce to reduce server requests:

```javascript
var searchTimeout;
searchInput.on('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        performSearch(searchInput.val().trim());
    }, 500);
});
```

### Limit Results

Search limits results to 100 records when no filter:

```php
$results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 100");
```

## Security Best Practices

1. **Always verify nonces** for AJAX requests
2. **Check user capabilities** before admin operations
3. **Use prepared statements** for database queries
4. **Sanitize input** with WordPress functions
5. **Escape output** to prevent XSS
6. **Validate file types** before processing
7. **Log errors** for debugging (not user input)

## Common Issues and Solutions

### Issue: Excel files not working
**Solution**: Run `composer install` to install PhpSpreadsheet

### Issue: Search not working
**Solution**: Check nonce configuration and JavaScript console

### Issue: Styles not loading
**Solution**: Clear WordPress cache and check file permissions

### Issue: Large file upload fails
**Solution**: Increase PHP `upload_max_filesize` and `post_max_size`

## Contributing

When contributing:

1. Follow WordPress coding standards
2. Test with PHP 7.2+ and latest WordPress
3. Ensure backward compatibility
4. Add error handling and logging
5. Update documentation
6. Write meaningful commit messages

## Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [PhpSpreadsheet Documentation](https://phpspreadsheet.readthedocs.io/)
- [AJAX in WordPress](https://codex.wordpress.org/AJAX_in_Plugins)

## Support

For development questions:
- GitHub Issues: https://github.com/MehdiSabourii/ho-tracking/issues
- WordPress Support: WordPress.org plugin support forum
