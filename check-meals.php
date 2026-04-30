<?php
// File: C:\xampp\htdocs\appF\check-meals.php

require_once 'includes/config.php';

$db = getDB();

echo "<h2>Meal Logs in Database</h2>";

$result = $db->query("SELECT * FROM meal_logs ORDER BY id DESC");
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Client ID</th><th>Meal Type</th><th>Meal Date</th><th>Total Calories</th><th>Created At</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['client_id'] . "</td>";
        echo "<td>" . $row['meal_type'] . "</td>";
        echo "<td>" . $row['meal_date'] . "</td>";
        echo "<td>" . $row['total_calories'] . "</td>";
        echo "<td>" . $row['logged_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No meal logs found!</p>";
}

// Also check if client ID 3 exists
echo "<h2>Client ID 3:</h2>";
$clientCheck = $db->query("SELECT id, name, email FROM users WHERE id = 3");
if ($clientCheck->num_rows > 0) {
    $client = $clientCheck->fetch_assoc();
    echo "Client found: " . $client['name'] . " (" . $client['email'] . ")";
} else {
    echo "Client ID 3 not found!";
}
?>