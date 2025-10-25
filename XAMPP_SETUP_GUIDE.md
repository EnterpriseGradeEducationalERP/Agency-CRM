# OneStop Agency CRM - XAMPP Local Setup Guide

## Quick Setup for XAMPP (Windows)

### Prerequisites Checklist
✅ XAMPP installed with:
   - Apache
   - MySQL
   - PHP 7.4+ (PHP 8.x recommended)
   - phpMyAdmin

---

## 🚀 Step-by-Step Setup Process

### Step 1: Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Click **Start** for:
   - ✅ Apache
   - ✅ MySQL

![XAMPP Control Panel](https://via.placeholder.com/400x200?text=Start+Apache+and+MySQL)

**Verify:**
- Apache: Green "Running" status
- MySQL: Green "Running" status

---

### Step 2: Verify PHP Version

1. Open browser and go to: `http://localhost/dashboard`
2. Check PHP version (should be 7.4+ or 8.x)

**OR via Command Line:**
```bash
# Open Command Prompt and run:
cd C:\xampp\php
php -v
```

You should see something like:
```
PHP 8.1.10 (cli) ...
```

---

### Step 3: Create Database

**Option A: Using phpMyAdmin (Recommended for Beginners)**

1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** in left sidebar
3. Enter database name: `agencycrm`
4. Select Collation: `utf8mb4_unicode_ci`
5. Click **"Create"**

![Create Database](https://via.placeholder.com/400x200?text=Create+Database+agencycrm)

**Option B: Using Command Line**

```bash
# Open Command Prompt
cd C:\xampp\mysql\bin

# Login to MySQL
mysql -u root -p
# Press Enter (no password by default in XAMPP)

# Create database
CREATE DATABASE agencycrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Verify it was created
SHOW DATABASES;

# Exit
EXIT;
```

---

### Step 4: Import Database Schema

**Option A: Using phpMyAdmin (Easiest)**

1. Go to: `http://localhost/phpmyadmin`
2. Click on **"agencycrm"** database in left sidebar
3. Click **"Import"** tab at the top
4. Click **"Choose File"** button
5. Navigate to: `C:\xampp\htdocs\Agency CRM\database\schema.sql`
6. Click **"Go"** button at bottom
7. Wait for success message: "Import has been successfully finished"

![Import Database](https://via.placeholder.com/400x200?text=Import+Schema)

**Option B: Using Command Line**

```bash
# Open Command Prompt
cd C:\xampp\htdocs\Agency CRM\database

# Import schema
C:\xampp\mysql\bin\mysql -u root -p agencycrm < schema.sql
# Press Enter (no password)

# Verify import
C:\xampp\mysql\bin\mysql -u root -p agencycrm -e "SHOW TABLES;"
```

You should see 20+ tables listed.

---

### Step 5: Verify Database Tables

1. In phpMyAdmin, click on **"agencycrm"** database
2. You should see these tables:

```
✅ users
✅ clients
✅ deals
✅ pipeline_stages
✅ services
✅ job_roles
✅ quotes
✅ quote_items
✅ projects
✅ project_team
✅ tasks
✅ task_comments
✅ time_logs
✅ invoices
✅ invoice_items
✅ payments
✅ notifications
✅ files
✅ ai_logs
✅ settings
... and more
```

---

### Step 6: Configure Application

The `.env` file has already been created with your XAMPP settings:

**Verify Configuration:**

Open: `C:\xampp\htdocs\Agency CRM\.env`

Should contain:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agencycrm
DB_USERNAME=root
DB_PASSWORD=
BASE_URL=http://localhost/Agency%20CRM
```

✅ **No changes needed!** Already configured for your XAMPP setup.

---

### Step 7: Set Folder Permissions

**Windows Permissions:**

1. Right-click on `C:\xampp\htdocs\Agency CRM\storage` folder
2. Select **"Properties"**
3. Go to **"Security"** tab
4. Click **"Edit"**
5. Select **"Users"** group
6. Check **"Full Control"**
7. Click **"Apply"** and **"OK"**

Repeat for these folders:
- `storage/logs`
- `storage/uploads`
- `storage/cache`
- `storage/backups`

**OR use Command Prompt (as Administrator):**

```bash
cd C:\xampp\htdocs\Agency CRM
icacls storage /grant Users:F /T
```

---

### Step 8: Test Apache URL Rewriting

**Check if mod_rewrite is enabled:**

1. Open: `C:\xampp\apache\conf\httpd.conf`
2. Find this line (use Ctrl+F to search):
   ```
   #LoadModule rewrite_module modules/mod_rewrite.so
   ```
3. **Remove the `#` to uncomment it:**
   ```
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
4. Save file
5. Restart Apache in XAMPP Control Panel

---

### Step 9: Test PHP Extensions

**Required PHP Extensions:**

1. Open: `C:\xampp\php\php.ini`
2. Find and uncomment these extensions (remove `;` at start):

```ini
extension=mysqli
extension=pdo_mysql
extension=mbstring
extension=openssl
extension=curl
extension=gd
extension=fileinfo
```

3. Save file
4. Restart Apache in XAMPP Control Panel

---

### Step 10: Access the Application

🎉 **You're ready!**

Open your browser and go to:

```
http://localhost/Agency%20CRM
```

**OR**

```
http://localhost/Agency CRM
```

**Note:** The browser will automatically encode spaces as `%20`

---

### Step 11: First Login

**Default Admin Credentials:**

- **Email:** `admin@onestopcrm.com`
- **Password:** `admin123`

![Login Screen](https://via.placeholder.com/400x200?text=Login+Screen)

**⚠️ IMPORTANT:** Change the password immediately after first login!

---

### Step 12: Test API Endpoints

**Test the API using your browser or Postman:**

1. **Login to get token:**
```
POST http://localhost/Agency%20CRM/api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@onestopcrm.com",
  "password": "admin123"
}
```

2. **Get user info:**
```
GET http://localhost/Agency%20CRM/api/v1/auth/me
Authorization: Bearer YOUR_TOKEN_HERE
```

3. **Test other endpoints:**
```
GET http://localhost/Agency%20CRM/api/v1/clients
GET http://localhost/Agency%20CRM/api/v1/projects
GET http://localhost/Agency%20CRM/api/v1/dashboard/admin
```

---

## 🔧 Troubleshooting

### Issue 1: "Can't connect to database"

**Solution:**
1. Check MySQL is running in XAMPP Control Panel
2. Verify database name is `agencycrm` (check in phpMyAdmin)
3. Check `.env` file has correct credentials
4. Try connecting manually:
   ```bash
   C:\xampp\mysql\bin\mysql -u root -p agencycrm
   ```

---

### Issue 2: "404 Not Found" or page not loading

**Solution:**
1. Check Apache is running
2. Verify URL is: `http://localhost/Agency%20CRM` (with space or %20)
3. Check `.htaccess` file exists in `public` folder
4. Enable mod_rewrite in Apache (see Step 8)

---

### Issue 3: "Internal Server Error" (500)

**Solution:**
1. Check Apache error log:
   - Location: `C:\xampp\apache\logs\error.log`
2. Check application error log:
   - Location: `C:\xampp\htdocs\Agency CRM\storage\logs\error_*.log`
3. Enable display_errors in `php.ini`:
   ```ini
   display_errors = On
   ```
4. Restart Apache

---

### Issue 4: "Permission denied" when uploading files

**Solution:**
1. Set write permissions on `storage` folder (see Step 7)
2. Check `php.ini` for upload settings:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```
3. Restart Apache

---

### Issue 5: "Blank page" when accessing application

**Solution:**
1. Check PHP error log: `C:\xampp\php\logs\php_error_log`
2. Enable error display in `.env`:
   ```
   APP_DEBUG=true
   ```
3. Check all required files exist:
   - `public/index.php`
   - `core/Database.php`
   - `core/Router.php`

---

## 📱 Testing Checklist

After setup, test these features:

### ✅ Authentication
- [ ] Login with admin credentials
- [ ] Logout
- [ ] Check "Remember me" works
- [ ] Test wrong password (should fail)

### ✅ Clients Module
- [ ] Go to: `http://localhost/Agency%20CRM/api/v1/clients`
- [ ] Create new client via API
- [ ] View client details
- [ ] Update client
- [ ] Delete test client

### ✅ Services Module
- [ ] View services list
- [ ] Create a new service
- [ ] Assign roles to service

### ✅ Pricing Calculator
- [ ] Test pricing calculation API
- [ ] Verify overhead, margin, tax calculations
- [ ] Check offer multiplier (2x value)

### ✅ Projects Module
- [ ] Create a new project
- [ ] Add team members
- [ ] Create tasks

### ✅ Time Tracking
- [ ] Start a timer
- [ ] Stop timer
- [ ] Add manual time entry

### ✅ File Upload
- [ ] Try uploading a PDF file
- [ ] Check file appears in `storage/uploads`
- [ ] Download the file

---

## 🎯 Quick Access URLs

After setup, bookmark these URLs:

```
Application: http://localhost/Agency%20CRM
phpMyAdmin:  http://localhost/phpmyadmin
API Login:   http://localhost/Agency%20CRM/api/v1/auth/login
Dashboard:   http://localhost/Agency%20CRM/dashboard
```

---

## 🔐 Security Notes for Local Development

**Current Settings (Development Only):**
- ✅ Debug mode enabled
- ✅ Display errors enabled
- ✅ No HTTPS required
- ✅ No password for MySQL root
- ✅ Simple JWT secret

**⚠️ For Production:**
- Change all passwords
- Disable debug mode
- Enable HTTPS
- Use strong JWT secret
- Set up proper database user with limited privileges

---

## 📊 Database Structure

Your database now has:
- **20+ tables** with relationships
- **Default admin user** (admin@onestopcrm.com)
- **7 pipeline stages** (Lead → Won/Lost)
- **Default settings** configured
- **Sample data** ready for testing

---

## 🆘 Need Help?

**Check Logs:**
1. Apache Error Log: `C:\xampp\apache\logs\error.log`
2. PHP Error Log: `C:\xampp\php\logs\php_error_log`
3. Application Log: `C:\xampp\htdocs\Agency CRM\storage\logs\`

**Common Log Locations:**
```
C:\xampp\apache\logs\error.log
C:\xampp\php\logs\php_error_log
C:\xampp\htdocs\Agency CRM\storage\logs\error_*.log
C:\xampp\htdocs\Agency CRM\storage\logs\database_*.log
```

**Restart Everything:**
1. Stop Apache and MySQL in XAMPP
2. Wait 5 seconds
3. Start them again
4. Clear browser cache (Ctrl+Shift+Delete)
5. Try accessing again

---

## 🎉 Success!

If you can see the login page or API responses, **congratulations!** Your OneStop Agency CRM is now running on XAMPP.

**Next Steps:**
1. Change admin password
2. Create test client
3. Create test quote
4. Explore all modules
5. Read API documentation in `docs/API_DOCUMENTATION.md`

---

## 📞 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review error logs
3. Verify all steps were completed
4. Check XAMPP services are running

**Happy CRM-ing! 🚀**

