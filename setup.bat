@echo off
echo ========================================
echo OneStop Agency CRM - XAMPP Setup Script
echo ========================================
echo.

REM Check if running in correct directory
if not exist "public\index.php" (
    echo ERROR: Please run this script from the root directory of Agency CRM
    echo Current directory: %CD%
    pause
    exit /b 1
)

echo [1/5] Checking XAMPP installation...
if not exist "C:\xampp\mysql\bin\mysql.exe" (
    echo ERROR: XAMPP not found at C:\xampp
    echo Please install XAMPP first or update the path in this script
    pause
    exit /b 1
)
echo     ✓ XAMPP found

echo.
echo [2/5] Checking if MySQL is running...
netstat -an | find ":3306" >nul
if errorlevel 1 (
    echo     ✗ MySQL is not running
    echo     Please start MySQL from XAMPP Control Panel
    pause
    exit /b 1
)
echo     ✓ MySQL is running

echo.
echo [3/5] Creating database 'agencycrm'...
C:\xampp\mysql\bin\mysql -u root -e "CREATE DATABASE IF NOT EXISTS agencycrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
if errorlevel 1 (
    echo     Note: Database might already exist or there was an error
) else (
    echo     ✓ Database created
)

echo.
echo [4/5] Importing database schema...
C:\xampp\mysql\bin\mysql -u root agencycrm < database\schema.sql
if errorlevel 1 (
    echo     ✗ Error importing schema
    echo     Please check if database\schema.sql exists
    pause
    exit /b 1
)
echo     ✓ Schema imported successfully

echo.
echo [5/5] Setting up storage folders...
if not exist "storage\logs" mkdir storage\logs
if not exist "storage\uploads" mkdir storage\uploads
if not exist "storage\cache" mkdir storage\cache
if not exist "storage\backups" mkdir storage\backups
echo     ✓ Storage folders created

echo.
echo ========================================
echo Setup Complete! 🎉
echo ========================================
echo.
echo Your OneStop Agency CRM is ready!
echo.
echo Access URL: http://localhost/Agency CRM
echo phpMyAdmin: http://localhost/phpmyadmin
echo Database: agencycrm
echo.
echo Default Login:
echo Email: admin@onestopcrm.com
echo Password: admin123
echo.
echo IMPORTANT: Change the default password after first login!
echo.
echo Next Steps:
echo 1. Make sure Apache is running in XAMPP Control Panel
echo 2. Open: http://localhost/Agency CRM
echo 3. Login with the credentials above
echo 4. Enjoy! 🚀
echo.
echo For detailed instructions, see: XAMPP_SETUP_GUIDE.md
echo For API documentation, see: docs\API_DOCUMENTATION.md
echo.
pause

