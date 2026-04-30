<?php
// File: C:\xampp\htdocs\appF\api\upload-photo.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/middleware.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Check authentication
authMiddleware();

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];
$db = getDB();

// Get form data
$clientId = isset($_POST['client_id']) ? (int)$_POST['client_id'] : null;
$bodyPart = sanitize($_POST['body_part'] ?? 'front');
$comments = sanitize($_POST['comments'] ?? '');
$photoDate = sanitize($_POST['date'] ?? date('Y-m-d'));

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

// Check if file was uploaded
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(['error' => 'No file uploaded or upload error'], 400);
}

// Create upload directory if it doesn't exist
$uploadDir = '../uploads/progress-photos/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Upload file
$result = uploadFile($_FILES['photo'], $uploadDir, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

if (!$result['success']) {
    jsonResponse(['error' => $result['error']], 400);
}

// Save to database
$photoUrl = '/uploads/progress-photos/' . $result['filename'];

$stmt = $db->prepare("
    INSERT INTO progress_photos (client_id, photo_date, photo_url, body_part, comments, uploaded_by)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("issssi", $clientId, $photoDate, $photoUrl, $bodyPart, $comments, $userId);
$stmt->execute();

logActivity($userId, 'photo_uploaded', "Uploaded progress photo for client $clientId");

jsonResponse([
    'success' => true,
    'photo_id' => $db->insert_id,
    'photo_url' => $photoUrl,
    'message' => 'Photo uploaded successfully'
]);
?>