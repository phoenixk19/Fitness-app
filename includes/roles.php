<?php
// File: C:\xampp\htdocs\appF\includes\roles.php

require_once 'config.php';
require_once 'functions.php';
require_once 'session.php';

// Define role hierarchy
$roleHierarchy = [
    'admin' => 100,
    'coach' => 80,
    'editor' => 60,
    'client' => 40
];

// Define permissions per role
$rolePermissions = [
    'admin' => [
        'manage_coaches',
        'manage_clients',
        'manage_packages',
        'manage_payments',
        'manage_settings',
        'view_analytics',
        'manage_leads',
        'manage_editors',
        'view_all_data'
    ],
    'coach' => [
        'manage_clients',
        'create_workouts',
        'assign_workouts',
        'manage_nutrition',
        'track_progress',
        'manage_packages',
        'view_payments',
        'manage_editors',
        'view_client_data'
    ],
    'editor' => [
        'log_workouts',
        'log_meals',
        'add_comments',
        'upload_photos',
        'view_assigned_clients'
    ],
    'client' => [
        'view_workouts',
        'log_workouts',
        'log_meals',
        'upload_photos',
        'view_progress',
        'view_leaderboard'
    ]
];

// Check if user has specific permission
function hasPermission($permission) {
    global $rolePermissions;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'];
    
    // Admin has all permissions
    if ($userRole === 'admin') {
        return true;
    }
    
    return in_array($permission, $rolePermissions[$userRole] ?? []);
}

// Require specific permission
function requirePermission($permission) {
    if (!hasPermission($permission)) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // AJAX request
            jsonResponse(['error' => 'Unauthorized'], 403);
        } else {
            // Regular request
            $_SESSION['error'] = 'You do not have permission to access this page';
            redirect('/dashboard/' . $_SESSION['user_role'] . '.php');
        }
    }
}

// Get all users by role
function getUsersByRole($role) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, created_at FROM users WHERE role = ? AND status = 'active'");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get coach's clients
function getCoachClients($coachId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT u.* 
        FROM users u
        JOIN coaches_clients cc ON u.id = cc.client_id
        WHERE cc.coach_id = ? AND u.role = 'client'
    ");
    $stmt->bind_param("i", $coachId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Assign client to coach
function assignClientToCoach($clientId, $coachId) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO coaches_clients (coach_id, client_id, assigned_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $coachId, $clientId);
    
    if ($stmt->execute()) {
        logActivity($coachId, 'assign_client', "Assigned client ID: $clientId");
        return true;
    }
    return false;
}

// Get user's role specific dashboard data
function getDashboardData($userId, $role) {
    $db = getDB();
    $data = [];
    
    switch ($role) {
        case 'admin':
            // Get system stats
            $data['total_coaches'] = $db->query("SELECT COUNT(*) FROM users WHERE role = 'coach'")->fetch_row()[0];
            $data['total_clients'] = $db->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetch_row()[0];
            $data['total_leads'] = $db->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetch_row()[0];
            $data['monthly_revenue'] = $db->query("SELECT SUM(amount) FROM payments WHERE MONTH(date) = MONTH(NOW())")->fetch_row()[0];
            break;
            
        case 'coach':
            // Get coach's stats
            $stmt = $db->prepare("SELECT COUNT(*) FROM coaches_clients WHERE coach_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $data['total_clients'] = $stmt->get_result()->fetch_row()[0];
            
            // Get pending payments
            $stmt = $db->prepare("
                SELECT SUM(p.amount) 
                FROM payments p
                JOIN coaches_clients cc ON p.client_id = cc.client_id
                WHERE cc.coach_id = ? AND p.status = 'pending'
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $data['pending_payments'] = $stmt->get_result()->fetch_row()[0];
            break;
            
        case 'client':
            // Get client's stats
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM client_workouts 
                WHERE client_id = ? AND status = 'completed'
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $data['workouts_completed'] = $stmt->get_result()->fetch_row()[0];
            
            // Get latest weight
            $stmt = $db->prepare("
                SELECT weight 
                FROM body_measurements 
                WHERE client_id = ? 
                ORDER BY date DESC LIMIT 1
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $data['current_weight'] = $stmt->get_result()->fetch_row()[0];
            break;
    }
    
    return $data;
}

// Check if user can access another user's data
function canAccessUserData($currentUserId, $targetUserId) {
    $currentUser = getCurrentUser();
    
    // Admin can access everything
    if ($currentUser['role'] === 'admin') {
        return true;
    }
    
    // Coach can access their clients
    if ($currentUser['role'] === 'coach') {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM coaches_clients WHERE coach_id = ? AND client_id = ?");
        $stmt->bind_param("ii", $currentUserId, $targetUserId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Clients can only access their own data
    if ($currentUser['role'] === 'client') {
        return $currentUserId == $targetUserId;
    }
    
    return false;
}
?>