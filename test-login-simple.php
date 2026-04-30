<?php
// File: test-login-simple.php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$email = 'editor@fitcoach.com';
$password = 'Admin@123';

echo "<h2>Testing Login for: $email</h2>";

// Test direct database query
$db = getDB();
$stmt = $db->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "<p>User found: " . $row['name'] . "</p>";
    echo "<p>Stored hash: " . substr($row['password_hash'], 0, 30) . "...</p>";
    
    if (password_verify($password, $row['password_hash'])) {
        echo "<p style='color:green'>✓ Password verification: SUCCESS!</p>";
        echo "<p>You can login with: $email / $password</p>";
    } else {
        echo "<p style='color:red'>✗ Password verification: FAILED!</p>";
        echo "<p>Creating new hash for this user...</p>";
        
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $update->bind_param("ss", $newHash, $email);
        $update->execute();
        
        echo "<p style='color:green'>✓ Password has been reset! Try logging in now.</p>";
        echo "<p>New hash: " . substr($newHash, 0, 30) . "...</p>";
    }
} else {
    echo "<p style='color:red'>User not found! Creating editor user...</p>";
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $db->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'editor', 'active')");
    $insert->bind_param("sss", $name, $email, $hash);
    $name = "Editor Smith";
    $insert->execute();
    
    echo "<p style='color:green'>✓ Editor user created! Try logging in now.</p>";
}

// Show all users
echo "<h3>All Users in Database:</h3>";
$all = $db->query("SELECT id, name, email, role FROM users");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
while ($u = $all->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $u['id'] . "</td>";
    echo "<td>" . $u['name'] . "</td>";
    echo "<td>" . $u['email'] . "</td>";
    echo "<td>" . $u['role'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>