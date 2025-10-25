<?php
/**
 * Database Configuration
 * Supports both local MySQL and Supabase hosted MySQL
 */

return [
    'default' => getenv('DB_CONNECTION') ?: 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => getenv('DB_PORT') ?: '3306',
            'database' => getenv('DB_DATABASE') ?: 'agencycrm',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => 'InnoDB',
        ],
        
        'supabase' => [
            'driver' => 'mysql',
            'host' => getenv('SUPABASE_DB_HOST'),
            'port' => getenv('SUPABASE_DB_PORT') ?: '5432',
            'database' => getenv('SUPABASE_DB_NAME'),
            'username' => getenv('SUPABASE_DB_USER'),
            'password' => getenv('SUPABASE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ],
    ],
    
    // Connection Pool Settings
    'pool' => [
        'min' => 2,
        'max' => 10,
        'idle_timeout' => 300,
    ],
];

