<?php
// File: D:\Program Files\xampp\htdocs\appF\includes\functions.php

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Calculate percentage
function percentage($value, $total) {
    if ($total == 0) return 0;
    return round(($value / $total) * 100);
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Upload file
function uploadFile($file, $targetDir, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']) {
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
        return ['success' => false, 'error' => 'File too large'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $targetPath = $targetDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'error' => 'Upload failed'];
}

// Send JSON response (for API)
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Get user role redirect URL
function getRoleRedirect($role) {
    $redirects = [
        'admin' => '/appF/dashboard/admin/index.php',
        'coach' => '/appF/dashboard/coach/index.php',
        'editor' => '/appF/dashboard/editor/index.php',
        'client' => '/appF/dashboard/client/index.php'
    ];
    
    return $redirects[$role] ?? '/appF/index.php';
}

// Check role permission
function hasRole($requiredRole) {
    if (!isLoggedIn()) return false;
    
    $user = getCurrentUser();
    $roles = [
        'admin' => 4,
        'coach' => 3,
        'editor' => 2,
        'client' => 1
    ];
    
    return $roles[$user['role']] >= $roles[$requiredRole];
}

// Log activity (for audit) - FIXED VERSION
function logActivity($userId, $action, $details = null) {
    $db = getDB();
    
    // Only log if userId exists (not 0 or null)
    if (empty($userId) || $userId == 0) {
        // For failed logins, we can still log but with NULL user_id
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (NULL, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt->bind_param("sss", $action, $details, $ip);
        $stmt->execute();
        return;
    }
    
    // Check if user exists before inserting
    $checkStmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt->bind_param("isss", $userId, $action, $details, $ip);
        $stmt->execute();
    } else {
        // User doesn't exist, log with NULL
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (NULL, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt->bind_param("sss", $action, $details, $ip);
        $stmt->execute();
    }
}

// Display flash message
function flashMessage($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>