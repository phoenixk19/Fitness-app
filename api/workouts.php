<?php
// File: C:\xampp\htdocs\appF\api\workouts.php

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

// Check authentication for all requests
authMiddleware();

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];
$db = getDB();

// Get workout ID from URL if exists
$workoutId = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    case 'GET':
        if ($workoutId) {
            // Get single workout
            if ($userRole === 'client') {
                $stmt = $db->prepare("
                    SELECT cw.*, wt.name as template_name, wt.training_type
                    FROM client_workouts cw
                    LEFT JOIN workout_templates wt ON cw.template_id = wt.id
                    WHERE cw.id = ? AND cw.client_id = ?
                ");
                $stmt->bind_param("ii", $workoutId, $userId);
            } else if ($userRole === 'coach') {
                $stmt = $db->prepare("
                    SELECT cw.*, wt.name as template_name, u.name as client_name
                    FROM client_workouts cw
                    LEFT JOIN workout_templates wt ON cw.template_id = wt.id
                    JOIN users u ON cw.client_id = u.id
                    WHERE cw.id = ? AND cw.client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = ?)
                ");
                $stmt->bind_param("ii", $workoutId, $userId);
            } else if ($userRole === 'admin') {
                $stmt = $db->prepare("
                    SELECT cw.*, wt.name as template_name, u.name as client_name
                    FROM client_workouts cw
                    LEFT JOIN workout_templates wt ON cw.template_id = wt.id
                    JOIN users u ON cw.client_id = u.id
                    WHERE cw.id = ?
                ");
                $stmt->bind_param("i", $workoutId);
            } else {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
            
            $stmt->execute();
            $workout = $stmt->get_result()->fetch_assoc();
            
            if (!$workout) {
                jsonResponse(['error' => 'Workout not found'], 404);
            }
            
            // Get exercises for this workout
            $stmt = $db->prepare("
                SELECT cwe.*, e.name, e.muscle_group, e.equipment
                FROM client_workout_exercises cwe
                JOIN exercises e ON cwe.exercise_id = e.id
                WHERE cwe.client_workout_id = ?
                ORDER BY cwe.set_number
            ");
            $stmt->bind_param("i", $workoutId);
            $stmt->execute();
            $exercises = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            $workout['exercises'] = $exercises;
            jsonResponse($workout);
            
        } else {
            // Get workouts list
            $clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;
            $status = isset($_GET['status']) ? sanitize($_GET['status']) : null;
            
            $query = "
                SELECT cw.*, wt.name as template_name
                FROM client_workouts cw
                LEFT JOIN workout_templates wt ON cw.template_id = wt.id
                WHERE 1=1
            ";
            $params = [];
            $types = "";
            
            if ($userRole === 'client') {
                $query .= " AND cw.client_id = ?";
                $params[] = $userId;
                $types .= "i";
            } else if ($userRole === 'coach' && $clientId) {
                $query .= " AND cw.client_id = ? AND cw.client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = ?)";
                $params[] = $clientId;
                $params[] = $userId;
                $types .= "ii";
            } else if ($userRole === 'coach') {
                $query .= " AND cw.client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = ?)";
                $params[] = $userId;
                $types .= "i";
            }
            
            if ($status) {
                $query .= " AND cw.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            $query .= " ORDER BY cw.start_date DESC LIMIT 50";
            
            $stmt = $db->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $workouts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            jsonResponse($workouts);
        }
        break;
        
    case 'POST':
        // Create new workout log or update
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? 'log';
        
        if ($action === 'log') {
            // Log a set
            $exerciseLogId = (int)$data['exercise_log_id'];
            $setNumber = (int)$data['set_number'];
            $weight = (float)$data['weight'];
            $reps = (int)$data['reps'];
            
            // Check if this is the client's workout
            $stmt = $db->prepare("
                SELECT cw.id FROM client_workouts cw
                JOIN client_workout_exercises cwe ON cw.id = cwe.client_workout_id
                WHERE cwe.id = ? AND cw.client_id = ?
            ");
            $stmt->bind_param("ii", $exerciseLogId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0 && $userRole !== 'editor') {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
            
            // Update the exercise log
            $stmt = $db->prepare("
                UPDATE client_workout_exercises 
                SET weight = ?, reps = ?, completed = TRUE, logged_at = NOW()
                WHERE id = ? AND set_number = ?
            ");
            $stmt->bind_param("diii", $weight, $reps, $exerciseLogId, $setNumber);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                // Check if all exercises are completed
                $stmt = $db->prepare("
                    SELECT COUNT(*) as total, SUM(CASE WHEN completed = TRUE THEN 1 ELSE 0 END) as completed
                    FROM client_workout_exercises
                    WHERE client_workout_id = (SELECT client_workout_id FROM client_workout_exercises WHERE id = ?)
                ");
                $stmt->bind_param("i", $exerciseLogId);
                $stmt->execute();
                $stats = $stmt->get_result()->fetch_assoc();
                
                if ($stats['total'] == $stats['completed']) {
                    // Complete the workout
                    $stmt = $db->prepare("
                        UPDATE client_workouts 
                        SET status = 'completed', completed_at = NOW()
                        WHERE id = (SELECT client_workout_id FROM client_workout_exercises WHERE id = ?)
                    ");
                    $stmt->bind_param("i", $exerciseLogId);
                    $stmt->execute();
                    
                    logActivity($userId, 'workout_completed', "Completed workout");
                }
                
                jsonResponse(['success' => true, 'message' => 'Set logged successfully']);
            } else {
                jsonResponse(['error' => 'Failed to log set'], 400);
            }
            
        } else if ($action === 'start') {
            // Start a workout
            $workoutId = (int)$data['workout_id'];
            
            $stmt = $db->prepare("
                UPDATE client_workouts 
                SET status = 'in_progress', started_at = NOW()
                WHERE id = ? AND client_id = ?
            ");
            $stmt->bind_param("ii", $workoutId, $userId);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                logActivity($userId, 'workout_started', "Started workout ID: $workoutId");
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['error' => 'Workout not found'], 404);
            }
        }
        break;
        
    case 'PUT':
        // Update workout status
        if (!$workoutId) {
            jsonResponse(['error' => 'Workout ID required'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $status = sanitize($data['status'] ?? '');
        
        if (!in_array($status, ['scheduled', 'in_progress', 'completed', 'missed'])) {
            jsonResponse(['error' => 'Invalid status'], 400);
        }
        
        // Check permission
        if ($userRole === 'client') {
            $stmt = $db->prepare("UPDATE client_workouts SET status = ? WHERE id = ? AND client_id = ?");
            $stmt->bind_param("sii", $status, $workoutId, $userId);
        } else if ($userRole === 'coach') {
            $stmt = $db->prepare("
                UPDATE client_workouts 
                SET status = ? 
                WHERE id = ? AND client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = ?)
            ");
            $stmt->bind_param("sii", $status, $workoutId, $userId);
        } else {
            jsonResponse(['error' => 'Unauthorized'], 403);
        }
        
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            logActivity($userId, 'workout_updated', "Updated workout $workoutId to $status");
            jsonResponse(['success' => true]);
        } else {
            jsonResponse(['error' => 'Workout not found or unauthorized'], 404);
        }
        break;
        
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
?>