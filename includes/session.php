<?php
// Start secure session
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
    
    // Check user agent for extra security
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    } else if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        // Potential session hijacking
        session_destroy();
        redirect('/login.php?error=session_expired');
    }
}

// Set user session after login
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
}

// Clear user session (logout)
function clearUserSession() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

// Check session timeout
function checkSessionTimeout($maxLifetime = 3600) {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > $maxLifetime) {
            clearUserSession();
            redirect('/login.php?error=timeout');
        }
    }
}

// Get session data safely
function getSessionData($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

// Set session data
function setSessionData($key, $value) {
    $_SESSION[$key] = $value;
}
?>