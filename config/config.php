<?php
/**
 * Application Configuration Loader (Single Source of Truth)
 * Save as: config/config.php
 * This file reads the .env file, defines PHP constants, and starts the session.
 */

// 1. Define the path to the environment file
$envFile = __DIR__ . '/../.env';

// Check if the .env file exists and fail gracefully
if (!file_exists($envFile)) {
    http_response_code(500);
    die("<h1>Configuration Error</h1><p>The .env file is missing from the project root. Please create it.</p>");
}

// 2. Load and parse the .env file
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    http_response_code(500);
    die("<h1>Configuration Error</h1><p>Could not read the .env file.</p>");
}

// --- CORE ENVIRONMENT LOADING LOGIC ---
foreach ($lines as $line) {
    // Ignore comments and empty lines
    if (str_starts_with(trim($line), '#') || empty(trim($line))) {
        continue;
    }

    // Split the line into key and value at the first equals sign
    $parts = explode('=', $line, 2);

    if (count($parts) === 2) {
        $key = trim($parts[0]);
        $value = trim($parts[1]);

        // Remove quotes from value (e.g., APP_NAME="My Blog")
        $value = trim($value, '"\'');

        // Set environment variable and PHP constant
        if (!empty($key) && !defined($key)) {
            putenv("{$key}={$value}");
            define($key, $value);
        }
    }
}
// --- END CORE ENVIRONMENT LOADING LOGIC ---


// 3. Set mandatory PHP settings and defaults
if (!defined('APP_TIMEZONE')) define('APP_TIMEZONE', 'Asia/Colombo');
if (!defined('APP_DEBUG')) define('APP_DEBUG', 'true');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', '3600'); // Default to 1 hour

date_default_timezone_set(APP_TIMEZONE);

// Error reporting based on environment
if (APP_DEBUG === 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// 4. Start Session
if (defined('SESSION_NAME')) {
    session_name(SESSION_NAME);
}
if (defined('SESSION_LIFETIME')) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
