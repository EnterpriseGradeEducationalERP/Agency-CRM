<?php
/**
 * Supabase Configuration
 * For authentication and storage
 */

return [
    'url' => getenv('SUPABASE_URL') ?: '',
    'anon_key' => getenv('SUPABASE_ANON_KEY') ?: '',
    'service_role_key' => getenv('SUPABASE_SERVICE_ROLE_KEY') ?: '',
    
    // Authentication
    'auth' => [
        'auto_refresh_token' => true,
        'persist_session' => true,
        'detect_session_in_url' => true,
        'storage_key' => 'supabase.auth.token',
    ],
    
    // Storage
    'storage' => [
        'bucket_name' => 'onestop-crm',
        'public_url' => getenv('SUPABASE_STORAGE_URL') ?: '',
    ],
    
    // OAuth Providers
    'oauth_providers' => [
        'google' => true,
        'github' => false,
        'microsoft' => false,
    ],
];

