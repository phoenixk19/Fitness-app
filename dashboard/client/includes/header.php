<?php
// File: dashboard/client/includes/header.php

require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../includes/roles.php';

// Apply middleware - only client can access
applyMiddleware(['authMiddleware', 'noCacheMiddleware'], function() {
    roleMiddleware(['client']);
});

$userId = $_SESSION['user_id'];
$db = getDB();
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Get client data
$client = $db->query("SELECT name, email, phone, created_at FROM users WHERE id = $userId")->fetch_assoc();

// Get unread messages count
$unreadMessages = 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - FitCoach Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4A6FA5;
            --secondary-color: #166088;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6c757d;
            --sidebar-width: 280px;
        }
        [data-bs-theme="dark"] {
            --primary-color: #5d8fd8;
            --secondary-color: #1c7ebd;
            --light-bg: #121212;
            --dark-bg: #0a0a0a;
            --card-bg: #1e1e1e;
            --text-color: #f8f9fa;
            --text-light: #adb5bd;
        }
        body { background-color: var(--light-bg); color: var(--text-color); transition: all 0.3s ease; overflow-x: hidden; }
        .client-sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: linear-gradient(180deg, var(--primary-color), var(--secondary-color)); color: white; z-index: 1000; box-shadow: 3px 0 15px rgba(0,0,0,0.1); transition: transform 0.3s ease; display: flex; flex-direction: column; }
        .client-brand { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); flex-shrink: 0; }
        .client-avatar-large { width: 80px; height: 80px; border-radius: 50%; background: white; color: var(--primary-color); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 15px; border: 4px solid rgba(255,255,255,0.3); }
        .menu-wrapper { flex: 1; overflow-y: auto; overflow-x: hidden; min-height: 0; }
        .menu-wrapper::-webkit-scrollbar { width: 5px; }
        .menu-wrapper::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .menu-wrapper::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.4); border-radius: 10px; }
        .client-menu { list-style: none; padding: 10px 15px; margin: 0; }
        .client-menu li { margin-bottom: 5px; }
        .client-menu a { color: rgba(255,255,255,0.9); text-decoration: none; padding: 12px 15px; display: flex; align-items: center; gap: 12px; transition: all 0.3s; border-radius: 10px; border-left: 3px solid transparent; }
        .client-menu a:hover, .client-menu a.active { background: rgba(255,255,255,0.15); color: white; border-left-color: white; }
        .sidebar-footer { padding: 15px; text-align: center; border-top: 1px solid rgba(255,255,255,0.1); flex-shrink: 0; font-size: 0.85rem; }
        .client-main { margin-left: var(--sidebar-width); padding: 20px; min-height: 100vh; transition: margin-left 0.3s ease; }
        .client-header { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 20px 25px; border-radius: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .client-info h1 { margin: 0; font-size: 1.8rem; }
        .client-info p { margin: 5px 0 0 0; opacity: 0.9; }
        .page-title { color: var(--primary-color); font-weight: 600; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .stat-card { background: var(--card-bg); border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.1); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .today-overview { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .overview-card { background: var(--card-bg); border-radius: 15px; padding: 25px; border: 1px solid rgba(0,0,0,0.1); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid rgba(0,0,0,0.1); }
        .card-title { color: var(--primary-color); font-weight: 600; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; }
        .exercise-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: var(--light-bg); border-radius: 10px; margin-bottom: 10px; border-left: 4px solid var(--primary-color); }
        .start-workout-btn { background: linear-gradient(45deg, var(--success-color), #20c997); border: none; padding: 12px; border-radius: 10px; color: white; font-weight: 600; width: 100%; }
        .nutrition-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .macro-circle { text-align: center; padding: 15px; border-radius: 10px; background: var(--light-bg); }
        .macro-value { font-size: 1.2rem; font-weight: bold; display: block; }
        .progress-section { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .progress-card { background: var(--card-bg); border-radius: 15px; padding: 25px; border: 1px solid rgba(0,0,0,0.1); }
        .chart-container { height: 250px; margin-top: 20px; }
        .week-calendar { background: var(--card-bg); border-radius: 15px; padding: 25px; margin-bottom: 30px; border: 1px solid rgba(0,0,0,0.1); }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; margin-top: 20px; }
        .day-cell { text-align: center; padding: 15px 10px; border-radius: 10px; background: var(--light-bg); }
        .day-cell.today { background: var(--primary-color); color: white; }
        .day-cell.workout { background: rgba(40,167,69,0.1); border: 2px solid var(--success-color); }
        .day-cell.rest { background: rgba(108,117,125,0.1); border: 2px solid var(--text-light); }
        .photos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .photo-card { background: var(--card-bg); border-radius: 10px; overflow: hidden; border: 1px solid rgba(0,0,0,0.1); }
        .photo-placeholder { height: 150px; background: linear-gradient(45deg, var(--light-bg), var(--card-bg)); display: flex; align-items: center; justify-content: center; }
        .photo-info { padding: 15px; }
        .leaderboard-list { margin-top: 20px; }
        .leaderboard-item { display: flex; align-items: center; padding: 15px; background: var(--light-bg); border-radius: 10px; margin-bottom: 10px; }
        .leaderboard-item.me { background: rgba(74,111,165,0.1); border: 2px solid var(--primary-color); }
        .rank { width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; }
        .data-table { background: var(--card-bg); border-radius: 15px; padding: 20px; border: 1px solid rgba(0,0,0,0.1); }
        .table th { border-bottom: 2px solid var(--primary-color); }
        .btn-client { background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); border: none; padding: 10px 20px; border-radius: 10px; color: white; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-client:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(74,111,165,0.3); color: white; }
        .btn-outline-client { background: transparent; border: 2px solid var(--primary-color); color: var(--primary-color); padding: 8px 20px; border-radius: 10px; transition: all 0.3s; }
        .btn-outline-client:hover { background: var(--primary-color); color: white; }
        .theme-toggle { position: fixed; bottom: 20px; right: 20px; z-index: 1000; background: var(--primary-color); color: white; border: none; width: 50px; height: 50px; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .form-control { background-color: var(--light-bg); border: 2px solid #e0e0e0; border-radius: 10px; padding: 12px 15px; color: var(--text-color); }
        [data-bs-theme="dark"] .form-control { background-color: #2d2d2d; border-color: #444; }
        .menu-toggle { display: none; background: none; border: none; color: var(--text-color); font-size: 1.5rem; cursor: pointer; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; display: none; }
        .sidebar-overlay.active { display: block; }
        @media (max-width: 992px) {
            .client-sidebar { transform: translateX(-100%); }
            .client-sidebar.open { transform: translateX(0); }
            .client-main { margin-left: 0; }
            .menu-toggle { display: block !important; }
            .today-overview { grid-template-columns: 1fr; }
            .nutrition-summary { grid-template-columns: repeat(2, 1fr); }
            .calendar-grid { grid-template-columns: repeat(4, 1fr); }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="client-sidebar" id="clientSidebar">
        <div class="client-brand">
            <div class="client-avatar-large"><?php echo strtoupper(substr($client['name'], 0, 2)); ?></div>
            <h5 class="mb-1"><?php echo htmlspecialchars($client['name']); ?></h5>
            <p class="small opacity-75 mb-0">Active Member</p>
        </div>
        <div class="menu-wrapper">
            <ul class="client-menu">
                <li><a href="index.php" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="workouts.php" class="<?php echo $current_page == 'workouts' ? 'active' : ''; ?>"><i class="bi bi-activity"></i> My Workouts</a></li>
                <li><a href="nutrition.php" class="<?php echo $current_page == 'nutrition' ? 'active' : ''; ?>"><i class="bi bi-egg-fried"></i> Nutrition</a></li>
                <li><a href="progress.php" class="<?php echo $current_page == 'progress' ? 'active' : ''; ?>"><i class="bi bi-graph-up"></i> My Progress</a></li>
                <li><a href="photos.php" class="<?php echo $current_page == 'photos' ? 'active' : ''; ?>"><i class="bi bi-images"></i> Progress Photos</a></li>
                <li><a href="leaderboard.php" class="<?php echo $current_page == 'leaderboard' ? 'active' : ''; ?>"><i class="bi bi-trophy"></i> Leaderboard</a></li>
                <li><a href="profile.php" class="<?php echo $current_page == 'profile' ? 'active' : ''; ?>"><i class="bi bi-person"></i> Profile</a></li>
                <li><a href="messages.php" class="<?php echo $current_page == 'messages' ? 'active' : ''; ?>"><i class="bi bi-chat-dots"></i> Messages <?php if ($unreadMessages > 0): ?><span class="badge bg-danger"><?php echo $unreadMessages; ?></span><?php endif; ?></a></li>
                <li class="mt-4"><a href="/appF/home/index.php"><i class="bi bi-globe"></i> Public Website</a></li>
                <li><a href="/appF/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <small class="opacity-75">Member since <?php echo date('M Y', strtotime($client['created_at'] ?? 'now')); ?></small>
        </div>
    </div>
    <div class="client-main">
        <div class="client-header">
            <button class="menu-toggle" id="menuToggle"><i class="bi bi-list"></i></button>
            <div class="client-info">
                <h1>Welcome back, <?php echo htmlspecialchars($client['name']); ?>!</h1>
                <p>Keep pushing forward - your fitness journey continues</p>
            </div>
        </div>