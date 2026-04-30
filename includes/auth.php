<?php
// File: C:\xampp\htdocs\appF\includes\auth.php

require_once 'config.php';
require_once 'functions.php';
require_once 'session.php';
require_once 'validation.php';

// Login user
function loginUser($email, $password, $remember = false) {
    $db = getDB();
    
    // Prepare statement to prevent SQL injection
    $stmt = $db->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ? AND status = 'active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Set session
            setUserSession($user);
            
            // Handle remember me
            if ($remember) {
                setRememberMeCookie($user['id']);
            }
            
            // Log login activity
            logActivity($user['id'], 'login', 'User logged in successfully');
            
            return ['success' => true, 'role' => $user['role']];
        }
    }
    
    // Log failed attempt
    logActivity(0, 'login_failed', "Failed login attempt for email: $email");
    
    return ['success' => false, 'error' => 'Invalid email or password'];
}

// Register new user
function registerUser($data) {
    $db = getDB();
    
    // Validate required fields
    $required = ['name', 'email', 'password', 'role'];
    $validation = validateRequired($data, $required);
    
    if ($validation !== true) {
        return ['success' => false, 'errors' => $validation];
    }
    
    // Validate email
    if (!validateEmail($data['email'])) {
        return ['success' => false, 'error' => 'Invalid email format'];
    }
    
    // Validate password strength
    $passwordCheck = validatePassword($data['password']);
    if ($passwordCheck !== true) {
        return ['success' => false, 'errors' => $passwordCheck];
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'error' => 'Email already registered'];
    }
    
    // Hash password
    $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
    
    // Insert user
    $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
    $stmt->bind_param("ssss", $data['name'], $data['email'], $passwordHash, $data['role']);
    
    if ($stmt->execute()) {
        $userId = $db->insert_id;
        logActivity($userId, 'register', 'New user registered');
        return ['success' => true, 'user_id' => $userId];
    }
    
    return ['success' => false, 'error' => 'Registration failed'];
}

// Set remember me cookie
function setRememberMeCookie($userId) {
    $token = bin2hex(random_bytes(32));
    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))");
    $stmt->bind_param("isi", $userId, $token, $expiry);
    $stmt->execute();
    
    setcookie('remember_token', $token, $expiry, '/', '', false, true);
}

// Check remember me cookie
function checkRememberMe() {
    if (isset($_COOKIE['remember_token'])) {
        $db = getDB();
        $stmt = $db->prepare("SELECT user_id FROM user_tokens WHERE token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $_COOKIE['remember_token']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Get user data
            $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
            $stmt->bind_param("i", $row['user_id']);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            setUserSession($user);
            return true;
        }
    }
    return false;
}

// Logout user
function logoutUser() {
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'logout', 'User logged out');
    }
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM user_tokens WHERE token = ?");
        $stmt->bind_param("s", $_COOKIE['remember_token']);
        $stmt->execute();
        
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    clearUserSession();
}

// Check if user has permission
function checkPermission($requiredRole) {
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
    
    if (!hasRole($requiredRole)) {
        redirect('/dashboard/' . $_SESSION['user_role'] . '.php?error=unauthorized');
    }
}

// Change password
function changePassword($userId, $oldPassword, $newPassword) {
    $db = getDB();
    
    // Verify old password
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!password_verify($oldPassword, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Current password is incorrect'];
    }
    
    // Validate new password
    $passwordCheck = validatePassword($newPassword);
    if ($passwordCheck !== true) {
        return ['success' => false, 'errors' => $passwordCheck];
    }
    
    // Update password
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->bind_param("si", $newHash, $userId);
    
    if ($stmt->execute()) {
        logActivity($userId, 'password_change', 'Password changed');
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => 'Password change failed'];
}

// Request password reset
function requestPasswordReset($email) {
    $db = getDB();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Don't reveal that email doesn't exist for security
        return ['success' => true];
    }
    
    $user = $result->fetch_assoc();
    $token = bin2hex(random_bytes(32));
    $expiry = time() + (24 * 60 * 60); // 24 hours
    
    // Store reset token
    $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))");
    $stmt->bind_param("isi", $user['id'], $token, $expiry);
    $stmt->execute();
    
    // Send email (you'll need to implement email sending)
    $resetLink = APP_URL . "/reset-password.php?token=" . $token;
    // sendEmail($email, "Password Reset", "Click here to reset your password: " . $resetLink);
    
    logActivity($user['id'], 'password_reset_request', 'Password reset requested');
    
    return ['success' => true];
}

// Reset password with token
function resetPassword($token, $newPassword) {
    $db = getDB();
    
    // Validate token
    $stmt = $db->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'error' => 'Invalid or expired token'];
    }
    
    $reset = $result->fetch_assoc();
    
    // Validate new password
    $passwordCheck = validatePassword($newPassword);
    if ($passwordCheck !== true) {
        return ['success' => false, 'errors' => $passwordCheck];
    }
    
    // Update password
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->bind_param("si", $newHash, $reset['user_id']);
    $stmt->execute();
    
    // Mark token as used
    $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    logActivity($reset['user_id'], 'password_reset', 'Password reset completed');
    
    return ['success' => true];
}
?>