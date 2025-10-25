# ✅ OneStop Agency CRM - Setup Complete!

## 🎉 Your CRM is Now Running!

The OneStop Agency CRM has been successfully set up and is running on your local XAMPP server.

---

## 🌐 Access URLs

### Main Application
- **Homepage:** http://localhost:8000/
- **Login Page:** http://localhost:8000/login
- **Dashboard:** http://localhost:8000/dashboard

### API Endpoints
- **Base URL:** http://localhost:8000/api/v1/
- **Login API:** http://localhost:8000/api/v1/auth/login
- **Test Connection:** http://localhost/Agency/test-connection.php

---

## 🔐 Default Credentials

```
Email: admin@onestopcrm.com
Password: admin123
```

**⚠️ IMPORTANT:** Change this password immediately after first login!

---

## ✅ What's Been Configured

### 1. Database Setup
- ✅ Database created: `agencycrm`
- ✅ All tables imported (24 tables)
- ✅ Admin user created
- ✅ Default settings configured
- ✅ Pipeline stages set up

### 2. Application Configuration
- ✅ `.env` file created with XAMPP settings
- ✅ Database connection configured
- ✅ Storage directories created
- ✅ View files created (homepage, login, dashboard)

### 3. Code Fixes Applied
- ✅ Fixed JSON input handling in Controller
- ✅ Updated AuthController to use proper input parsing
- ✅ Fixed admin password hash in database
- ✅ Updated schema.sql with correct password

### 4. Services Running
- ✅ MySQL running on port 3306
- ✅ Apache running on port 80
- ✅ PHP 8.0.30 built-in server on port 8000

---

## 🚀 Quick Start Guide

### Step 1: Login
1. Open http://localhost:8000/login
2. Enter the default credentials above
3. Click "Sign In"

### Step 2: Explore the Dashboard
- View statistics (clients, deals, projects, tasks)
- Access quick links for common actions
- Check API status

### Step 3: Test the API
Using any API client (Postman, Insomnia, or curl):

```bash
# Login to get token
POST http://localhost:8000/api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@onestopcrm.com",
  "password": "admin123"
}

# Use the token for other requests
GET http://localhost:8000/api/v1/clients
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 📚 Available API Endpoints

### Authentication
- `POST /api/v1/auth/login` - User login
- `POST /api/v1/auth/logout` - User logout
- `GET /api/v1/auth/me` - Get current user
- `POST /api/v1/auth/refresh` - Refresh token

### Clients
- `GET /api/v1/clients` - List all clients
- `POST /api/v1/clients` - Create new client
- `GET /api/v1/clients/{id}` - Get client details
- `PUT /api/v1/clients/{id}` - Update client
- `DELETE /api/v1/clients/{id}` - Delete client

### Deals & Pipeline
- `GET /api/v1/deals` - List all deals
- `POST /api/v1/deals` - Create new deal
- `GET /api/v1/pipeline` - Get pipeline view

### Projects
- `GET /api/v1/projects` - List all projects
- `POST /api/v1/projects` - Create new project
- `GET /api/v1/projects/{id}` - Get project details

### Tasks
- `GET /api/v1/tasks` - List all tasks
- `POST /api/v1/tasks` - Create new task

### Quotes & Invoices
- `GET /api/v1/quotes` - List quotes
- `POST /api/v1/quotes` - Create quote
- `GET /api/v1/invoices` - List invoices
- `POST /api/v1/invoices` - Create invoice

### Dashboard
- `GET /api/v1/dashboard/admin` - Admin dashboard
- `GET /api/v1/dashboard/pm` - Project Manager dashboard
- `GET /api/v1/dashboard/sales` - Sales dashboard

See `docs/API_DOCUMENTATION.md` for complete API reference.

---

## 🛠️ Technical Details

### Database Structure
- **Database:** agencycrm
- **Tables:** 24 tables including users, clients, deals, projects, tasks, invoices, etc.
- **Admin User ID:** 1
- **Character Set:** utf8mb4
- **Collation:** utf8mb4_unicode_ci

### File Structure
```
C:\xampp\htdocs\Agency\
├── app/
│   ├── controllers/    # 18 controllers
│   ├── models/         # 16 models
│   ├── middleware/     # Auth & Role middleware
│   └── views/          # HTML views
├── config/             # Configuration files
├── core/               # Core framework files
├── database/           # Database schema
├── docs/               # Documentation
├── public/             # Public entry point
├── storage/            # Logs, uploads, cache
└── .env                # Environment configuration
```

---

## 🔧 Troubleshooting

### Issue: Can't access the application
**Solution:** Make sure the PHP built-in server is running:
```bash
cd C:\xampp\htdocs\Agency\public
C:\xampp\php\php.exe -S localhost:8000
```

### Issue: Database connection error
**Solution:** 
1. Check MySQL is running in XAMPP Control Panel
2. Verify `.env` file has correct credentials
3. Test connection: http://localhost/Agency/test-connection.php

### Issue: API returns 401 Unauthorized
**Solution:**
1. Make sure you're logged in
2. Include the Bearer token in Authorization header
3. Token expires after 30 minutes - use refresh endpoint

---

## 📖 Next Steps

1. **Change Admin Password**
   - Login and update your password immediately

2. **Configure Settings**
   - Update company information
   - Set up payment gateways (optional)
   - Configure email settings (optional)

3. **Start Adding Data**
   - Create your first client
   - Add services and roles
   - Set up a project

4. **Explore Features**
   - Sales pipeline management
   - Time tracking
   - Invoicing
   - Reports and analytics

---

## 📞 Support & Documentation

- **Full Documentation:** See `README.md`
- **Setup Guide:** See `XAMPP_SETUP_GUIDE.md`
- **API Docs:** See `docs/API_DOCUMENTATION.md`
- **Deployment Guide:** See `docs/DEPLOYMENT_GUIDE.md`

---

## 🎯 Key Features to Try

1. ✨ **Dynamic Pricing Calculator** - Calculate project costs with overhead & margins
2. 📊 **Visual Sales Pipeline** - Drag-and-drop deal management
3. ⏱️ **Time Tracking** - Track billable hours with idle detection
4. 💰 **Invoice Generation** - Create and send professional invoices
5. 📈 **Analytics Dashboards** - Real-time business insights
6. 🤖 **AI Features** - Smart pricing suggestions (requires OpenAI API key)

---

## 🔐 Security Reminders

- ✅ Change default admin password
- ✅ Keep `.env` file secure (already in .gitignore)
- ✅ Regular database backups
- ✅ Update PHP and dependencies regularly
- ✅ Use HTTPS in production
- ✅ Set strong JWT secret for production

---

**Version:** 2.0  
**Setup Date:** October 25, 2025  
**Built by:** Outreach IT  

**🎉 Happy CRM-ing! 🚀**

