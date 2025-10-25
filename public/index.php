<?php
/**
 * OneStop Agency CRM - Entry Point
 * Version: 2.0
 * 
 * This is the main entry point for the application
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define path constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Load configuration
require_once CONFIG_PATH . '/constants.php';
$appConfig = require CONFIG_PATH . '/app.php';

// Set timezone
date_default_timezone_set($appConfig['timezone']);

// Auto-loader for core and app classes
spl_autoload_register(function ($className) {
    $paths = [
        CORE_PATH . '/' . $className . '.php',
        APP_PATH . '/controllers/' . $className . '.php',
        APP_PATH . '/models/' . $className . '.php',
        APP_PATH . '/middleware/' . $className . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Create storage directories if they don't exist
$directories = [
    STORAGE_PATH . '/logs',
    STORAGE_PATH . '/uploads',
    STORAGE_PATH . '/cache',
    STORAGE_PATH . '/backups',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS headers for API requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Create router instance
$router = new Router();

// Global middleware
$router->middleware('AuthMiddleware');

// ============================================================
// ROUTES DEFINITION
// ============================================================

// -------------------- Authentication Routes --------------------
$router->post('/api/v1/auth/login', 'AuthController@login');
$router->post('/api/v1/auth/register', 'AuthController@register');
$router->post('/api/v1/auth/logout', 'AuthController@logout');
$router->post('/api/v1/auth/refresh', 'AuthController@refresh');
$router->post('/api/v1/auth/forgot-password', 'AuthController@forgotPassword');
$router->post('/api/v1/auth/reset-password', 'AuthController@resetPassword');
$router->get('/api/v1/auth/me', 'AuthController@me');

// -------------------- User Routes --------------------
$router->get('/api/v1/users', 'UserController@index');
$router->get('/api/v1/users/{id}', 'UserController@show');
$router->post('/api/v1/users', 'UserController@create');
$router->put('/api/v1/users/{id}', 'UserController@update');
$router->delete('/api/v1/users/{id}', 'UserController@delete');

// -------------------- Client Routes --------------------
$router->get('/api/v1/clients', 'ClientController@index');
$router->get('/api/v1/clients/{id}', 'ClientController@show');
$router->post('/api/v1/clients', 'ClientController@create');
$router->put('/api/v1/clients/{id}', 'ClientController@update');
$router->delete('/api/v1/clients/{id}', 'ClientController@delete');

// -------------------- Deal/Pipeline Routes --------------------
$router->get('/api/v1/deals', 'DealController@index');
$router->get('/api/v1/deals/{id}', 'DealController@show');
$router->post('/api/v1/deals', 'DealController@create');
$router->put('/api/v1/deals/{id}', 'DealController@update');
$router->put('/api/v1/deals/{id}/stage', 'DealController@updateStage');
$router->delete('/api/v1/deals/{id}', 'DealController@delete');
$router->get('/api/v1/pipeline', 'DealController@pipeline');

// -------------------- Service Routes --------------------
$router->get('/api/v1/services', 'ServiceController@index');
$router->get('/api/v1/services/{id}', 'ServiceController@show');
$router->post('/api/v1/services', 'ServiceController@create');
$router->put('/api/v1/services/{id}', 'ServiceController@update');
$router->delete('/api/v1/services/{id}', 'ServiceController@delete');

// -------------------- Role Routes --------------------
$router->get('/api/v1/roles', 'RoleController@index');
$router->get('/api/v1/roles/{id}', 'RoleController@show');
$router->post('/api/v1/roles', 'RoleController@create');
$router->put('/api/v1/roles/{id}', 'RoleController@update');
$router->delete('/api/v1/roles/{id}', 'RoleController@delete');

// -------------------- Quote Routes --------------------
$router->get('/api/v1/quotes', 'QuoteController@index');
$router->get('/api/v1/quotes/{id}', 'QuoteController@show');
$router->post('/api/v1/quotes', 'QuoteController@create');
$router->post('/api/v1/quotes/calculate', 'QuoteController@calculate');
$router->put('/api/v1/quotes/{id}', 'QuoteController@update');
$router->delete('/api/v1/quotes/{id}', 'QuoteController@delete');
$router->get('/api/v1/quotes/{id}/pdf', 'QuoteController@generatePdf');

// -------------------- Project Routes --------------------
$router->get('/api/v1/projects', 'ProjectController@index');
$router->get('/api/v1/projects/{id}', 'ProjectController@show');
$router->post('/api/v1/projects', 'ProjectController@create');
$router->put('/api/v1/projects/{id}', 'ProjectController@update');
$router->delete('/api/v1/projects/{id}', 'ProjectController@delete');
$router->post('/api/v1/projects/{id}/team', 'ProjectController@addTeamMember');
$router->delete('/api/v1/projects/{id}/team/{userId}', 'ProjectController@removeTeamMember');

// -------------------- Task Routes --------------------
$router->get('/api/v1/tasks', 'TaskController@index');
$router->get('/api/v1/projects/{projectId}/tasks', 'TaskController@projectTasks');
$router->get('/api/v1/tasks/{id}', 'TaskController@show');
$router->post('/api/v1/tasks', 'TaskController@create');
$router->put('/api/v1/tasks/{id}', 'TaskController@update');
$router->delete('/api/v1/tasks/{id}', 'TaskController@delete');
$router->post('/api/v1/tasks/{id}/comments', 'TaskController@addComment');

// -------------------- Time Tracking Routes --------------------
$router->get('/api/v1/time-logs', 'TimeLogController@index');
$router->get('/api/v1/time-logs/{id}', 'TimeLogController@show');
$router->post('/api/v1/time-logs/start', 'TimeLogController@start');
$router->put('/api/v1/time-logs/{id}/stop', 'TimeLogController@stop');
$router->post('/api/v1/time-logs/manual', 'TimeLogController@manual');
$router->delete('/api/v1/time-logs/{id}', 'TimeLogController@delete');

// -------------------- Invoice Routes --------------------
$router->get('/api/v1/invoices', 'InvoiceController@index');
$router->get('/api/v1/invoices/{id}', 'InvoiceController@show');
$router->post('/api/v1/invoices', 'InvoiceController@create');
$router->put('/api/v1/invoices/{id}', 'InvoiceController@update');
$router->delete('/api/v1/invoices/{id}', 'InvoiceController@delete');
$router->get('/api/v1/invoices/{id}/pdf', 'InvoiceController@generatePdf');

// -------------------- Payment Routes --------------------
$router->get('/api/v1/payments', 'PaymentController@index');
$router->get('/api/v1/payments/{id}', 'PaymentController@show');
$router->post('/api/v1/payments', 'PaymentController@create');
$router->post('/api/v1/payments/razorpay/callback', 'PaymentController@razorpayCallback');
$router->post('/api/v1/payments/stripe/callback', 'PaymentController@stripeCallback');

// -------------------- Dashboard Routes --------------------
$router->get('/api/v1/dashboard/admin', 'DashboardController@admin');
$router->get('/api/v1/dashboard/pm', 'DashboardController@projectManager');
$router->get('/api/v1/dashboard/sales', 'DashboardController@sales');
$router->get('/api/v1/dashboard/team', 'DashboardController@team');

// -------------------- Notification Routes --------------------
$router->get('/api/v1/notifications', 'NotificationController@index');
$router->put('/api/v1/notifications/{id}/read', 'NotificationController@markAsRead');
$router->put('/api/v1/notifications/read-all', 'NotificationController@markAllAsRead');
$router->delete('/api/v1/notifications/{id}', 'NotificationController@delete');

// -------------------- AI Routes --------------------
$router->post('/api/v1/ai/pricing-suggestion', 'AIController@pricingSuggestion');
$router->post('/api/v1/ai/resource-allocation', 'AIController@resourceAllocation');
$router->post('/api/v1/ai/deal-score', 'AIController@dealScore');
$router->get('/api/v1/ai/insights', 'AIController@insights');

// -------------------- Settings Routes --------------------
$router->get('/api/v1/settings', 'SettingsController@index');
$router->get('/api/v1/settings/{key}', 'SettingsController@show');
$router->put('/api/v1/settings/{key}', 'SettingsController@update');

// -------------------- Report Routes --------------------
$router->get('/api/v1/reports/financial', 'ReportController@financial');
$router->get('/api/v1/reports/productivity', 'ReportController@productivity');
$router->get('/api/v1/reports/utilization', 'ReportController@utilization');
$router->post('/api/v1/reports/export', 'ReportController@export');

// -------------------- File Upload Routes --------------------
$router->post('/api/v1/files/upload', 'FileController@upload');
$router->get('/api/v1/files/{id}', 'FileController@download');
$router->delete('/api/v1/files/{id}', 'FileController@delete');

// -------------------- Frontend Routes (Serve HTML) --------------------
$router->get('/', function() {
    require_once APP_PATH . '/views/index.html';
});

$router->get('/login', function() {
    require_once APP_PATH . '/views/auth/login.html';
});

$router->get('/dashboard', function() {
    require_once APP_PATH . '/views/dashboard.html';
});

// ============================================================
// DISPATCH THE REQUEST
// ============================================================
try {
    $router->dispatch();
} catch (Exception $e) {
    // Log error
    $logFile = STORAGE_PATH . '/logs/error_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    error_log("[{$timestamp}] ERROR: {$e->getMessage()}\n", 3, $logFile);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $appConfig['environment'] === 'development' ? $e->getMessage() : null
    ]);
}

