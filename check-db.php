<?php
// File: C:\xampp\htdocs\appF\check-db.php

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'fitcoach_pro';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Connection: SUCCESS</h2>";

// Check users table
$result = $conn->query("SELECT id, name, email, role FROM users");
echo "<h3>Users in Database:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No users found</td></tr>";
}
echo "</table>";

// Check if admin exists and test password
$adminCheck = $conn->query("SELECT * FROM users WHERE email = 'admin@fitcoach.com'");
if ($adminCheck->num_rows > 0) {
    $admin = $adminCheck->fetch_assoc();
    echo "<h3>Admin User Found:</h3>";
    echo "Name: " . $admin['name'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Password Hash: " . substr($admin['password_hash'], 0, 50) . "...<br>";
    echo "Hash Length: " . strlen($admin['password_hash']) . " characters<br>";
    
    // Test password verification
    $testPassword = 'Admin@123';
    if (password_verify($testPassword, $admin['password_hash'])) {
        echo "<strong style='color:green'>Password 'Admin@123' is CORRECT!</strong><br>";
    } else {
        echo "<strong style='color:red'>Password 'Admin@123' is INCORRECT!</strong><br>";
        echo "Need to update password hash.<br>";
    }
} else {
    echo "<h3 style='color:red'>Admin user NOT FOUND!</h3>";
}

// If password is wrong, this will fix it
if ($adminCheck->num_rows > 0 && !password_verify('Admin@123', $admin['password_hash'])) {
    echo "<h3>Fixing password...</h3>";
    $newHash = password_hash('Admin@123', PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password_hash = '$newHash' WHERE email = 'admin@fitcoach.com'");
    echo "Password updated! <a href='check-db.php'>Refresh to verify</a>";
}

$conn->close();
?>