# HO Tracking - Installation Guide

Quick installation guide for the HO Tracking WordPress plugin.

## Prerequisites

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher
- WordPress admin access

## Installation Methods

### Method 1: Direct Upload (Recommended)

1. **Download the Plugin**
   ```bash
   git clone https://github.com/MehdiSabourii/ho-tracking.git
   ```

2. **Upload to WordPress**
   - Copy the `ho-tracking` folder to `/wp-content/plugins/`
   - Or use SFTP/FTP to upload the folder

3. **Install Dependencies (Optional - for Excel support)**
   ```bash
   cd /wp-content/plugins/ho-tracking
   composer install --no-dev
   ```
   
   **Note:** Without Composer, the plugin works perfectly with CSV files.

4. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "HO Tracking"
   - Click "Activate"

### Method 2: ZIP Upload

1. **Create ZIP File**
   ```bash
   zip -r ho-tracking.zip ho-tracking/
   ```

2. **Upload via WordPress**
   - Go to WordPress Admin → Plugins → Add New
   - Click "Upload Plugin"
   - Choose `ho-tracking.zip`
   - Click "Install Now"
   - Click "Activate Plugin"

## Post-Installation Setup

### Step 1: Verify Installation

1. Check that "HO Tracking" appears in the left admin menu
2. Click on it to access the upload interface
3. Verify that the database table was created successfully

### Step 2: Prepare Your Data

Create a CSV file with tracking information:

```csv
tracking_code,recipient_name,status,date_sent,date_delivered,notes
TR123456789,John Doe,Delivered,2024-01-15,2024-01-18,Package delivered successfully
TR987654321,Jane Smith,In Transit,2024-01-20,,On the way to destination
```

Or use the included `sample-data.csv` file for testing.

### Step 3: Upload Data

1. Go to WordPress Admin → HO Tracking
2. Click "Choose File"
3. Select your CSV or Excel file
4. Check "Clear Existing Data" if replacing all records
5. Click "Upload and Import"
6. Wait for success message

### Step 4: Add Tracking Search to a Page

1. Go to WordPress Admin → Pages → Add New
2. Give your page a title (e.g., "Track Your Package")
3. In the page editor, add this shortcode:
   ```
   [ho_tracking_table]
   ```
4. Publish the page

### Step 5: Test

1. Visit the page you just created
2. Try searching for a tracking code
3. Verify results appear correctly

## Verifying Installation

### Check Database Table

Run this SQL query in phpMyAdmin or similar:

```sql
SHOW TABLES LIKE '%_ho_tracking';
```

You should see a table like `wp_ho_tracking` (prefix may vary).

### Check Files

Verify these files exist:

```
wp-content/plugins/ho-tracking/
├── ho-tracking.php
├── admin/
│   └── admin-page.php
├── templates/
│   └── tracking-table.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
└── includes/
    └── phpspreadsheet-loader.php
```

## Excel Support (Optional)

To enable Excel (.xls, .xlsx) file upload:

1. **Install Composer** (if not already installed)
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

2. **Install Dependencies**
   ```bash
   cd /wp-content/plugins/ho-tracking
   composer install --no-dev
   ```

3. **Verify Installation**
   - Upload an Excel file in the admin interface
   - If it works, Excel support is enabled

**Note:** CSV support works without any additional dependencies.

## Troubleshooting

### Plugin doesn't appear in menu

- Check that the plugin is activated
- Clear WordPress cache
- Check for PHP errors in WordPress debug log

### File upload fails

- Check PHP upload limits:
  ```ini
  upload_max_filesize = 10M
  post_max_size = 10M
  ```
- Verify file permissions on plugin directory
- Check WordPress debug log for errors

### Styles not loading

- Clear browser cache
- Clear WordPress cache
- Verify assets/css files exist and are readable

### Search not working

- Check browser console for JavaScript errors
- Verify jQuery is loaded
- Check that AJAX URL is correct

### Excel files not working

- Install Composer dependencies (see Excel Support section)
- Or use CSV files instead
- Check PHP version (7.2+ required)

## Updating

To update the plugin:

1. **Backup Your Data**
   - Export database or backup tracking data
   - Or use WordPress backup plugin

2. **Deactivate Plugin**
   - WordPress Admin → Plugins
   - Deactivate "HO Tracking"

3. **Replace Files**
   - Delete old plugin files
   - Upload new plugin files

4. **Reactivate Plugin**
   - WordPress Admin → Plugins
   - Activate "HO Tracking"

**Note:** Your tracking data in the database will be preserved.

## Uninstallation

To completely remove the plugin:

1. **Deactivate Plugin**
   - WordPress Admin → Plugins
   - Deactivate "HO Tracking"

2. **Delete Plugin**
   - Click "Delete" under the plugin name
   - Confirm deletion

3. **Remove Database Table (Optional)**
   ```sql
   DROP TABLE IF EXISTS wp_ho_tracking;
   ```

**Warning:** This will permanently delete all tracking data.

## Security Recommendations

1. **Keep WordPress Updated**
   - Always use the latest WordPress version
   - Update plugins regularly

2. **Use Strong Passwords**
   - Use complex admin passwords
   - Enable two-factor authentication

3. **Limit Admin Access**
   - Only give admin access to trusted users
   - Use role-based access control

4. **Regular Backups**
   - Backup database regularly
   - Backup WordPress files

5. **SSL Certificate**
   - Use HTTPS for your website
   - Especially important for admin area

## Performance Tips

1. **Database Optimization**
   - Keep table size reasonable (< 100,000 records)
   - Run database optimization periodically

2. **Caching**
   - Use WordPress caching plugin
   - Enable browser caching

3. **File Size**
   - Keep upload files under 5MB
   - Split large datasets into multiple files

## Support

If you encounter issues:

1. **Check Documentation**
   - README.md - Overview and features
   - USAGE.md - User guide
   - DEVELOPER.md - Technical details
   - This file - Installation help

2. **Enable Debug Mode**
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
   Check `wp-content/debug.log` for errors

3. **GitHub Issues**
   - https://github.com/MehdiSabourii/ho-tracking/issues
   - Provide error messages and WordPress version

4. **WordPress Support**
   - WordPress.org support forums
   - Stack Overflow with tag [wordpress]

## Next Steps

After installation:

1. Read USAGE.md for detailed usage instructions
2. Review SCREENSHOTS.md for visual guide
3. Check DEVELOPER.md if you want to customize
4. Import your tracking data
5. Share the tracking page with your users

## Quick Reference

- **Admin Page**: WordPress Admin → HO Tracking
- **Shortcode**: `[ho_tracking_table]`
- **Table Name**: `{prefix}_ho_tracking`
- **Required Capability**: `manage_options`
- **Min PHP**: 7.2
- **Min WordPress**: 5.0

---

For more information, visit:
- GitHub: https://github.com/MehdiSabourii/ho-tracking
- Documentation: See README.md, USAGE.md, DEVELOPER.md
