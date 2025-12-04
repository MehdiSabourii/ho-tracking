# HO Tracking - راهنمای استفاده / Usage Guide

This guide provides step-by-step instructions for using the HO Tracking plugin in both English and Persian.

---

## English Instructions

### Installation

1. **Upload the Plugin**
   - Download the plugin files
   - Upload to `/wp-content/plugins/ho-tracking/`
   - Go to WordPress Admin → Plugins
   - Activate "HO Tracking"

2. **Install Dependencies (Optional - for Excel support)**
   ```bash
   cd wp-content/plugins/ho-tracking
   composer install --no-dev
   ```
   
   **Note:** Without Composer, the plugin works perfectly with CSV files.

### Uploading Tracking Data

1. **Access the Admin Page**
   - Go to WordPress Admin
   - Click on "HO Tracking" in the left menu

2. **Prepare Your File**
   - Create a CSV or Excel file with your tracking data
   - Required column: `tracking_code` (or alternatives: tracking, code, کد رهگیری)
   - Optional columns: 
     - recipient_name (or: recipient, name, نام)
     - status (or: وضعیت)
     - date_sent (or: تاریخ ارسال)
     - date_delivered (or: تاریخ تحویل)
     - notes (or: توضیحات)

3. **Upload the File**
   - Click "Select File" button
   - Choose your CSV or Excel file
   - Check "Clear Existing Data" if you want to replace all records
   - Click "Upload and Import"
   - Wait for the success message

### Displaying the Tracking Table

1. **Create or Edit a Page**
   - Go to WordPress Admin → Pages → Add New (or edit existing)
   - In the page editor, add the shortcode: `[ho_tracking_table]`
   - Publish or Update the page

2. **View the Page**
   - Visit the page on your website
   - You'll see the search interface and tracking table

### How Users Search for Tracking

Users can:
- Enter their tracking code in the search box
- Enter recipient name to search
- See results update automatically as they type
- View all tracking details in a responsive table

---

## دستورالعمل فارسی

### نصب افزونه

1. **بارگذاری افزونه**
   - فایل‌های افزونه را دانلود کنید
   - به مسیر `/wp-content/plugins/ho-tracking/` آپلود کنید
   - به بخش مدیریت وردپرس → افزونه‌ها بروید
   - افزونه "HO Tracking" را فعال کنید

2. **نصب وابستگی‌ها (اختیاری - برای پشتیبانی اکسل)**
   ```bash
   cd wp-content/plugins/ho-tracking
   composer install --no-dev
   ```
   
   **توجه:** بدون نصب Composer، افزونه به خوبی با فایل‌های CSV کار می‌کند.

### آپلود اطلاعات رهگیری

1. **دسترسی به صفحه مدیریت**
   - به پنل مدیریت وردپرس بروید
   - روی "HO Tracking" در منوی سمت چپ کلیک کنید

2. **آماده‌سازی فایل**
   - یک فایل CSV یا اکسل با اطلاعات رهگیری ایجاد کنید
   - ستون الزامی: `tracking_code` (یا معادل‌های: tracking, code, کد رهگیری)
   - ستون‌های اختیاری:
     - recipient_name (یا: recipient, name, نام، گیرنده)
     - status (یا: وضعیت)
     - date_sent (یا: تاریخ ارسال)
     - date_delivered (یا: تاریخ تحویل)
     - notes (یا: توضیحات)

3. **آپلود فایل**
   - روی دکمه "Select File" کلیک کنید
   - فایل CSV یا اکسل خود را انتخاب کنید
   - اگر می‌خواهید تمام رکوردها جایگزین شوند، "Clear Existing Data" را علامت بزنید
   - روی "Upload and Import" کلیک کنید
   - منتظر پیام موفقیت بمانید

### نمایش جدول رهگیری

1. **ایجاد یا ویرایش صفحه**
   - به پنل مدیریت وردپرس → صفحات → افزودن صفحه جدید بروید (یا صفحه موجود را ویرایش کنید)
   - در ویرایشگر صفحه، شورت‌کد زیر را اضافه کنید: `[ho_tracking_table]`
   - صفحه را منتشر یا به‌روزرسانی کنید

2. **مشاهده صفحه**
   - به صفحه در وب‌سایت خود بروید
   - رابط جستجو و جدول رهگیری را خواهید دید

### نحوه جستجوی کاربران

کاربران می‌توانند:
- کد رهگیری خود را در کادر جستجو وارد کنند
- نام گیرنده را برای جستجو وارد کنند
- نتایج را به صورت خودکار در حین تایپ مشاهده کنند
- تمام جزئیات رهگیری را در یک جدول واکنش‌گرا ببینند

---

## Sample Data Format / نمونه فرمت داده

### CSV Example:
```csv
tracking_code,recipient_name,status,date_sent,date_delivered,notes
TR123456789,John Doe,Delivered,2024-01-15,2024-01-18,Package delivered successfully
TR987654321,Jane Smith,In Transit,2024-01-20,,On the way to destination
```

### نمونه CSV فارسی:
```csv
کد رهگیری,نام,وضعیت,تاریخ ارسال,تاریخ تحویل,توضیحات
TR321654987,محمد احمدی,تحویل شده,1402/10/21,1402/10/25,تحویل با موفقیت انجام شد
TR456123789,فاطمه رضایی,در حال ارسال,1402/11/01,,در مسیر ارسال
```

---

## Troubleshooting / عیب‌یابی

### English
- **Excel files not working?** Install Composer dependencies or use CSV format
- **No results showing?** Make sure you've uploaded data and the tracking codes match
- **Shortcode not displaying?** Check if you copied the exact shortcode: `[ho_tracking_table]`

### فارسی
- **فایل‌های اکسل کار نمی‌کنند؟** وابستگی‌های Composer را نصب کنید یا از فرمت CSV استفاده کنید
- **نتایجی نمایش داده نمی‌شود؟** مطمئن شوید که داده‌ها را آپلود کرده‌اید و کدهای رهگیری مطابقت دارند
- **شورت‌کد نمایش داده نمی‌شود؟** بررسی کنید که شورت‌کد دقیق را کپی کرده‌اید: `[ho_tracking_table]`

---

## Advanced Usage / استفاده پیشرفته

### Database Table Structure

The plugin creates a table `{prefix}_ho_tracking` with the following structure:

| Column | Type | Description |
|--------|------|-------------|
| id | bigint(20) | Auto-increment ID |
| tracking_code | varchar(255) | Tracking code (indexed) |
| recipient_name | varchar(255) | Recipient name |
| status | varchar(100) | Delivery status |
| date_sent | datetime | Date sent |
| date_delivered | datetime | Date delivered |
| notes | text | Additional notes |
| created_at | datetime | Record creation timestamp |

### Customization

You can customize the appearance by adding custom CSS to your theme:

```css
/* Custom styling for tracking table */
.ho-tracking-wrapper {
    /* Your custom styles */
}

.tracking-table {
    /* Your table styles */
}
```

---

## Support / پشتیبانی

For questions or issues:
- GitHub: https://github.com/MehdiSabourii/ho-tracking
- Create an issue with detailed description of your problem

برای سوالات یا مشکلات:
- گیت‌هاب: https://github.com/MehdiSabourii/ho-tracking
- یک issue با توضیحات کامل مشکل خود ایجاد کنید
