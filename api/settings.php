<?php
// File: C:\xampp\htdocs\appF\api\settings.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/middleware.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check authentication and admin role
authMiddleware();

if ($_SESSION['user_role'] !== 'admin') {
    jsonResponse(['error' => 'Admin access required'], 403);
}

$db = getDB();

switch ($method) {
    case 'GET':
        $settingKey = isset($_GET['key']) ? sanitize($_GET['key']) : null;
        
        if ($settingKey) {
            $stmt = $db->prepare("SELECT setting_key, setting_value, setting_type FROM site_settings WHERE setting_key = ?");
            $stmt->bind_param("s", $settingKey);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result) {
                jsonResponse(['success' => true, 'setting' => $result]);
            } else {
                jsonResponse(['error' => 'Setting not found'], 404);
            }
        } else {
            $result = $db->query("SELECT setting_key, setting_value, setting_type FROM site_settings");
            $settings = $result->fetch_all(MYSQLI_ASSOC);
            
            $settingsMap = [];
            foreach ($settings as $setting) {
                $settingsMap[$setting['setting_key']] = $setting;
            }
            
            jsonResponse(['success' => true, 'settings' => $settingsMap]);
        }
        break;
        
    case 'POST':
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['settings']) && is_array($data['settings'])) {
            // Bulk update
            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, setting_type) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            
            foreach ($data['settings'] as $key => $value) {
                $type = $data['types'][$key] ?? 'text';
                $stmt->bind_param("sss", $key, $value, $type);
                $stmt->execute();
            }
            
            logActivity($_SESSION['user_id'], 'settings_updated', "Updated site settings");
            jsonResponse(['success' => true]);
            
        } else {
            // Single setting update
            $key = sanitize($data['key'] ?? '');
            $value = sanitize($data['value'] ?? '');
            $type = sanitize($data['type'] ?? 'text');
            
            if (empty($key)) {
                jsonResponse(['error' => 'Setting key required'], 400);
            }
            
            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, setting_type) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->bind_param("sss", $key, $value, $type);
            $stmt->execute();
            
            logActivity($_SESSION['user_id'], 'settings_updated', "Updated setting: $key");
            jsonResponse(['success' => true]);
        }
        break;
        
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
?>