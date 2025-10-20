<?php
/**
 * Database Configuration - NO ERRORS VERSION
 * Save as: config/database.php
 */

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        echo "ERROR: .env file not found at: " . htmlspecialchars($path) . "<br>";
        echo "Please copy .env.example to .env and configure it.";
        exit(1);
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            
            putenv("$key=$value");
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}

// Load .env file
$envPath = __DIR__ . '/../.env';
loadEnv($envPath);

// Define constants with defaults if not set
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'blog_application');
if (!defined('APP_NAME')) define('APP_NAME', 'My Blog');
if (!defined('SITE_NAME')) define('SITE_NAME', 'My Blog');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/blog-app');
if (!defined('APP_ENV')) define('APP_ENV', 'development');
if (!defined('APP_DEBUG')) define('APP_DEBUG', 'true');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', '3600');
if (!defined('HASH_COST')) define('HASH_COST', '12');
if (!defined('APP_TIMEZONE')) define('APP_TIMEZONE', 'Asia/Colombo');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Error reporting based on environment
// Convert APP_DEBUG to boolean safely
$appDebug = filter_var(APP_DEBUG, FILTER_VALIDATE_BOOLEAN);

if ($appDebug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}


/**
 * Get database connection
 */
function getDB() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $conn = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch(PDOException $e) {
            $errorMsg = "<h2>Database Connection Failed</h2>";
            $errorMsg .= "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            $errorMsg .= "<h3>Troubleshooting Steps:</h3>";
            $errorMsg .= "<ol>";
            $errorMsg .= "<li>Make sure XAMPP MySQL is running</li>";
            $errorMsg .= "<li>Check if database '<strong>" . DB_NAME . "</strong>' exists</li>";
            $errorMsg .= "<li>Verify .env file settings</li>";
            $errorMsg .= "<li>Import database_setup.sql in phpMyAdmin</li>";
            $errorMsg .= "</ol>";
            echo $errorMsg;
            exit(1);
        }
    }
    
    return $conn;
}
?>