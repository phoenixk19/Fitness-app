<?php
// File: D:\Program Files\xampp\htdocs\appF\test.php

ob_start();
echo "========================================\n";
echo "FITCOACH PRO - SYSTEM DIAGNOSTIC REPORT\n";
echo "========================================\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// 1. PHP Environment
echo "1. PHP ENVIRONMENT\n";
echo "-----------------\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Path: " . __DIR__ . "\n\n";

// 2. Database Connection
echo "2. DATABASE CONNECTION\n";
echo "---------------------\n";
require_once 'includes/config.php';
$db = getDB();
if ($db->ping()) {
    echo "Database Connection: SUCCESS\n";
} else {
    echo "Database Connection: FAILED\n";
}

// Get database info
$dbName = $db->query("SELECT DATABASE()")->fetch_row()[0];
echo "Database Name: " . $dbName . "\n";

// Check tables
$tables = ['users', 'leads', 'packages', 'payments', 'client_workouts', 'meal_logs', 'body_measurements', 'progress_photos', 'fitness_tests', 'notifications', 'site_settings'];
echo "\nTables Status:\n";
foreach ($tables as $table) {
    $result = $db->query("SHOW TABLES LIKE '$table'");
    $status = $result && $result->num_rows > 0 ? "✓ EXISTS" : "✗ MISSING";
    echo "  - $table: $status\n";
}

// 3. Users Count
echo "\n3. USERS\n";
echo "--------\n";
$roles = ['admin', 'coach', 'editor', 'client'];
foreach ($roles as $role) {
    $count = $db->query("SELECT COUNT(*) FROM users WHERE role = '$role'")->fetch_row()[0] ?? 0;
    echo ucfirst($role) . "s: $count\n";
}

// Sample users
echo "\nSample Users:\n";
$users = $db->query("SELECT id, name, email, role FROM users LIMIT 10");
while ($user = $users->fetch_assoc()) {
    echo "  - ID:{$user['id']} | {$user['name']} ({$user['email']}) - Role: {$user['role']}\n";
}

// 4. Leads (Applications)
echo "\n4. LEADS (APPLICATIONS)\n";
echo "-----------------------\n";
$leadCount = $db->query("SELECT COUNT(*) FROM leads")->fetch_row()[0] ?? 0;
echo "Total Leads: $leadCount\n";
$statusCounts = $db->query("SELECT status, COUNT(*) as count FROM leads GROUP BY status");
while ($row = $statusCounts->fetch_assoc()) {
    echo "  - {$row['status']}: {$row['count']}\n";
}

// 5. Packages
echo "\n5. PACKAGES\n";
echo "-----------\n";
$packageCount = $db->query("SELECT COUNT(*) FROM packages")->fetch_row()[0] ?? 0;
echo "Total Packages: $packageCount\n";

// 6. Payments
echo "\n6. PAYMENTS\n";
echo "-----------\n";
$paymentCount = $db->query("SELECT COUNT(*) FROM payments")->fetch_row()[0] ?? 0;
$totalAmount = $db->query("SELECT COALESCE(SUM(amount),0) FROM payments")->fetch_row()[0] ?? 0;
echo "Total Payments: $paymentCount\n";
echo "Total Amount: $$totalAmount\n";

// 7. Site Settings
echo "\n7. SITE SETTINGS\n";
echo "----------------\n";
$settings = $db->query("SELECT setting_key, setting_value FROM site_settings");
while ($row = $settings->fetch_assoc()) {
    echo "  - {$row['setting_key']}: " . substr($row['setting_value'], 0, 50) . (strlen($row['setting_value']) > 50 ? '...' : '') . "\n";
}

// 8. Dashboard Files Check
echo "\n8. DASHBOARD FILES\n";
echo "------------------\n";
$dashboards = ['admin', 'coach', 'client', 'editor'];
foreach ($dashboards as $dashboard) {
    $indexPath = __DIR__ . "/dashboard/$dashboard/index.php";
    $exists = file_exists($indexPath);
    echo ucfirst($dashboard) . " Dashboard: " . ($exists ? "✓ EXISTS" : "✗ MISSING") . "\n";
    
    if ($exists) {
        $headerPath = __DIR__ . "/dashboard/$dashboard/includes/header.php";
        $footerPath = __DIR__ . "/dashboard/$dashboard/includes/footer.php";
        echo "    - header.php: " . (file_exists($headerPath) ? "✓" : "✗") . "\n";
        echo "    - footer.php: " . (file_exists($footerPath) ? "✓" : "✗") . "\n";
    }
}

// 9. API Files Check
echo "\n9. API ENDPOINTS\n";
echo "----------------\n";
$apiFiles = ['auth.php', 'workouts.php', 'nutrition.php', 'progress.php', 'upload-photo.php', 'settings.php'];
foreach ($apiFiles as $apiFile) {
    $exists = file_exists(__DIR__ . "/api/$apiFile");
    echo "  - $apiFile: " . ($exists ? "✓ EXISTS" : "✗ MISSING") . "\n";
}

// 10. Upload Directory
echo "\n10. UPLOADS DIRECTORY\n";
echo "--------------------\n";
$uploadDir = __DIR__ . "/uploads";
if (is_dir($uploadDir)) {
    echo "Uploads directory: ✓ EXISTS\n";
    $photoDir = $uploadDir . "/progress-photos";
    if (is_dir($photoDir)) {
        $photoCount = count(glob($photoDir . "/*.{jpg,jpeg,png,gif}", GLOB_BRACE));
        echo "  - progress-photos/: EXISTS ($photoCount files)\n";
    } else {
        echo "  - progress-photos/: ✗ MISSING\n";
    }
} else {
    echo "Uploads directory: ✗ MISSING\n";
}

// 11. API Connectivity Test (simple)
echo "\n11. API CONNECTIVITY\n";
echo "--------------------\n";
$url = "http://localhost/appF/api/auth.php?action=check";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "API is reachable (HTTP $httpCode)\n";
    $data = json_decode($response, true);
    if (isset($data['logged_in'])) {
        echo "  - Auth endpoint: WORKING\n";
    } else {
        echo "  - Auth endpoint: RESPONDING but unexpected format\n";
    }
} else {
    echo "API not reachable (HTTP $httpCode)\n";
}

// 12. Summary
echo "\n12. SUMMARY\n";
echo "-----------\n";
$issues = [];

// Check critical tables
foreach ($tables as $table) {
    $result = $db->query("SHOW TABLES LIKE '$table'");
    if (!$result || $result->num_rows === 0) {
        $issues[] = "Missing table: $table";
    }
}

// Check dashboard files
foreach ($dashboards as $dashboard) {
    if (!file_exists(__DIR__ . "/dashboard/$dashboard/index.php")) {
        $issues[] = "Missing dashboard: $dashboard/index.php";
    }
}

if (count($issues) > 0) {
    echo "ISSUES FOUND (" . count($issues) . "):\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
} else {
    echo "✓ All critical components are present!\n";
}

echo "\n========================================\n";
echo "DIAGNOSTIC COMPLETE\n";
echo "========================================\n";

// Write to file
$output = ob_get_clean();
file_put_contents(__DIR__ . '/result.txt', $output);
echo $output;
echo "\n\nReport saved to: result.txt\n";
?>