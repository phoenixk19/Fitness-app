<?php
// File: C:\xampp\htdocs\appF\reset-passwords.php

require_once 'includes/config.php';

$db = getDB();

// Create fresh password hash for 'Admin@123' on THIS PC
$newHash = password_hash('Admin@123', PASSWORD_DEFAULT);

echo "<h2>Resetting Passwords</h2>";
echo "<p>New hash generated for this PC: <code>" . $newHash . "</code></p>";

// Update all users
$users = [
    ['email' => 'admin@fitcoach.com', 'name' => 'Admin'],
    ['email' => 'sarah@fitcoach.com', 'name' => 'Coach Sarah'],
    ['email' => 'michael@example.com', 'name' => 'Client Michael'],
    ['email' => 'editor@fitcoach.com', 'name' => 'Editor']
];

echo "<ul>";
foreach ($users as $user) {
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->bind_param("ss", $newHash, $user['email']);
    
    if ($stmt->execute()) {
        echo "<li style='color:green'>✓ Password reset for: " . $user['name'] . " (" . $user['email'] . ")</li>";
    } else {
        echo "<li style='color:red'>✗ Failed: " . $user['email'] . "</li>";
    }
}
echo "</ul>";

// Verify
echo "<h3>Verification:</h3>";
$testHash = $db->query("SELECT password_hash FROM users WHERE email = 'admin@fitcoach.com'")->fetch_row()[0];
$isValid = password_verify('Admin@123', $testHash);

if ($isValid) {
    echo "<p style='color:green; font-weight:bold;'>✓ PASSWORDS ARE WORKING!</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>✗ Still not working. Please run this script again.</p>";
}

echo "<p><a href='/appF/login.php' class='btn btn-primary'>Go to Login</a></p>";
?>