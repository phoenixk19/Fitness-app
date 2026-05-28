<?php
// File: dashboard/coach/includes/header.php

require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../includes/roles.php';

// Apply middleware - only coach can access
applyMiddleware(['authMiddleware', 'noCacheMiddleware'], function() {
    roleMiddleware(['coach']);
});

$userId = $_SESSION['user_id'];
$db = getDB();
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Get counts for sidebar badges
$totalClients = $db->query("SELECT COUNT(*) FROM coaches_clients WHERE coach_id = $userId")->fetch_row()[0] ?? 0;
$pendingWorkouts = $db->query("
    SELECT COUNT(*) FROM client_workouts 
    WHERE client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND status = 'scheduled' AND start_date <= CURDATE()
")->fetch_row()[0] ?? 0;
$pendingPayments = $db->query("
    SELECT COALESCE(SUM(amount), 0) FROM payments 
    WHERE client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND status = 'pending'
")->fetch_row()[0] ?? 0;
$newLeads = $db->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetch_row()[0] ?? 0;
$unreadMessages = 3; // Placeholder
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard - FitCoach Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary-color: #4A6FA5;
            --secondary-color: #166088;
            --accent-color: #17a2b8;
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
            --accent-color: #20c997;
            --light-bg: #121212;
            --dark-bg: #0a0a0a;
            --card-bg: #1e1e1e;
            --text-color: #f8f9fa;
            --text-light: #adb5bd;
        }
        body { background-color: var(--light-bg); color: var(--text-color); transition: all 0.3s ease; overflow-x: hidden; }
        .coach-sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: linear-gradient(180deg, var(--primary-color), var(--secondary-color)); color: white; z-index: 1000; box-shadow: 3px 0 15px rgba(0,0,0,0.1); transition: transform 0.3s ease; display: flex; flex-direction: column; }
        .coach-brand { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); flex-shrink: 0; }
        .coach-avatar { width: 80px; height: 80px; border-radius: 50%; background: white; color: var(--primary-color); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 15px; border: 4px solid rgba(255,255,255,0.3); }
        .menu-wrapper { flex: 1; overflow-y: auto; overflow-x: hidden; min-height: 0; }
        .menu-wrapper::-webkit-scrollbar { width: 5px; }
        .menu-wrapper::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .menu-wrapper::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.4); border-radius: 10px; }
        .coach-menu { list-style: none; padding: 10px 15px; margin: 0; }
        .coach-menu li { margin-bottom: 5px; }
        .coach-menu a { color: rgba(255,255,255,0.9); text-decoration: none; padding: 12px 15px; display: flex; align-items: center; gap: 12px; transition: all 0.3s; border-radius: 10px; border-left: 3px solid transparent; }
        .coach-menu a:hover, .coach-menu a.active { background: rgba(255,255,255,0.15); color: white; border-left-color: white; }
        .coach-menu .badge { margin-left: auto; background: rgba(255,255,255,0.2); }
        .sidebar-footer { padding: 15px; text-align: center; border-top: 1px solid rgba(255,255,255,0.1); flex-shrink: 0; font-size: 0.85rem; }
        .coach-main { margin-left: var(--sidebar-width); padding: 20px; min-height: 100vh; transition: margin-left 0.3s ease; }
        .coach-header { background: var(--card-bg); padding: 20px 25px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .header-title h1 { color: var(--primary-color); margin: 0; font-size: 1.8rem; }
        .header-title p { color: var(--text-light); margin: 5px 0 0 0; }
        .header-actions { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .page-title { color: var(--primary-color); font-weight: 600; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .quick-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: var(--card-bg); border-radius: 12px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.1); transition: transform 0.3s; }
        .stat-box:hover { transform: translateY(-5px); }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 15px; }
        .stat-value { font-size: 2.2rem; font-weight: bold; margin-bottom: 5px; }
        .stat-label { color: var(--text-light); font-size: 0.9rem; }
        .dashboard-section { margin-bottom: 30px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .section-title { color: var(--primary-color); font-weight: 600; font-size: 1.3rem; display: flex; align-items: center; gap: 10px; }
        .clients-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .client-card { background: var(--card-bg); border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.1); transition: all 0.3s; }
        .client-card:hover { transform: translateY(-5px); }
        .client-header { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid rgba(0,0,0,0.1); }
        .client-avatar { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; }
        .client-metrics { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px; }
        .metric-item { text-align: center; padding: 10px; background: var(--light-bg); border-radius: 8px; }
        .metric-value { font-weight: bold; font-size: 1.1rem; display: block; }
        .metric-label { font-size: 0.8rem; color: var(--text-light); }
        .client-status { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .status-excellent { background: rgba(40,167,69,0.1); color: var(--success-color); }
        .status-good { background: rgba(23,162,184,0.1); color: var(--info-color); }
        .status-warning { background: rgba(255,193,7,0.1); color: var(--warning-color); }
        .workout-planning { background: var(--card-bg); border-radius: 15px; padding: 25px; margin-bottom: 30px; border: 1px solid rgba(0,0,0,0.1); }
        .exercise-library { max-height: 300px; overflow-y: auto; border: 1px solid rgba(0,0,0,0.1); border-radius: 10px; padding: 15px; }
        .exercise-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .nutrition-card { background: var(--card-bg); border-radius: 15px; padding: 25px; border: 1px solid rgba(0,0,0,0.1); height: 100%; }
        .macro-targets { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .macro-box { text-align: center; padding: 15px; border-radius: 10px; background: var(--light-bg); }
        .macro-value { font-size: 1.5rem; font-weight: bold; display: block; }
        .chart-container { background: var(--card-bg); border-radius: 15px; padding: 25px; margin-bottom: 30px; border: 1px solid rgba(0,0,0,0.1); height: 350px; }
        .packages-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .package-card { background: var(--card-bg); border-radius: 15px; padding: 25px; border: 1px solid rgba(0,0,0,0.1); border-top: 5px solid var(--primary-color); }
        .data-table { background: var(--card-bg); border-radius: 15px; padding: 20px; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.1); }
        .table th { border-bottom: 2px solid var(--primary-color); }
        .btn-coach { background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); border: none; padding: 10px 25px; border-radius: 10px; color: white; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-coach:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(74,111,165,0.3); color: white; }
        .btn-outline-coach { background: transparent; border: 2px solid var(--primary-color); color: var(--primary-color); padding: 8px 20px; border-radius: 10px; transition: all 0.3s; }
        .btn-outline-coach:hover { background: var(--primary-color); color: white; }
        .theme-toggle { position: fixed; bottom: 20px; right: 20px; z-index: 1000; background: var(--primary-color); color: white; border: none; width: 50px; height: 50px; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .form-control, .form-select, textarea { background-color: var(--light-bg); border: 2px solid #e0e0e0; border-radius: 10px; padding: 12px 15px; color: var(--text-color); }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select, [data-bs-theme="dark"] textarea { background-color: #2d2d2d; border-color: #444; }
        .progress { height: 8px; border-radius: 4px; background: var(--light-bg); }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .menu-toggle { display: none; background: none; border: none; color: var(--text-color); font-size: 1.5rem; cursor: pointer; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; display: none; }
        .sidebar-overlay.active { display: block; }
        @media (max-width: 992px) {
            .coach-sidebar { transform: translateX(-100%); }
            .coach-sidebar.open { transform: translateX(0); }
            .coach-main { margin-left: 0; }
            .menu-toggle { display: block !important; }
            .clients-grid { grid-template-columns: 1fr; }
            .quick-stats { grid-template-columns: 1fr; }
            .macro-targets { grid-template-columns: repeat(2, 1fr); }
            .packages-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 576px) {
            .chart-container { height: 280px; }
            .header-actions { width: 100%; }
            .header-actions .input-group { width: 100% !important; }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="coach-sidebar" id="coachSidebar">
        <div class="coach-brand">
            <div class="coach-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
            <h5 class="mb-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
            <p class="small opacity-75 mb-0">Certified Personal Trainer</p>
            <span class="badge bg-light text-dark mt-2">Premium Coach</span>
        </div>
        <div class="menu-wrapper">
            <ul class="coach-menu">
                <li><a href="index.php" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="clients.php" class="<?php echo $current_page == 'clients' ? 'active' : ''; ?>"><i class="bi bi-people"></i> Clients <span class="badge"><?php echo $totalClients; ?></span></a></li>
                <li><a href="leads.php" class="<?php echo $current_page == 'leads' ? 'active' : ''; ?>"><i class="bi bi-person-plus"></i> Applications <span class="badge"><?php echo $newLeads; ?></span></a></li>
                <li><a href="workouts.php" class="<?php echo $current_page == 'workouts' ? 'active' : ''; ?>"><i class="bi bi-activity"></i> Workouts <?php if ($pendingWorkouts > 0): ?><span class="badge bg-danger"><?php echo $pendingWorkouts; ?></span><?php endif; ?></a></li>
                <li><a href="nutrition.php" class="<?php echo $current_page == 'nutrition' ? 'active' : ''; ?>"><i class="bi bi-egg-fried"></i> Nutrition</a></li>
                <li><a href="progress.php" class="<?php echo $current_page == 'progress' ? 'active' : ''; ?>"><i class="bi bi-graph-up"></i> Progress</a></li>
                <li><a href="fitness-tests.php" class="<?php echo $current_page == 'fitness-tests' ? 'active' : ''; ?>"><i class="bi bi-clipboard-check"></i> Fitness Tests</a></li>
                <li><a href="packages.php" class="<?php echo $current_page == 'packages' ? 'active' : ''; ?>"><i class="bi bi-box-seam"></i> Packages</a></li>
                <li><a href="payments.php" class="<?php echo $current_page == 'payments' ? 'active' : ''; ?>"><i class="bi bi-cash"></i> Payments <?php if ($pendingPayments > 0): ?><span class="badge bg-warning">$<?php echo number_format($pendingPayments); ?></span><?php endif; ?></a></li>
                <li><a href="analytics.php" class="<?php echo $current_page == 'analytics' ? 'active' : ''; ?>"><i class="bi bi-bar-chart"></i> Analytics</a></li>
                <li><a href="editors.php" class="<?php echo $current_page == 'editors' ? 'active' : ''; ?>"><i class="bi bi-person-plus"></i> Editors</a></li>
                <li><a href="schedule.php" class="<?php echo $current_page == 'schedule' ? 'active' : ''; ?>"><i class="bi bi-calendar-check"></i> Schedule</a></li>
                <li><a href="reports.php" class="<?php echo $current_page == 'reports' ? 'active' : ''; ?>"><i class="bi bi-file-text"></i> Reports</a></li>
                <li><a href="messages.php" class="<?php echo $current_page == 'messages' ? 'active' : ''; ?>"><i class="bi bi-messenger"></i> Messages <?php if ($unreadMessages > 0): ?><span class="badge bg-danger"><?php echo $unreadMessages; ?></span><?php endif; ?></a></li>
                <li class="mt-4"><a href="/appF/home/index.php"><i class="bi bi-globe"></i> Public Website</a></li>
                <li><a href="/appF/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <small class="opacity-75">Weekly Revenue: $1,850</small>
            <div class="progress mt-2" style="height: 5px;"><div class="progress-bar bg-success" style="width: 75%"></div></div>
        </div>
    </div>
    <div class="coach-main">
        <div class="coach-header">
            <button class="menu-toggle" id="menuToggle"><i class="bi bi-list"></i></button>
            <div class="header-title">
                <h1>Coach Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            </div>
            <div class="header-actions">
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control" placeholder="Search clients...">
                    <button class="btn btn-coach"><i class="bi bi-search"></i></button>
                </div>
                <button class="btn btn-coach" data-bs-toggle="modal" data-bs-target="#newClientModal"><i class="bi bi-plus-circle"></i> New Client</button>
                <div class="dropdown">
                    <button class="btn btn-outline-coach dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-bell"></i><span class="badge bg-danger">3</span></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">New lead from website</a></li>
                        <li><a class="dropdown-item" href="#">Client missed workout</a></li>
                        <li><a class="dropdown-item" href="#">Payment due reminder</a></li>
                    </ul>
                </div>
            </div>
        </div>