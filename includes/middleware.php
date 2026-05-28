<?php
// File: C:\xampp\htdocs\appF\includes\middleware.php

require_once 'auth.php';
require_once 'roles.php';

// Middleware to check if user is authenticated
function authMiddleware() {
    // Check remember me first
    if (!isLoggedIn()) {
        checkRememberMe();
    }
    
    if (!isLoggedIn()) {
        // Store requested URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('/login.php');
    }
    
    // Check session timeout
    checkSessionTimeout();
}

// Middleware for specific roles
function roleMiddleware($allowedRoles) {
    authMiddleware();
    
    $userRole = $_SESSION['user_role'];
    
    if (!in_array($userRole, $allowedRoles)) {
        // Log unauthorized access attempt
        logActivity($_SESSION['user_id'], 'unauthorized_access', 
            "Attempted to access " . $_SERVER['REQUEST_URI']);
        
        // Redirect to appropriate dashboard
        redirect('/dashboard/' . $userRole . '.php');
    }
}

// Middleware for AJAX requests
function ajaxMiddleware() {
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        jsonResponse(['error' => 'Invalid request'], 400);
    }
    
    authMiddleware();
}

// Middleware to check if user is not logged in (for login/register pages)
function guestMiddleware() {
    if (isLoggedIn()) {
        redirect('/dashboard/' . $_SESSION['user_role'] . '.php');
    }
}

// Middleware to prevent caching of authenticated pages
function noCacheMiddleware() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

// Middleware to set security headers
function securityHeadersMiddleware() {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    
    // Content Security Policy (customize as needed)
    header("Content-Security-Policy: default-src 'self'; " .
           "script-src 'self' https://cdn.jsdelivr.net; " .
           "style-src 'self' https://cdn.jsdelivr.net; " .
           "img-src 'self' https: data:;");
}

// Middleware to validate CSRF token for POST requests
function csrfMiddleware() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!verifyCSRFToken($token)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                jsonResponse(['error' => 'Invalid CSRF token'], 403);
            } else {
                $_SESSION['error'] = 'Invalid form submission';
                redirect($_SERVER['HTTP_REFERER'] ?? '/index.php');
            }
        }
    }
}

// Middleware to rate limit API requests
function rateLimitMiddleware($limit = 60, $timeWindow = 60) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = "rate_limit_{$ip}";
    
    $requests = $_SESSION[$key] ?? ['count' => 0, 'first_request' => time()];
    
    if (time() - $requests['first_request'] > $timeWindow) {
        // Reset window
        $requests = ['count' => 1, 'first_request' => time()];
    } else {
        $requests['count']++;
    }
    
    $_SESSION[$key] = $requests;
    
    if ($requests['count'] > $limit) {
        jsonResponse(['error' => 'Rate limit exceeded. Try again later.'], 429);
    }
}

// Apply all security middlewares
function applySecurityMiddleware() {
    securityHeadersMiddleware();
    
    // Rate limiting for API endpoints
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        rateLimitMiddleware();
    }
}

// Middleware to check if user has specific permission
function permissionMiddleware($permission) {
    authMiddleware();
    
    if (!hasPermission($permission)) {
        logActivity($_SESSION['user_id'], 'permission_denied', 
            "Tried to perform: $permission");
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            jsonResponse(['error' => 'Permission denied'], 403);
        } else {
            $_SESSION['error'] = "You don't have permission to do that";
            redirect('/dashboard/' . $_SESSION['user_role'] . '.php');
        }
    }
}

// Middleware to load user data
function loadUserMiddleware() {
    authMiddleware();
    
    // Load user data into global variable
    $GLOBALS['current_user'] = getCurrentUser();
    
    // Load additional data based on role
    switch ($GLOBALS['current_user']['role']) {
        case 'coach':
            $GLOBALS['coach_clients'] = getCoachClients($_SESSION['user_id']);
            break;
        case 'editor':
            // Load editor's assigned clients
            $db = getDB();
            $stmt = $db->prepare("
                SELECT u.* 
                FROM users u
                JOIN editor_assignments ea ON u.id = ea.client_id
                WHERE ea.editor_id = ?
            ");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $GLOBALS['editor_clients'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            break;
    }
}

// Middleware wrapper for routes
function applyMiddleware($middlewares, $callback) {
    foreach ($middlewares as $middleware) {
        $middleware();
    }
    
    return $callback();
}
?>