<?php
// File: dashboard/admin/includes/header.php

require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../includes/roles.php';

// Apply middleware - only admin can access
applyMiddleware(['authMiddleware', 'noCacheMiddleware'], function() {
    roleMiddleware(['admin']);
});

$db = getDB();
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Get counts for sidebar badges
$totalCoaches = $db->query("SELECT COUNT(*) FROM users WHERE role = 'coach'")->fetch_row()[0] ?? 0;
$totalClients = $db->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetch_row()[0] ?? 0;
$newLeads = $db->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetch_row()[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FitCoach Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4A6FA5;
            --secondary-color: #166088;
            --accent-color: #17a2b8;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6c757d;
            --sidebar-width: 260px;
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
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: linear-gradient(180deg, var(--primary-color), var(--secondary-color)); color: white; z-index: 1000; box-shadow: 3px 0 10px rgba(0,0,0,0.1); transition: transform 0.3s ease; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); flex-shrink: 0; }
        .menu-wrapper { flex: 1; overflow-y: auto; overflow-x: hidden; min-height: 0; }
        .menu-wrapper::-webkit-scrollbar { width: 5px; }
        .menu-wrapper::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .menu-wrapper::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.4); border-radius: 10px; }
        .sidebar-menu { list-style: none; padding: 10px 15px; margin: 0; }
        .sidebar-menu li { margin-bottom: 5px; }
        .sidebar-menu a { color: rgba(255,255,255,0.9); text-decoration: none; padding: 10px 15px; display: flex; align-items: center; gap: 12px; transition: all 0.3s; border-radius: 8px; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.15); color: white; }
        .sidebar-menu .badge { margin-left: auto; }
        .sidebar-footer { padding: 15px; text-align: center; border-top: 1px solid rgba(255,255,255,0.1); flex-shrink: 0; font-size: 0.85rem; }
        .main-content { margin-left: var(--sidebar-width); padding: 20px; min-height: 100vh; transition: margin-left 0.3s ease; }
        .top-navbar { background: var(--card-bg); padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .page-title { color: var(--primary-color); font-weight: 600; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .stat-card { background: var(--card-bg); border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.1); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px; background: rgba(74,111,165,0.1); color: var(--primary-color); }
        .stat-number { font-size: 2rem; font-weight: bold; }
        .data-table { background: var(--card-bg); border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.1); }
        .table th { border-bottom: 2px solid var(--primary-color); }
        .btn-primary { background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); border: none; padding: 8px 20px; border-radius: 8px; color: white; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(74,111,165,0.3); color: white; }
        .btn-outline-primary { background: transparent; border: 2px solid var(--primary-color); color: var(--primary-color); padding: 6px 18px; border-radius: 8px; transition: all 0.3s; }
        .btn-outline-primary:hover { background: var(--primary-color); color: white; }
        .theme-toggle { position: fixed; bottom: 20px; right: 20px; z-index: 1000; background: var(--primary-color); color: white; border: none; width: 45px; height: 45px; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .form-control, .form-select { background-color: var(--light-bg); border: 2px solid #e0e0e0; border-radius: 8px; padding: 10px 15px; color: var(--text-color); }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: #2d2d2d; border-color: #444; }
        .menu-toggle { display: none; background: none; border: none; color: var(--text-color); font-size: 1.5rem; cursor: pointer; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; display: none; }
        .sidebar-overlay.active { display: block; }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .menu-toggle { display: block !important; }
        }
        .badge-danger { background: #dc3545; }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-activity me-2"></i> FitCoach Pro</h4>
            <p class="mb-0 small opacity-75">Admin Panel</p>
        </div>
        <div class="menu-wrapper">
            <ul class="sidebar-menu">
                <li><a href="index.php" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="coaches.php" class="<?php echo $current_page == 'coaches' ? 'active' : ''; ?>"><i class="bi bi-people"></i> Coaches <span class="badge"><?php echo $totalCoaches; ?></span></a></li>
                <li><a href="settings.php" class="<?php echo $current_page == 'settings' ? 'active' : ''; ?>"><i class="bi bi-gear"></i> Settings</a></li>
                <li><a href="branding.php" class="<?php echo $current_page == 'branding' ? 'active' : ''; ?>"><i class="bi bi-palette"></i> Branding</a></li>
                <li class="mt-4"><a href="/appF/home/index.php"><i class="bi bi-globe"></i> Public Website</a></li>
                <li><a href="/appF/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <div class="d-flex align-items-center justify-content-center">
                <div class="user-avatar me-2" style="width:35px; height:35px; border-radius:50%; background:white; color:var(--primary-color); display:flex; align-items:center; justify-content:center; font-weight:bold;"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
                <div><div class="small fw-bold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div><div class="x-small opacity-75">Administrator</div></div>
            </div>
        </div>
    </div>
    <div class="main-content">
        <div class="top-navbar">
            <button class="menu-toggle" id="menuToggle"><i class="bi bi-list"></i></button>
            <div class="search-box"><input type="text" class="form-control" placeholder="Search..."></div>
            <div class="user-info"><i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
        </div>