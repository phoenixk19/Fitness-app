<?php
// File: reset-all-passwords.php
require_once 'includes/config.php';

$db = getDB();
$password = 'Admin@123';

// Generate new hash
$newHash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Resetting all user passwords</h2>";
echo "New hash being used: " . $newHash . "<br><br>";

// Update all users
$stmt = $db->prepare("UPDATE users SET password_hash = ?");
$stmt->bind_param("s", $newHash);

if ($stmt->execute()) {
    echo "<p style='color:green'>✓ All user passwords have been updated!</p>";
} else {
    echo "<p style='color:red'>✗ Error: " . $db->error . "</p>";
}

// Verify each user
$result = $db->query("SELECT id, name, email, role, password_hash FROM users");
echo "<h3>Verification:</h3>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Name</th><th>Email</th><th>Role</th><th>Hash Match</th><th>Test Login</th></tr>";

while ($row = $result->fetch_assoc()) {
    $hashMatch = ($row['password_hash'] === $newHash) ? '✓ Yes' : '✗ No';
    $testVerify = password_verify($password, $row['password_hash']) ? '✓ Works' : '✗ Fails';
    
    echo "<tr>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . $row['role'] . "</td>";
    echo "<td>" . $hashMatch . "</td>";
    echo "<td>" . $testVerify . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Try logging in with:</h3>";
echo "<ul>";
echo "<li>Admin: admin@fitcoach.com / Admin@123</li>";
echo "<li>Coach: sarah@fitcoach.com / Admin@123</li>";
echo "<li>Client: michael@example.com / Admin@123</li>";
echo "<li>Editor: editor@fitcoach.com / Admin@123</li>";
echo "</ul>";
?>