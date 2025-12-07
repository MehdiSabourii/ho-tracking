# HO Tracking - WordPress Postal Tracking Plugin

A WordPress plugin that allows you to upload CSV or Excel files containing postal tracking information and display it to users in a searchable table with AJAX functionality.

## Features

### Frontend Features
- 📁 Upload CSV, XLS, or XLSX files with tracking information
- 🔍 Real-time AJAX search functionality
- 📊 Beautiful, responsive tracking table
- 🌐 Supports both English and Persian (Farsi) column headers
- 🎨 Mobile-friendly design
- ⚡ Fast and efficient search
- 🔄 Option to clear existing data before importing
- 🧩 Elementor widget support for easy page building

### Admin Dashboard Features (NEW!)
- 🎛️ **Organized Menu Structure**: Separate pages for Upload, Manage Records, and Settings
- 📋 **Record Management**: View, search, edit, and delete tracking records
- 🔍 **Advanced Search**: Filter records by tracking code, recipient name, or status
- 📄 **Pagination**: Browse through records with configurable items per page
- ✏️ **Inline Editing**: Edit records in a beautiful modal dialog
- 🗑️ **Bulk Actions**: Delete multiple records at once
- 📊 **Statistics Dashboard**: Visual display of total records
- ⚙️ **Settings Page**: Configure plugin options and preferences
- 🎨 **Modern UI/UX**: Professional design with smooth animations
- 🌐 **RTL Support**: Full support for Persian/Farsi right-to-left layout
- 🔒 **Enhanced Security**: SQL injection prevention, nonce verification, and input validation

## Installation

### Method 1: Manual Installation

1. Download or clone this repository
2. Upload the `ho-tracking` folder to the `/wp-content/plugins/` directory
3. Install dependencies (optional, for Excel support):
   ```bash
   cd wp-content/plugins/ho-tracking
   composer install
   ```
4. Activate the plugin through the 'Plugins' menu in WordPress

### Method 2: Upload ZIP

1. Create a ZIP file of the `ho-tracking` folder
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Choose the ZIP file and click 'Install Now'
4. Activate the plugin

## Excel Support

For full Excel (.xls, .xlsx) file support, you need to install PhpSpreadsheet via Composer:

```bash
cd wp-content/plugins/ho-tracking
composer install
```

Without Composer, the plugin will work with CSV files only.

## Usage

### 1. Upload Tracking Data

1. Go to WordPress Admin → HO Tracking → Upload Data
2. Click "Select File" and choose your CSV or Excel file
3. Optionally check "Clear Existing Data" to remove old records
4. Click "Upload and Import"

### 2. Manage Records

1. Go to WordPress Admin → HO Tracking → Manage Records
2. Browse, search, edit, or delete existing tracking records
3. Use the search box to find specific records
4. Click "Edit" to modify a record in a modal dialog
5. Use bulk actions to delete multiple records at once

### 3. Configure Settings

1. Go to WordPress Admin → HO Tracking → Settings
2. Configure records per page (5-100)
3. Choose your preferred date format
4. Enable/disable export functionality
5. View database statistics
6. Access quick links to documentation

### 4. Display Tracking Table

#### Option A: Using Shortcode

Add the following shortcode to any page or post:

```
[ho_tracking_table]
```

#### Option B: Using Elementor Widget (if Elementor is installed)

1. Edit a page with Elementor
2. Search for "HO Tracking Table" in the widgets panel
3. Drag and drop the widget to your desired location
4. Customize the widget style if needed
5. Publish the page

Users can then search for their tracking code or recipient name using the search box.

## File Format

Your CSV or Excel file should contain the following columns (case-insensitive):

| Column Name | Alternative Names | Required | Description |
|-------------|------------------|----------|-------------|
| tracking_code | tracking, code, کد رهگیری | Yes | The tracking code |
| recipient_name | recipient, name, نام, گیرنده | No | Recipient name |
| status | وضعیت | No | Delivery status |
| date_sent | sent_date, تاریخ ارسال | No | Date sent |
| date_delivered | delivered_date, تاریخ تحویل | No | Date delivered |
| notes | note, توضیحات | No | Additional notes |

### Sample CSV Format

```csv
tracking_code,recipient_name,status,date_sent,date_delivered,notes
TR123456789,John Doe,Delivered,2024-01-15,2024-01-18,Package delivered successfully
TR987654321,Jane Smith,In Transit,2024-01-20,,On the way to destination
TR456789123,Ahmad Rezaei,Pending,2024-01-22,,Awaiting shipment
```

A sample CSV file (`sample-data.csv`) is included in the plugin directory for reference.

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## Database

The plugin creates a table `wp_ho_tracking` (prefix may vary) to store tracking information. The table is automatically created upon plugin activation.

## Shortcode & Widget

### Shortcode

**[ho_tracking_table]**

Displays a search interface and tracking results table. Can be used in any post, page, or text widget.

### Elementor Widget

**HO Tracking Table**

Available in the Elementor widgets panel under the "General" category. Provides the same functionality as the shortcode with additional styling options:
- Search title color customization
- Button color customization
- Button hover color customization

## Features in Detail

### AJAX Search

- Real-time search as you type (500ms debounce)
- Searches both tracking code and recipient name
- No page refresh required

### Responsive Design

- Mobile-friendly table layout
- Stacks columns on small screens
- Touch-friendly search interface

### Multi-language Support

- Supports English and Persian column headers
- Automatic detection of column names
- Easy to extend for other languages

## Security

- Nonce verification for all AJAX requests
- Capability checks for admin functions
- SQL injection prevention with prepared statements
- File type validation
- Sanitized input and escaped output

## Support

For issues, questions, or contributions, please visit:
https://github.com/MehdiSabourii/ho-tracking

## License

This plugin is open source and available under the GPL v2 or later license.

## Credits

Developed by Mehdi Sabouri
