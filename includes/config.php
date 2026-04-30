<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Change this if you have a MySQL password
define('DB_NAME', 'fitcoach_pro');

// Application configuration
define('APP_NAME', 'FitCoach Pro');
define('APP_URL', 'http://localhost/appF');
define('APP_ENV', 'development'); // development or production

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS only

// Timezone
date_default_timezone_set('America/New_York'); // Change to your timezone

// Error reporting (development only)
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
function getDB() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($db->connect_error) {
                throw new Exception("Connection failed: " . $db->connect_error);
            }
            
            $db->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Database connection error. Please try again later.");
        }
    }
    
    return $db;
}
?>