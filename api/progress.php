<?php
// File: C:\xampp\htdocs\appF\api\progress.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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

// Check authentication
authMiddleware();

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];
$db = getDB();

switch ($method) {
    case 'GET':
        $clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;
        $metric = isset($_GET['metric']) ? sanitize($_GET['metric']) : 'weight';
        $weeks = isset($_GET['weeks']) ? (int)$_GET['weeks'] : 8;
        
        // Determine which client to fetch
        if ($userRole === 'client') {
            $clientId = $userId;
        } else if ($userRole === 'coach' && $clientId) {
            $stmt = $db->prepare("SELECT id FROM coaches_clients WHERE coach_id = ? AND client_id = ?");
            $stmt->bind_param("ii", $userId, $clientId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
        } else if ($userRole === 'editor' && $clientId) {
            $stmt = $db->prepare("SELECT id FROM editor_assignments WHERE editor_id = ? AND client_id = ?");
            $stmt->bind_param("ii", $userId, $clientId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
        } else if ($userRole !== 'admin') {
            jsonResponse(['error' => 'Client ID required'], 400);
        }
        
        if ($metric === 'weight') {
            // Get weight history
            $stmt = $db->prepare("
                SELECT measurement_date as date, weight
                FROM body_measurements
                WHERE client_id = ?
                ORDER BY measurement_date DESC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $clientId, $weeks);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $data = array_reverse($data);
            
        } else if ($metric === 'measurements') {
            // Get body measurements
            $stmt = $db->prepare("
                SELECT measurement_date as date, weight, waist, chest, arms, thighs, body_fat_us_navy
                FROM body_measurements
                WHERE client_id = ?
                ORDER BY measurement_date DESC
                LIMIT 1
            ");
            $stmt->bind_param("i", $clientId);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            
        } else if ($metric === 'workouts') {
            // Get workout completion history
            $stmt = $db->prepare("
                SELECT 
                    DATE_FORMAT(start_date, '%Y-%m-%d') as date,
                    CASE WHEN status = 'completed' THEN 100 
                         WHEN status = 'in_progress' THEN 50 
                         ELSE 0 END as completion
                FROM client_workouts
                WHERE client_id = ? AND start_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY start_date DESC
            ");
            $stmt->bind_param("ii", $clientId, $weeks * 7);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
        } else {
            jsonResponse(['error' => 'Invalid metric'], 400);
        }
        
        jsonResponse(['success' => true, 'data' => $data]);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? 'measurement';
        
        if ($action === 'measurement') {
            // Add body measurement
            $clientId = (int)$data['client_id'];
            
            // Check permissions
            if ($userRole === 'client') {
                $clientId = $userId;
            } else if ($userRole === 'coach') {
                $stmt = $db->prepare("SELECT id FROM coaches_clients WHERE coach_id = ? AND client_id = ?");
                $stmt->bind_param("ii", $userId, $clientId);
                $stmt->execute();
                if ($stmt->get_result()->num_rows === 0) {
                    jsonResponse(['error' => 'Unauthorized'], 403);
                }
            } else if ($userRole !== 'admin') {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
            
            $stmt = $db->prepare("
                INSERT INTO body_measurements 
                (client_id, measurement_date, weight, waist, chest, arms, thighs, body_fat_us_navy, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $measurementDate = sanitize($data['date'] ?? date('Y-m-d'));
            $weight = (float)($data['weight'] ?? 0);
            $waist = (float)($data['waist'] ?? null);
            $chest = (float)($data['chest'] ?? null);
            $arms = (float)($data['arms'] ?? null);
            $thighs = (float)($data['thighs'] ?? null);
            $bodyFat = (float)($data['body_fat'] ?? null);
            $notes = sanitize($data['notes'] ?? '');
            
            $stmt->bind_param("isdddddds", 
                $clientId, $measurementDate, $weight, $waist, $chest, $arms, $thighs, $bodyFat, $notes
            );
            $stmt->execute();
            
            logActivity($userId, 'measurement_added', "Added measurement for client $clientId");
            jsonResponse(['success' => true, 'id' => $db->insert_id]);
            
        } else if ($action === 'fitness_test') {
            // Add fitness test result
            $clientId = (int)$data['client_id'];
            
            if ($userRole === 'client') {
                $clientId = $userId;
            } else if ($userRole === 'coach') {
                $stmt = $db->prepare("SELECT id FROM coaches_clients WHERE coach_id = ? AND client_id = ?");
                $stmt->bind_param("ii", $userId, $clientId);
                $stmt->execute();
                if ($stmt->get_result()->num_rows === 0) {
                    jsonResponse(['error' => 'Unauthorized'], 403);
                }
            } else if ($userRole !== 'admin') {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
            
            $stmt = $db->prepare("
                INSERT INTO fitness_tests (client_id, test_type, test_date, result, unit, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $testType = sanitize($data['test_type']);
            $testDate = sanitize($data['date'] ?? date('Y-m-d'));
            $result = (float)$data['result'];
            $unit = sanitize($data['unit'] ?? '');
            $notes = sanitize($data['notes'] ?? '');
            
            $stmt->bind_param("issdss", $clientId, $testType, $testDate, $result, $unit, $notes);
            $stmt->execute();
            
            logActivity($userId, 'fitness_test_added', "Added fitness test for client $clientId");
            jsonResponse(['success' => true, 'id' => $db->insert_id]);
        }
        break;
        
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
?>