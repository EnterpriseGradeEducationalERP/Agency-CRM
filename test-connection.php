<?php
/**
 * Database Connection Test Script
 * Run this file to test your database connection
 * Access: http://localhost/Agency%20CRM/test-connection.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>OneStop CRM - Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; }
        .test-item { padding: 10px; margin: 10px 0; border-left: 4px solid #ddd; background: #f9f9f9; }
        .test-item.pass { border-left-color: #28a745; }
        .test-item.fail { border-left-color: #dc3545; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; margin-left: 10px; }
        .badge.success { background: #28a745; color: white; }
        .badge.error { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 OneStop Agency CRM - System Test</h1>
        <p><strong>Testing your XAMPP setup...</strong></p>
";

// Test 1: PHP Version
echo "<div class='test-item " . (version_compare(PHP_VERSION, '7.4.0', '>=') ? 'pass' : 'fail') . "'>";
echo "<strong>✓ Test 1: PHP Version</strong><br>";
echo "Current PHP Version: <strong>" . PHP_VERSION . "</strong>";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo " <span class='badge success'>OK</span>";
} else {
    echo " <span class='badge error'>Need 7.4+</span>";
}
echo "</div>";

// Test 2: Required Extensions
echo "<div class='test-item'>";
echo "<strong>✓ Test 2: Required PHP Extensions</strong><br>";
$required_extensions = ['mysqli', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'curl'];
$missing = [];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ $ext <span class='badge success'>Loaded</span><br>";
    } else {
        echo "✗ $ext <span class='badge error'>Missing</span><br>";
        $missing[] = $ext;
    }
}
echo "</div>";

// Test 3: Configuration Files
echo "<div class='test-item " . (file_exists('.env') ? 'pass' : 'fail') . "'>";
echo "<strong>✓ Test 3: Configuration Files</strong><br>";
if (file_exists('.env')) {
    echo "✓ .env file found <span class='badge success'>OK</span><br>";
    
    // Load .env
    $envContent = file_get_contents('.env');
    preg_match('/DB_DATABASE=(.+)/', $envContent, $dbName);
    preg_match('/DB_HOST=(.+)/', $envContent, $dbHost);
    preg_match('/DB_USERNAME=(.+)/', $envContent, $dbUser);
    
    echo "Database Name: <strong>" . (isset($dbName[1]) ? trim($dbName[1]) : 'Not set') . "</strong><br>";
    echo "Database Host: <strong>" . (isset($dbHost[1]) ? trim($dbHost[1]) : 'Not set') . "</strong><br>";
    echo "Database User: <strong>" . (isset($dbUser[1]) ? trim($dbUser[1]) : 'Not set') . "</strong><br>";
} else {
    echo "✗ .env file not found <span class='badge error'>Missing</span><br>";
    echo "Please copy .env.example to .env";
}
echo "</div>";

// Test 4: Database Connection
echo "<div class='test-item'>";
echo "<strong>✓ Test 4: Database Connection</strong><br>";

$dbHost = '127.0.0.1';
$dbName = 'agencycrm';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to MySQL server <span class='badge success'>OK</span><br>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbName'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Database '<strong>$dbName</strong>' exists <span class='badge success'>OK</span><br>";
        
        // Connect to specific database
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "✓ Found <strong>" . count($tables) . " tables</strong> <span class='badge success'>OK</span><br>";
            echo "<details><summary>View tables (click to expand)</summary><pre>";
            foreach ($tables as $table) {
                echo "  • $table\n";
            }
            echo "</pre></details>";
            
            // Check default admin user
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
            $adminCount = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($adminCount['count'] > 0) {
                echo "✓ Admin user found <span class='badge success'>OK</span><br>";
            } else {
                echo "✗ No admin user found <span class='badge error'>Error</span><br>";
            }
        } else {
            echo "✗ No tables found <span class='badge error'>Error</span><br>";
            echo "Please import database/schema.sql";
        }
    } else {
        echo "✗ Database '<strong>$dbName</strong>' does not exist <span class='badge error'>Error</span><br>";
        echo "Please create the database or run setup.bat";
    }
} catch (PDOException $e) {
    echo "✗ Database connection failed <span class='badge error'>Error</span><br>";
    echo "<pre>Error: " . $e->getMessage() . "</pre>";
    echo "<div class='info'>💡 <strong>Make sure:</strong><br>";
    echo "1. MySQL is running in XAMPP Control Panel<br>";
    echo "2. Database name is 'agencycrm'<br>";
    echo "3. Username is 'root' with no password</div>";
}
echo "</div>";

// Test 5: File Permissions
echo "<div class='test-item'>";
echo "<strong>✓ Test 5: Storage Folders</strong><br>";
$folders = ['storage/logs', 'storage/uploads', 'storage/cache', 'storage/backups'];
foreach ($folders as $folder) {
    if (is_dir($folder)) {
        if (is_writable($folder)) {
            echo "✓ $folder <span class='badge success'>Writable</span><br>";
        } else {
            echo "✗ $folder <span class='badge error'>Not Writable</span><br>";
        }
    } else {
        echo "✗ $folder <span class='badge error'>Missing</span><br>";
    }
}
echo "</div>";

// Test 6: Core Files
echo "<div class='test-item'>";
echo "<strong>✓ Test 6: Core Application Files</strong><br>";
$coreFiles = [
    'public/index.php',
    'core/Database.php',
    'core/Router.php',
    'core/Auth.php',
    'core/Controller.php',
    'core/Model.php'
];
foreach ($coreFiles as $file) {
    if (file_exists($file)) {
        echo "✓ $file <span class='badge success'>OK</span><br>";
    } else {
        echo "✗ $file <span class='badge error'>Missing</span><br>";
    }
}
echo "</div>";

// Final Summary
echo "<div class='info'>";
echo "<h3>📋 Setup Summary</h3>";
if (empty($missing) && isset($tables) && count($tables) > 0) {
    echo "<p class='success'>✓ All tests passed! Your system is ready to use.</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Delete this test file (test-connection.php) for security</li>";
    echo "<li>Access your CRM at: <a href='http://localhost/Agency%20CRM' target='_blank'>http://localhost/Agency%20CRM</a></li>";
    echo "<li>Login with: <strong>admin@onestopcrm.com</strong> / <strong>admin123</strong></li>";
    echo "<li>Change the default password immediately!</li>";
    echo "</ol>";
} else {
    echo "<p class='error'>✗ Some tests failed. Please review the errors above.</p>";
    echo "<p><strong>Common Solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP Apache and MySQL are running</li>";
    echo "<li>Run setup.bat to create database automatically</li>";
    echo "<li>Or manually import database/schema.sql via phpMyAdmin</li>";
    echo "<li>Check XAMPP_SETUP_GUIDE.md for detailed instructions</li>";
    echo "</ul>";
}
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "OneStop Agency CRM v2.0 | System Test Script<br>";
echo "For support, see <a href='XAMPP_SETUP_GUIDE.md'>XAMPP_SETUP_GUIDE.md</a>";
echo "</p>";

echo "</div></body></html>";
?>

