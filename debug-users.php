<?php
// File: C:\xampp\htdocs\appF\debug-users.php

require_once 'includes/config.php';

$db = getDB();

// Get all users
$result = $db->query("SELECT id, name, email, role, LENGTH(password_hash) as hash_length FROM users");

echo "<h2>Users in Database</h2>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Hash Length</th><th>Password Test</th></tr>";

$testPassword = 'Admin@123';

while ($row = $result->fetch_assoc()) {
    // Test if password works
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $row['id']);
    $stmt->execute();
    $hashResult = $stmt->get_result();
    $hashRow = $hashResult->fetch_assoc();
    
    $passwordValid = password_verify($testPassword, $hashRow['password_hash']);
    $status = $passwordValid ? '<span style="color:green">✓ Valid</span>' : '<span style="color:red">✗ Invalid</span>';
    
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . $row['role'] . "</td>";
    echo "<td>" . $row['hash_length'] . "</td>";
    echo "<td>" . $status . "</td>";
    echo "</tr>";
}
echo "</table>";

// Also check editor assignments
echo "<h2>Editor Assignments</h2>";
$assignResult = $db->query("SELECT * FROM editor_assignments");
if ($assignResult->num_rows > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Editor ID</th><th>Client ID</th><th>Assigned At</th></tr>";
    while ($row = $assignResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['editor_id'] . "</td>";
        echo "<td>" . $row['client_id'] . "</td>";
        echo "<td>" . $row['assigned_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No editor assignments found.</p>";
}
?>