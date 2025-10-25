<?php
/**
 * OneStop Agency CRM - Application Configuration
 * Version: 2.0
 * 
 * Core application settings and environment variables
 */

return [
    // Application Details
    'app_name' => 'OneStop Agency CRM',
    'app_version' => '2.0.0',
    'environment' => getenv('APP_ENV') ?: 'development', // production, staging, development
    
    // URL Configuration
    'base_url' => getenv('BASE_URL') ?: 'http://localhost/Agency%20CRM',
    'api_prefix' => '/api/v1',
    
    // Security
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production',
    'jwt_expiry' => 1800, // 30 minutes in seconds
    'session_timeout' => 1800, // 30 minutes
    'password_min_length' => 8,
    'force_https' => getenv('FORCE_HTTPS') === 'true',
    
    // Timezone & Localization
    'timezone' => 'Asia/Kolkata',
    'default_currency' => 'INR',
    'supported_currencies' => ['INR', 'USD', 'AED', 'GBP'],
    'default_language' => 'en',
    
    // Pagination
    'items_per_page' => 20,
    'max_items_per_page' => 100,
    
    // File Uploads
    'upload_max_size' => 10485760, // 10MB in bytes
    'allowed_file_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'],
    'upload_path' => __DIR__ . '/../storage/uploads',
    
    // Email Configuration
    'mail_from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@onestopcrm.com',
    'mail_from_name' => 'OneStop Agency CRM',
    
    // API Rate Limiting
    'rate_limit_requests' => 100,
    'rate_limit_period' => 60, // seconds
    
    // Cache
    'cache_enabled' => true,
    'cache_ttl' => 3600, // 1 hour
    
    // Logs
    'log_path' => __DIR__ . '/../storage/logs',
    'log_level' => 'debug', // debug, info, warning, error
    'log_rotation' => 'weekly',
    
    // Features Toggle
    'features' => [
        'ai_enabled' => getenv('AI_ENABLED') === 'true',
        'notifications_enabled' => true,
        'email_alerts_enabled' => true,
        'time_tracking_enabled' => true,
        'kanban_board_enabled' => true,
    ],
    
    // Idle Detection
    'idle_timeout' => 900, // 15 minutes in seconds
    
    // Backup
    'backup_enabled' => true,
    'backup_schedule' => 'daily',
    'backup_retention_days' => 30,
];

