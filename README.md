# OneStop Agency CRM

**Version:** 2.0  
**Last Updated:** October 2025

## Overview

OneStop Agency CRM is a comprehensive, unified platform designed for digital agencies to manage sales, pricing, projects, teams, and finances in one centralized system. It combines CRM, project management, time tracking, invoicing, and AI-powered insights to streamline agency operations.

## Key Features

### 🔐 Authentication & Security
- Supabase-based authentication
- JWT token management
- Role-Based Access Control (RBAC)
- Password reset functionality
- Secure session management

### 💰 Dynamic Pricing Calculator
- Hourly rate × Role hours calculation
- Configurable overhead, margin, and tax percentages
- Multi-currency support (INR, USD, AED, GBP)
- "No-Brainer Offer" value multiplier (200%)
- Export quotes as PDF

### 👥 Client & CRM Management
- Complete client database
- Communication tracking
- Source attribution
- Related deals, quotes, and invoices
- Custom notes and reminders

### 📊 Sales Pipeline (Kanban)
- Visual pipeline with drag-and-drop
- Stages: Lead → Contacted → Qualified → Proposal Sent → Negotiation → Won/Lost
- Real-time value aggregation
- Follow-up reminders
- Automatic deal-to-project conversion

### 📋 Project Management
- Project planning and tracking
- Budget management
- Team allocation
- Progress monitoring
- File attachments

### ✅ Task & Kanban Board
- Per-project task management
- Status: To-Do / In Progress / Blocked / Done
- Priority levels
- Assignees and due dates
- Comment threads

### ⏱️ Time Tracking
- Start/pause/stop timer
- Idle detection (auto-pause after 15 min)
- Manual time entry with justification
- Billable vs non-billable hours
- Export timesheets (CSV/PDF)

### 💳 Invoicing & Payments
- Automated invoice generation
- Razorpay & Stripe integration
- Payment tracking
- Tax calculations (GST, VAT)
- Overdue notifications

### 📈 Analytics & Dashboards
- **C-Level Dashboard:** Revenue, profit, margins, client retention
- **Project Manager Dashboard:** Project progress, team load, budget vs actual
- **Sales Dashboard:** Pipeline summary, conversion rates, top clients
- **Team Dashboard:** Assigned tasks, time logged, performance

### 🔔 Notifications & Alerts
- In-app notifications
- Task assignments
- Payment receipts
- Deadline reminders
- Optional email alerts

### 🤖 AI Features
- **AI Pricing Assistant:** Market-based pricing suggestions
- **AI Resource Allocator:** Optimal task allocation based on workload
- **AI Deal Scorer:** Likelihood of deal conversion
- **AI Insights:** Daily "Top 5 things needing attention"

### 📊 Reporting & Exports
- Financial reports (monthly, quarterly, yearly)
- Productivity reports
- Resource utilization
- Export formats: CSV, PDF, XLSX

## Technology Stack

| Layer | Technology |
|-------|-----------|
| **Frontend** | HTML5, CSS3, Bootstrap 5, jQuery/AJAX |
| **Backend** | PHP 8.x (MVC Modular Framework) |
| **Database** | MySQL / Supabase |
| **Authentication** | Supabase Auth (JWT-based) |
| **Payment Gateways** | Razorpay (INR), Stripe (USD) |
| **PDF Generation** | TCPDF / FPDF |
| **Charts** | Chart.js |
| **AI** | OpenAI API |
| **Hosting** | DigitalOcean VPS / Hostinger Business Cloud |

## Installation

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer (optional, for dependencies)
- Web server (Apache/Nginx)

### Step 1: Clone Repository

```bash
git clone https://github.com/your-org/onestop-crm.git
cd onestop-crm
```

### Step 2: Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and configure your settings:
- Database credentials
- Supabase configuration
- Payment gateway keys
- OpenAI API key (if using AI features)

### Step 3: Create Database

```bash
mysql -u root -p
CREATE DATABASE onestop_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 4: Import Schema

```bash
mysql -u root -p onestop_crm < database/schema.sql
```

### Step 5: Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 storage/logs
chmod -R 755 storage/uploads
```

### Step 6: Configure Web Server

#### Apache (.htaccess)

Create/update `.htaccess` in the `public` directory:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/onestop-crm/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 7: Access Application

Navigate to `http://your-domain.com` or `http://localhost/Agency%20CRM`

**Default Admin Credentials:**
- Email: `admin@onestopcrm.com`
- Password: `admin123`

**⚠️ IMPORTANT:** Change the default admin password immediately after first login!

## API Documentation

### Authentication Endpoints

```
POST /api/v1/auth/login
POST /api/v1/auth/register
POST /api/v1/auth/logout
POST /api/v1/auth/forgot-password
POST /api/v1/auth/reset-password
GET  /api/v1/auth/me
```

### Client Endpoints

```
GET    /api/v1/clients
GET    /api/v1/clients/{id}
POST   /api/v1/clients
PUT    /api/v1/clients/{id}
DELETE /api/v1/clients/{id}
```

### Deal/Pipeline Endpoints

```
GET    /api/v1/deals
GET    /api/v1/deals/{id}
POST   /api/v1/deals
PUT    /api/v1/deals/{id}
PUT    /api/v1/deals/{id}/stage
DELETE /api/v1/deals/{id}
GET    /api/v1/pipeline
```

### Quote Endpoints

```
GET    /api/v1/quotes
GET    /api/v1/quotes/{id}
POST   /api/v1/quotes
POST   /api/v1/quotes/calculate
PUT    /api/v1/quotes/{id}
DELETE /api/v1/quotes/{id}
GET    /api/v1/quotes/{id}/pdf
```

### Project Endpoints

```
GET    /api/v1/projects
GET    /api/v1/projects/{id}
POST   /api/v1/projects
PUT    /api/v1/projects/{id}
DELETE /api/v1/projects/{id}
POST   /api/v1/projects/{id}/team
DELETE /api/v1/projects/{id}/team/{userId}
```

### Dashboard Endpoints

```
GET /api/v1/dashboard/admin
GET /api/v1/dashboard/pm
GET /api/v1/dashboard/sales
GET /api/v1/dashboard/team
```

### AI Endpoints

```
POST /api/v1/ai/pricing-suggestion
POST /api/v1/ai/resource-allocation
POST /api/v1/ai/deal-score
GET  /api/v1/ai/insights
```

All API requests require authentication via JWT token in the `Authorization` header:

```
Authorization: Bearer <your-jwt-token>
```

## User Roles & Permissions

| Role | Access Level |
|------|-------------|
| **Admin** | Full system access |
| **Project Manager** | Projects, tasks, team, reports |
| **Sales Executive** | Leads, deals, quotes, clients |
| **Team Member** | Assigned tasks, time tracking |
| **Finance Officer** | Invoices, payments, financial reports |
| **Client** | View-only access to quotes and invoices |

## Configuration

### Application Settings

Edit `config/app.php` to configure:
- Base URL
- Timezone
- Currency
- Session timeout
- File upload limits

### Database Settings

Edit `config/database.php` for database connection settings.

### Pricing Defaults

Edit `config/constants.php` to change default values:
- `DEFAULT_OVERHEAD_PERCENTAGE` (20%)
- `DEFAULT_MARGIN_PERCENTAGE` (30%)
- `DEFAULT_GST_PERCENTAGE` (18%)
- `DEFAULT_OFFER_MULTIPLIER` (2.0)

## Backup & Restore

### Automated Daily Backup

Configure in `config/app.php`:

```php
'backup_enabled' => true,
'backup_schedule' => 'daily',
'backup_retention_days' => 30,
```

### Manual Backup

```bash
mysqldump -u root -p onestop_crm > backup_$(date +%Y%m%d).sql
```

### Restore

```bash
mysql -u root -p onestop_crm < backup_20251025.sql
```

## Troubleshooting

### Common Issues

**Issue:** Cannot login / Token expired
- **Solution:** Check JWT secret in `.env`, clear browser cache

**Issue:** Database connection failed
- **Solution:** Verify database credentials in `.env`

**Issue:** File upload fails
- **Solution:** Check `storage/uploads` permissions (755)

**Issue:** API returns 500 error
- **Solution:** Check `storage/logs/error_*.log` for details

## Security Best Practices

1. ✅ Change default admin password
2. ✅ Use strong JWT secret
3. ✅ Enable HTTPS in production
4. ✅ Keep PHP and dependencies updated
5. ✅ Regular database backups
6. ✅ Restrict file upload types
7. ✅ Use environment variables for sensitive data
8. ✅ Enable CORS only for trusted domains

## Development Roadmap

### Phase 2 (Q1 2026)
- [ ] Mobile app (React Native)
- [ ] Slack/Teams integration
- [ ] Advanced AI chatbot
- [ ] Multi-tenant SaaS version

### Phase 3 (Q2 2026)
- [ ] Performance bonus tracker
- [ ] API marketplace for plugins
- [ ] Advanced reporting with BI tools

## Support

For issues, questions, or feature requests:
- Email: support@onestopcrm.com
- Documentation: https://docs.onestopcrm.com
- GitHub Issues: https://github.com/your-org/onestop-crm/issues

## License

Proprietary Software © 2025 Outreach IT. All rights reserved.

## Credits

**Product Owner:** Shouieb Syed  
**Development Team:** Outreach IT Product & Engineering Team  
**Version:** 2.0  
**Release Date:** October 2025

---

**Built with ❤️ by Outreach IT**

