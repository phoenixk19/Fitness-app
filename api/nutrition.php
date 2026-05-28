<?php
// File: C:\xampp\htdocs\appF\api\nutrition.php

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
        $date = isset($_GET['date']) ? sanitize($_GET['date']) : date('Y-m-d');
        
        // Determine which client to fetch
        if ($userRole === 'client') {
            $clientId = $userId;
        } else if ($userRole === 'coach' && $clientId) {
            // Check if client belongs to coach
            $stmt = $db->prepare("SELECT id FROM coaches_clients WHERE coach_id = ? AND client_id = ?");
            $stmt->bind_param("ii", $userId, $clientId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
        } else if ($userRole === 'editor' && $clientId) {
            // Check if client is assigned to editor
            $stmt = $db->prepare("SELECT id FROM editor_assignments WHERE editor_id = ? AND client_id = ?");
            $stmt->bind_param("ii", $userId, $clientId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
        } else if ($userRole !== 'admin') {
            jsonResponse(['error' => 'Client ID required'], 400);
        }
        
        // Get meal logs for the date
        $stmt = $db->prepare("
            SELECT ml.*, 
                   (SELECT COUNT(*) FROM meal_items WHERE meal_log_id = ml.id) as item_count
            FROM meal_logs ml
            WHERE ml.client_id = ? AND ml.meal_date = ?
            ORDER BY ml.meal_type
        ");
        $stmt->bind_param("is", $clientId, $date);
        $stmt->execute();
        $meals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get daily totals
        $stmt = $db->prepare("
            SELECT 
                COALESCE(SUM(total_calories), 0) as total_calories,
                COALESCE(SUM(total_protein), 0) as total_protein,
                COALESCE(SUM(total_carbs), 0) as total_carbs,
                COALESCE(SUM(total_fat), 0) as total_fat
            FROM meal_logs
            WHERE client_id = ? AND meal_date = ?
        ");
        $stmt->bind_param("is", $clientId, $date);
        $stmt->execute();
        $totals = $stmt->get_result()->fetch_assoc();
        
        jsonResponse([
            'date' => $date,
            'meals' => $meals,
            'totals' => $totals
        ]);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $clientId = (int)$data['client_id'];
        $mealType = sanitize($data['meal_type']);
        $mealDate = sanitize($data['meal_date'] ?? date('Y-m-d'));
        
        // Validate permissions
        if ($userRole === 'client') {
            $clientId = $userId;
        } else if ($userRole === 'coach') {
            $stmt = $db->prepare("SELECT id FROM coaches_clients WHERE coach_id = ? AND client_id = ?");
            $stmt->bind_param("ii", $userId, $clientId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
        } else if ($userRole === 'editor') {
            $stmt = $db->prepare("SELECT id FROM editor_assignments WHERE editor_id = ? AND client_id = ?");
            $stmt->bind_param("ii", $userId, $clientId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
        } else if ($userRole !== 'admin') {
            jsonResponse(['error' => 'Unauthorized'], 403);
        }
        
        // Validate meal type
        $validMeals = ['breakfast', 'lunch', 'dinner', 'snack'];
        if (!in_array($mealType, $validMeals)) {
            jsonResponse(['error' => 'Invalid meal type'], 400);
        }
        
        // Insert meal log
        $stmt = $db->prepare("
            INSERT INTO meal_logs (client_id, meal_type, meal_date, total_calories, total_protein, total_carbs, total_fat)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $totalCalories = (float)($data['total_calories'] ?? 0);
        $totalProtein = (float)($data['total_protein'] ?? 0);
        $totalCarbs = (float)($data['total_carbs'] ?? 0);
        $totalFat = (float)($data['total_fat'] ?? 0);
        
        $stmt->bind_param("issdddd", $clientId, $mealType, $mealDate, $totalCalories, $totalProtein, $totalCarbs, $totalFat);
        $stmt->execute();
        $mealId = $db->insert_id;
        
        // Insert food items if provided
        if (isset($data['items']) && is_array($data['items'])) {
            $itemStmt = $db->prepare("
                INSERT INTO meal_items (meal_log_id, custom_food_name, quantity_grams, calories, protein, carbs, fat)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($data['items'] as $item) {
                $foodName = sanitize($item['name']);
                $quantity = (float)$item['quantity'];
                $calories = (float)($item['calories'] ?? 0);
                $protein = (float)($item['protein'] ?? 0);
                $carbs = (float)($item['carbs'] ?? 0);
                $fat = (float)($item['fat'] ?? 0);
                
                $itemStmt->bind_param("isddddd", $mealId, $foodName, $quantity, $calories, $protein, $carbs, $fat);
                $itemStmt->execute();
            }
        }
        
        logActivity($userId, 'meal_logged', "Logged meal for client $clientId: $mealType");
        jsonResponse(['success' => true, 'meal_id' => $mealId]);
        break;
        
    case 'DELETE':
        if (!$workoutId) {
            jsonResponse(['error' => 'Meal ID required'], 400);
        }
        
        $mealId = (int)$_GET['id'];
        
        // Check permissions
        $stmt = $db->prepare("
            SELECT client_id FROM meal_logs WHERE id = ?
        ");
        $stmt->bind_param("i", $mealId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            jsonResponse(['error' => 'Meal not found'], 404);
        }
        
        $clientId = $result['client_id'];
        
        if ($userRole === 'client' && $clientId != $userId) {
            jsonResponse(['error' => 'Unauthorized'], 403);
        } else if ($userRole === 'coach') {
            $stmt = $db->prepare("SELECT id FROM coaches_clients WHERE coach_id = ? AND client_id = ?");
            $stmt->bind_param("ii", $userId, $clientId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
        } else if ($userRole === 'editor') {
            $stmt = $db->prepare("SELECT id FROM editor_assignments WHERE editor_id = ? AND client_id = ?");
            $stmt->bind_param("ii", $userId, $clientId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
        } else if ($userRole !== 'admin') {
            jsonResponse(['error' => 'Unauthorized'], 403);
        }
        
        // Delete meal log
        $stmt = $db->prepare("DELETE FROM meal_logs WHERE id = ?");
        $stmt->bind_param("i", $mealId);
        $stmt->execute();
        
        logActivity($userId, 'meal_deleted', "Deleted meal $mealId for client $clientId");
        jsonResponse(['success' => true]);
        break;
        
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
?>