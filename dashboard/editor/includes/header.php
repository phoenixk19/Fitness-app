<?php
// File: dashboard/editor/includes/header.php

require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../includes/roles.php';

// Apply middleware - only editor can access
applyMiddleware(['authMiddleware', 'noCacheMiddleware'], function() {
    roleMiddleware(['editor']);
});

$userId = $_SESSION['user_id'];
$db = getDB();
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Get editor's assigned clients
$assignedClients = $db->query("
    SELECT u.id, u.name, u.email
    FROM users u
    JOIN editor_assignments ea ON u.id = ea.client_id
    WHERE ea.editor_id = $userId
    ORDER BY u.name
")->fetch_all(MYSQLI_ASSOC) ?? [];

$totalClients = count($assignedClients);

// Count pending tasks for today
$pendingWorkouts = 0;
$pendingMeals = 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Dashboard - FitCoach Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4A6FA5;
            --secondary-color: #166088;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
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
        .editor-sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: linear-gradient(180deg, var(--primary-color), var(--secondary-color)); color: white; z-index: 1000; box-shadow: 3px 0 15px rgba(0,0,0,0.1); transition: transform 0.3s ease; display: flex; flex-direction: column; }
        .editor-brand { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); flex-shrink: 0; }
        .editor-avatar { width: 80px; height: 80px; border-radius: 50%; background: white; color: var(--primary-color); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 15px; border: 4px solid rgba(255,255,255,0.3); }
        .menu-wrapper { flex: 1; overflow-y: auto; overflow-x: hidden; min-height: 0; }
        .menu-wrapper::-webkit-scrollbar { width: 5px; }
        .menu-wrapper::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .menu-wrapper::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.4); border-radius: 10px; }
        .editor-menu { list-style: none; padding: 10px 15px; margin: 0; }
        .editor-menu li { margin-bottom: 5px; }
        .editor-menu a { color: rgba(255,255,255,0.9); text-decoration: none; padding: 12px 15px; display: flex; align-items: center; gap: 12px; transition: all 0.3s; border-radius: 10px; border-left: 3px solid transparent; }
        .editor-menu a:hover, .editor-menu a.active { background: rgba(255,255,255,0.15); color: white; border-left-color: white; }
        .editor-menu .badge { margin-left: auto; background: rgba(255,255,255,0.2); }
        .sidebar-footer { padding: 15px; text-align: center; border-top: 1px solid rgba(255,255,255,0.1); flex-shrink: 0; font-size: 0.85rem; }
        .editor-main { margin-left: var(--sidebar-width); padding: 20px; min-height: 100vh; transition: margin-left 0.3s ease; }
        .editor-header { background: var(--card-bg); padding: 20px 25px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .header-title h1 { color: var(--primary-color); margin: 0; font-size: 1.8rem; }
        .header-title p { color: var(--text-light); margin: 5px 0 0 0; }
        .page-title { color: var(--primary-color); font-weight: 600; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card-bg); border-radius: 12px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.1); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px; background: rgba(74,111,165,0.1); color: var(--primary-color); }
        .stat-number { font-size: 2rem; font-weight: bold; }
        .client-selector { background: var(--card-bg); border-radius: 12px; padding: 15px 20px; margin-bottom: 25px; border: 1px solid rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .editor-tabs { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; border-bottom: 2px solid rgba(0,0,0,0.1); padding-bottom: 10px; }
        .tab-btn { padding: 10px 20px; background: transparent; border: none; border-radius: 8px; color: var(--text-color); font-weight: 500; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
        .tab-btn.active { background: var(--primary-color); color: white; }
        .tab-content { display: none; animation: fadeIn 0.3s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .workout-card { background: var(--card-bg); border-radius: 12px; padding: 25px; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.1); }
        .client-info { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid rgba(0,0,0,0.1); }
        .client-avatar { width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; }
        .exercise-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; background: var(--light-bg); border-radius: 8px; margin-bottom: 10px; border-left: 4px solid var(--primary-color); flex-wrap: wrap; gap: 15px; }
        .exercise-inputs { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .input-group-small input { width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 5px; background: var(--card-bg); color: var(--text-color); }
        .meal-log-form { background: var(--card-bg); border-radius: 12px; padding: 25px; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.1); }
        .food-item { display: flex; gap: 15px; align-items: center; margin-bottom: 15px; padding: 15px; background: var(--light-bg); border-radius: 8px; flex-wrap: wrap; }
        .food-details { flex: 1; }
        .comment-card { background: var(--card-bg); border-radius: 12px; padding: 25px; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.1); }
        .comment-box { background: var(--light-bg); border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .comment-meta { display: flex; justify-content: space-between; font-size: 0.9rem; color: var(--text-light); margin-bottom: 10px; flex-wrap: wrap; gap: 10px; }
        .photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .photo-card { background: var(--card-bg); border-radius: 10px; overflow: hidden; border: 1px solid rgba(0,0,0,0.1); }
        .photo-placeholder { height: 150px; background: linear-gradient(45deg, var(--light-bg), var(--card-bg)); display: flex; align-items: center; justify-content: center; color: var(--text-light); }
        .photo-info { padding: 15px; }
        .data-table { background: var(--card-bg); border-radius: 12px; padding: 20px; border: 1px solid rgba(0,0,0,0.1); }
        .table th { border-bottom: 2px solid var(--primary-color); }
        .btn-editor { background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); border: none; padding: 10px 25px; border-radius: 8px; color: white; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-editor:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(74,111,165,0.3); color: white; }
        .btn-outline-editor { background: transparent; border: 2px solid var(--primary-color); color: var(--primary-color); padding: 8px 20px; border-radius: 8px; transition: all 0.3s; }
        .btn-outline-editor:hover { background: var(--primary-color); color: white; }
        .theme-toggle { position: fixed; bottom: 20px; right: 20px; z-index: 1000; background: var(--primary-color); color: white; border: none; width: 50px; height: 50px; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .form-control, .form-select, textarea { background-color: var(--light-bg); border: 2px solid #e0e0e0; border-radius: 8px; padding: 10px 15px; color: var(--text-color); }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select, [data-bs-theme="dark"] textarea { background-color: #2d2d2d; border-color: #444; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .menu-toggle { display: none; background: none; border: none; color: var(--text-color); font-size: 1.5rem; cursor: pointer; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; display: none; }
        .sidebar-overlay.active { display: block; }
        @media (max-width: 992px) {
            .editor-sidebar { transform: translateX(-100%); }
            .editor-sidebar.open { transform: translateX(0); }
            .editor-main { margin-left: 0; }
            .menu-toggle { display: block !important; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="editor-sidebar" id="editorSidebar">
        <div class="editor-brand">
            <div class="editor-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
            <h5 class="mb-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
            <p class="small opacity-75 mb-0">Assistant Editor</p>
        </div>
        <div class="menu-wrapper">
            <ul class="editor-menu">
                <li><a href="index.php" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="clients.php" class="<?php echo $current_page == 'clients' ? 'active' : ''; ?>"><i class="bi bi-people"></i> Assigned Clients <span class="badge"><?php echo $totalClients; ?></span></a></li>
                <li><a href="workouts.php" class="<?php echo $current_page == 'workouts' ? 'active' : ''; ?>"><i class="bi bi-activity"></i> Workout Logging</a></li>
                <li><a href="nutrition.php" class="<?php echo $current_page == 'nutrition' ? 'active' : ''; ?>"><i class="bi bi-egg-fried"></i> Nutrition Tracking</a></li>
                <li><a href="comments.php" class="<?php echo $current_page == 'comments' ? 'active' : ''; ?>"><i class="bi bi-chat-dots"></i> Add Comments</a></li>
                <li><a href="photos.php" class="<?php echo $current_page == 'photos' ? 'active' : ''; ?>"><i class="bi bi-images"></i> Progress Photos</a></li>
                <li class="mt-4"><a href="/appF/home/index.php"><i class="bi bi-globe"></i> Public Website</a></li>
                <li><a href="/appF/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <small class="opacity-75">Editor Access Only</small>
        </div>
    </div>
    <div class="editor-main">
        <div class="editor-header">
            <button class="menu-toggle" id="menuToggle"><i class="bi bi-list"></i></button>
            <div class="header-title">
                <h1>Editor Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            </div>
        </div>