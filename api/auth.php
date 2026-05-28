<?php
// File: C:\xampp\htdocs\appF\api\auth.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/validation.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request action
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        if ($method !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $email = sanitize($data['email'] ?? '');
        $password = $data['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            jsonResponse(['error' => 'Email and password required'], 400);
        }
        
        if (!validateEmail($email)) {
            jsonResponse(['error' => 'Invalid email format'], 400);
        }
        
        $result = loginUser($email, $password, $data['remember'] ?? false);
        
        if ($result['success']) {
            jsonResponse([
                'success' => true,
                'role' => $result['role'],
                'redirect' => getRoleRedirect($result['role'])
            ]);
        } else {
            jsonResponse(['error' => $result['error']], 401);
        }
        break;
        
    case 'logout':
        if ($method !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        logoutUser();
        jsonResponse(['success' => true]);
        break;
        
    case 'register':
        if ($method !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $result = registerUser([
            'name' => sanitize($data['name'] ?? ''),
            'email' => sanitize($data['email'] ?? ''),
            'password' => $data['password'] ?? '',
            'role' => 'client'
        ]);
        
        if ($result['success']) {
            jsonResponse(['success' => true, 'user_id' => $result['user_id']]);
        } else {
            jsonResponse(['error' => $result['error'], 'errors' => $result['errors'] ?? null], 400);
        }
        break;
        
    case 'check':
        if ($method !== 'GET') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        if (isLoggedIn()) {
            $user = getCurrentUser();
            jsonResponse([
                'logged_in' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            jsonResponse(['logged_in' => false]);
        }
        break;
        
    case 'change-password':
        if ($method !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        if (!isLoggedIn()) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $oldPassword = $data['old_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        
        $result = changePassword($_SESSION['user_id'], $oldPassword, $newPassword);
        
        if ($result['success']) {
            jsonResponse(['success' => true]);
        } else {
            jsonResponse(['error' => $result['error'], 'errors' => $result['errors'] ?? null], 400);
        }
        break;
        
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
?>