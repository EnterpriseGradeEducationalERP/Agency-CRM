# OneStop Agency CRM - Deployment Guide

## Production Deployment Checklist

### Pre-Deployment

- [ ] Backup existing database (if upgrading)
- [ ] Review and test all changes in staging
- [ ] Update `.env` with production values
- [ ] Generate strong JWT secret
- [ ] Configure production database
- [ ] Set up SSL certificate
- [ ] Configure payment gateways
- [ ] Test email sending
- [ ] Configure backup schedule

### Deployment Steps

#### 1. Server Requirements

**Minimum Requirements:**
- CPU: 2 cores
- RAM: 4GB
- Storage: 20GB SSD
- OS: Ubuntu 20.04+ or CentOS 8+

**Recommended for Production:**
- CPU: 4 cores
- RAM: 8GB
- Storage: 50GB SSD

#### 2. Install Dependencies

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.x
sudo apt install php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-curl php8.1-mbstring php8.1-xml php8.1-zip php8.1-gd -y

# Install MySQL
sudo apt install mysql-server -y

# Install Nginx
sudo apt install nginx -y

# Install Certbot for SSL
sudo apt install certbot python3-certbot-nginx -y
```

#### 3. Clone & Configure Application

```bash
# Navigate to web root
cd /var/www

# Clone repository
git clone https://github.com/your-org/onestop-crm.git
cd onestop-crm

# Set ownership
sudo chown -R www-data:www-data /var/www/onestop-crm
sudo chmod -R 755 /var/www/onestop-crm

# Set storage permissions
sudo chmod -R 775 storage/
sudo chmod -R 775 storage/logs
sudo chmod -R 775 storage/uploads
```

#### 4. Configure Database

```bash
# Access MySQL
sudo mysql -u root -p

# Create database
CREATE DATABASE onestop_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user
CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'strong_password_here';

# Grant privileges
GRANT ALL PRIVILEGES ON onestop_crm.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u crm_user -p onestop_crm < database/schema.sql
```

#### 5. Configure Environment

```bash
# Copy env file
cp .env.example .env

# Edit configuration
nano .env
```

**Production .env Example:**
```env
APP_ENV=production
APP_DEBUG=false
BASE_URL=https://crm.yourdomain.com
JWT_SECRET=your-super-secret-random-key-here
FORCE_HTTPS=true

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=onestop_crm
DB_USERNAME=crm_user
DB_PASSWORD=strong_password_here

SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_ROLE_KEY=your-service-key

RAZORPAY_KEY_ID=your-razorpay-key
RAZORPAY_KEY_SECRET=your-razorpay-secret

STRIPE_PUBLIC_KEY=your-stripe-public-key
STRIPE_SECRET_KEY=your-stripe-secret-key

AI_ENABLED=true
OPENAI_API_KEY=your-openai-key

LOG_LEVEL=error
```

#### 6. Configure Nginx

Create Nginx config: `/etc/nginx/sites-available/onestop-crm`

```nginx
server {
    listen 80;
    server_name crm.yourdomain.com;
    root /var/www/onestop-crm/public;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/onestop-crm-access.log;
    error_log /var/log/nginx/onestop-crm-error.log;

    # Max upload size
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/onestop-crm /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### 7. Configure SSL

```bash
sudo certbot --nginx -d crm.yourdomain.com
```

Follow prompts to configure SSL. Certbot will automatically update Nginx config.

#### 8. Configure PHP-FPM

Edit: `/etc/php/8.1/fpm/pool.d/www.conf`

```ini
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

Edit: `/etc/php/8.1/fpm/php.ini`

```ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 300
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.1-fpm
```

#### 9. Set Up Automated Backups

Create backup script: `/usr/local/bin/backup-crm.sh`

```bash
#!/bin/bash

BACKUP_DIR="/backups/onestop-crm"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="onestop_crm"
DB_USER="crm_user"
DB_PASS="your_db_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup uploads
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz /var/www/onestop-crm/storage/uploads

# Delete backups older than 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

Make executable:
```bash
sudo chmod +x /usr/local/bin/backup-crm.sh
```

Add to crontab:
```bash
sudo crontab -e

# Add line for daily backup at 2 AM
0 2 * * * /usr/local/bin/backup-crm.sh >> /var/log/crm-backup.log 2>&1
```

#### 10. Set Up Monitoring

Install monitoring tools:
```bash
# Install htop for server monitoring
sudo apt install htop -y

# Install fail2ban for security
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

#### 11. Configure Firewall

```bash
# Enable UFW
sudo ufw enable

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP & HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Check status
sudo ufw status
```

### Post-Deployment

#### 1. Verify Installation

- [ ] Access application via HTTPS
- [ ] Test login functionality
- [ ] Create test client
- [ ] Create test quote
- [ ] Test file upload
- [ ] Test payment gateway (sandbox)
- [ ] Check logs for errors
- [ ] Test API endpoints

#### 2. Performance Optimization

**Enable OPcache:**

Edit `/etc/php/8.1/fpm/php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

**Enable Gzip Compression:**

Add to Nginx config:

```nginx
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript;
```

#### 3. Security Hardening

```bash
# Disable directory listing
# Add to Nginx server block
autoindex off;

# Hide PHP version
# Edit /etc/php/8.1/fpm/php.ini
expose_php = Off

# Secure MySQL
sudo mysql_secure_installation

# Regular updates
sudo apt update && sudo apt upgrade -y
```

#### 4. Set Up Log Rotation

Create `/etc/logrotate.d/onestop-crm`:

```
/var/www/onestop-crm/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### Rollback Procedure

If deployment fails:

```bash
# Restore database backup
gunzip -c /backups/onestop-crm/db_YYYYMMDD.sql.gz | mysql -u crm_user -p onestop_crm

# Restore uploads
tar -xzf /backups/onestop-crm/uploads_YYYYMMDD.tar.gz -C /

# Switch to previous version
git checkout previous-release-tag
```

### Scaling Considerations

**Horizontal Scaling:**
- Use load balancer (Nginx/HAProxy)
- Shared file storage (NFS/S3)
- Redis for session management
- Read replicas for database

**Vertical Scaling:**
- Increase server resources
- Optimize database queries
- Enable caching (Redis/Memcached)

### Monitoring & Maintenance

**Daily:**
- Check error logs
- Monitor disk space
- Review backup logs

**Weekly:**
- Review system performance
- Check for security updates
- Analyze slow queries

**Monthly:**
- Review and archive old logs
- Database optimization
- Security audit

### Troubleshooting

**Issue: 502 Bad Gateway**
```bash
sudo systemctl status php8.1-fpm
sudo tail -f /var/log/nginx/error.log
```

**Issue: Database connection failed**
```bash
mysql -u crm_user -p
# Verify credentials and permissions
```

**Issue: High memory usage**
```bash
htop
# Check PHP-FPM processes
sudo systemctl restart php8.1-fpm
```

### Support

For deployment issues:
- Email: devops@onestopcrm.com
- Documentation: https://docs.onestopcrm.com/deployment

### Emergency Contacts

- Lead Developer: dev@onestopcrm.com
- DevOps Team: devops@onestopcrm.com
- Support: support@onestopcrm.com

