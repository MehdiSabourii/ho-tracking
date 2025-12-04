# HO Tracking Plugin - Visual Guide

This document provides a visual guide to the HO Tracking plugin interface and functionality.

## Admin Interface

### Upload Page
The admin interface (accessible via WordPress Admin → HO Tracking) provides:

1. **File Upload Section**
   - File selector (accepts .csv, .xls, .xlsx)
   - Option to clear existing data before import
   - Upload and Import button
   - Real-time upload progress indication

2. **Expected File Format Guide**
   - Lists all supported column names (English and Persian)
   - Shows which columns are required vs optional
   - Provides examples for each field type

3. **Usage Instructions**
   - Shows the shortcode: `[ho_tracking_table]`
   - Explains where to add the shortcode

### Admin Features
```
┌─────────────────────────────────────────────────────┐
│ HO Tracking - Upload Tracking Data                 │
├─────────────────────────────────────────────────────┤
│                                                     │
│ Upload Tracking File                                │
│ ────────────────────────────────────────────────── │
│                                                     │
│ Select File: [Choose File] [sample-data.csv]       │
│ Accepted formats: CSV, XLS, XLSX                    │
│                                                     │
│ ☐ Clear Existing Data                              │
│ Delete all existing tracking records before import  │
│                                                     │
│ [Upload and Import]                                 │
│                                                     │
│ ✓ Success: 5 records imported successfully          │
│                                                     │
├─────────────────────────────────────────────────────┤
│ Expected File Format                                │
│ ────────────────────────────────────────────────── │
│                                                     │
│ • tracking_code (Required) - The tracking code     │
│ • recipient_name (Optional) - Recipient name       │
│ • status (Optional) - Delivery status               │
│ • date_sent (Optional) - Date sent                  │
│ • date_delivered (Optional) - Date delivered        │
│ • notes (Optional) - Additional notes               │
│                                                     │
└─────────────────────────────────────────────────────┘
```

## Frontend Interface

### Tracking Search Page
When users add `[ho_tracking_table]` to a page, they see:

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│           Track Your Package                        │
│                                                     │
│  ┌──────────────────────────────────────┐          │
│  │ Enter tracking code or name...       │ [Search] │
│  └──────────────────────────────────────┘          │
│                                                     │
└─────────────────────────────────────────────────────┘

Results Table:
┌──────────────────────────────────────────────────────────────────────┐
│ Tracking Code │ Recipient  │ Status      │ Sent       │ Delivered   │
├──────────────────────────────────────────────────────────────────────┤
│ TR123456789   │ John Doe   │ Delivered   │ 2024-01-15 │ 2024-01-18 │
│ TR987654321   │ Jane Smith │ In Transit  │ 2024-01-20 │            │
│ TR456789123   │ Ahmad R.   │ Pending     │ 2024-01-22 │            │
└──────────────────────────────────────────────────────────────────────┘
```

### Search Functionality
- **Real-time Search**: Results update as you type (500ms debounce)
- **Search Fields**: Searches both tracking code and recipient name
- **No Results Message**: Shows friendly message when no matches found
- **Loading Indicator**: Displays "Searching..." during AJAX requests

### Status Badges
The plugin displays status with color-coded badges:

- 🟢 **Delivered** - Green badge (background: #d4edda, text: #155724)
- 🟡 **In Transit** - Yellow badge (background: #fff3cd, text: #856404)
- 🔴 **Pending** - Red badge (background: #f8d7da, text: #721c24)

### Mobile Responsive Design
On mobile devices (< 768px), the table transforms:
- Headers disappear
- Each row becomes a card
- Labels appear inline with data
- Touch-friendly interface
- Full-width search box

```
Mobile View:
┌─────────────────────────┐
│ Track Your Package      │
│ ┌─────────────────────┐ │
│ │ Search...           │ │
│ └─────────────────────┘ │
│ [Search]                │
└─────────────────────────┘

┌─────────────────────────┐
│ Tracking Code:          │
│ TR123456789             │
│ Recipient: John Doe     │
│ Status: Delivered       │
│ Sent: 2024-01-15        │
│ Delivered: 2024-01-18   │
└─────────────────────────┘
```

## User Workflow

### For Administrators
1. Go to WordPress Admin → HO Tracking
2. Click "Choose File" and select CSV or Excel file
3. Optionally check "Clear Existing Data"
4. Click "Upload and Import"
5. See success message with import count
6. Add `[ho_tracking_table]` shortcode to desired page

### For End Users
1. Visit page with tracking table
2. Enter tracking code or recipient name
3. Results appear automatically (or click Search)
4. View tracking details in formatted table
5. Check status badges for delivery status

## Data Flow

```
CSV/Excel File
     ↓
Upload via Admin Interface
     ↓
File Parsing (with validation)
     ↓
Database Insert (wp_ho_tracking table)
     ↓
Frontend Shortcode Display
     ↓
User AJAX Search
     ↓
Filtered Results Display
```

## Sample CSV Format

```csv
tracking_code,recipient_name,status,date_sent,date_delivered,notes
TR123456789,John Doe,Delivered,2024-01-15,2024-01-18,Package delivered successfully
TR987654321,Jane Smith,In Transit,2024-01-20,,On the way to destination
TR456789123,Ahmad Rezaei,Pending,2024-01-22,,Awaiting shipment
```

## Supported Column Names

The plugin recognizes multiple column name variations:

| Field | Supported Names |
|-------|----------------|
| Tracking Code | tracking_code, tracking, code, کد رهگیری, کد |
| Recipient | recipient_name, recipient, name, نام, گیرنده |
| Status | status, وضعیت |
| Date Sent | date_sent, sent_date, تاریخ ارسال |
| Date Delivered | date_delivered, delivered_date, تاریخ تحویل |
| Notes | notes, note, توضیحات |

## Color Scheme

### Admin Interface
- Primary: WordPress blue (#0073aa)
- Success: Green (#d4edda)
- Error: Red (#f8d7da)
- Background: Light gray (#f9f9f9)

### Frontend Interface
- Primary: WordPress blue (#0073aa)
- Hover: Darker blue (#005a87)
- Table Header: Blue (#0073aa)
- Row Hover: Light gray (#f9f9f9)
- Border: Light gray (#eee)

## Performance Notes

- AJAX requests are debounced (500ms) to reduce server load
- Database queries use indexes on tracking_code for fast lookups
- Results limited to 100 records when searching without filter
- File parsing handles large files efficiently
- Row validation prevents array_combine errors

## Security Measures

✅ Nonce verification on all AJAX requests
✅ Capability checks (manage_options)
✅ SQL injection prevention
✅ XSS prevention (escaped output)
✅ File type validation
✅ Error logging for debugging
✅ Sanitized input fields
