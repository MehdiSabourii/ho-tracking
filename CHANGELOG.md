# Changelog

All notable changes to the HO Tracking plugin will be documented in this file.

## [1.0.0] - 2024-12-03

### Added
- Initial release of HO Tracking WordPress plugin
- CSV file upload and parsing functionality
- Excel (XLS/XLSX) file support via PhpSpreadsheet
- Database table for storing tracking information
- Admin interface for file upload
- Option to clear existing data before import
- Frontend shortcode `[ho_tracking_table]` for displaying tracking search
- Real-time AJAX search functionality with 500ms debounce
- Search by tracking code or recipient name
- Responsive table design with mobile-friendly layout
- Support for both English and Persian (Farsi) column headers
- Status badge styling with color coding
- Security features:
  - Nonce verification for AJAX requests
  - Capability checks for admin functions
  - SQL injection prevention
  - File type validation
  - Input sanitization and output escaping
- Sample CSV data file for testing
- Comprehensive documentation (README, USAGE guide)
- Automatic database table creation on plugin activation
- Column name flexibility (supports multiple variations)
- Beautiful admin interface with upload progress indication
- CSS styling for both admin and frontend
- Multi-language support (English/Persian)

### Features
- Upload CSV, XLS, or XLSX files
- Auto-detect column names in multiple languages
- Real-time search as you type
- Mobile-responsive design
- Clean and modern UI
- Fast and efficient search
- Bulk data import
- Option to replace or append data

### Requirements
- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher
- Optional: Composer for Excel file support

### Security
- All AJAX calls are protected with WordPress nonces
- User capability checks (manage_options)
- Prepared SQL statements to prevent injection
- File type validation before processing
- Sanitized input, escaped output

### Known Issues
- Excel file support requires Composer dependencies
- Large files (>1000 rows) may take longer to process
- Date formats are not automatically converted

### Future Enhancements
- Pagination for large datasets
- Export functionality
- Advanced filtering options
- Date format auto-detection and conversion
- Bulk delete functionality
- Import history tracking
- Email notifications for tracking updates
- REST API endpoints
- Multi-file upload support
