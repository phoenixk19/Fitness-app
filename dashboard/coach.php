<?php
// File: C:\xampp\htdocs\appF\dashboard\coach.php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/middleware.php';
require_once '../includes/roles.php';

// Apply middleware - only coach can access
applyMiddleware(['authMiddleware', 'noCacheMiddleware'], function() {
    roleMiddleware(['coach']);
});

$userId = $_SESSION['user_id'];
$db = getDB();

// Get coach's clients
$clients = [];
$clientsResult = $db->query("
    SELECT u.id, u.name, u.email, u.phone, 
           (SELECT weight FROM body_measurements WHERE client_id = u.id ORDER BY measurement_date DESC LIMIT 1) as current_weight,
           (SELECT COUNT(*) FROM client_workouts WHERE client_id = u.id AND status = 'completed') as workouts_completed,
           (SELECT COUNT(*) FROM meal_logs WHERE client_id = u.id AND meal_date = CURDATE()) as meals_today
    FROM users u
    JOIN coaches_clients cc ON u.id = cc.client_id
    WHERE cc.coach_id = $userId AND u.status = 'active'
");

if ($clientsResult) {
    $clients = $clientsResult->fetch_all(MYSQLI_ASSOC);
}

// Get stats
$totalClients = count($clients);

$completionRate = 0;
$completionResult = $db->query("
    SELECT ROUND(AVG(CASE WHEN status = 'completed' THEN 100 ELSE 0 END), 1) as completion_rate
    FROM client_workouts
    WHERE client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND start_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
if ($completionResult && $completionResult->num_rows > 0) {
    $completionRate = $completionResult->fetch_assoc()['completion_rate'] ?? 0;
}

$pendingCount = 0;
$pendingResult = $db->query("
    SELECT COUNT(*) as pending
    FROM client_workouts
    WHERE client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND status = 'scheduled'
    AND start_date <= CURDATE()
");
if ($pendingResult && $pendingResult->num_rows > 0) {
    $pendingCount = $pendingResult->fetch_assoc()['pending'] ?? 0;
}

$pendingTotal = 0;
$paymentResult = $db->query("
    SELECT COALESCE(SUM(p.amount), 0) as total
    FROM payments p
    WHERE p.client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND p.status = 'pending'
");
if ($paymentResult && $paymentResult->num_rows > 0) {
    $pendingTotal = $paymentResult->fetch_assoc()['total'] ?? 0;
}

// Get program distribution
$programLabels = ['No Data'];
$programCounts = [1];
$programResult = $db->query("
    SELECT 
        CASE 
            WHEN goal = 'weight-loss' THEN 'Weight Loss'
            WHEN goal = 'muscle-gain' THEN 'Muscle Gain'
            WHEN goal = 'general-fitness' THEN 'General Fitness'
            WHEN goal = 'sports-specific' THEN 'Sports'
            ELSE 'Other'
        END as program,
        COUNT(*) as count
    FROM leads l
    WHERE l.converted_to_client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    GROUP BY program
");
if ($programResult && $programResult->num_rows > 0) {
    $programData = $programResult->fetch_all(MYSQLI_ASSOC);
    $programLabels = array_column($programData, 'program');
    $programCounts = array_column($programData, 'count');
}

// Get weekly progress data
$weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$weekCompletion = array_fill(0, 7, 0);

$weeklyResult = $db->query("
    SELECT 
        DATE_FORMAT(start_date, '%a') as day,
        ROUND(AVG(CASE WHEN status = 'completed' THEN 100 ELSE 0 END), 1) as completion_rate
    FROM client_workouts
    WHERE client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND start_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(start_date)
    ORDER BY start_date
");
if ($weeklyResult && $weeklyResult->num_rows > 0) {
    $weeklyData = $weeklyResult->fetch_all(MYSQLI_ASSOC);
    foreach ($weeklyData as $data) {
        $dayIndex = array_search($data['day'], $weekDays);
        if ($dayIndex !== false) {
            $weekCompletion[$dayIndex] = (float)$data['completion_rate'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard - FitCoach Pro</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* SIDEBAR - FIXED SCROLLABLE VERSION */
        .coach-sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            color: white;
            z-index: 1000;
            box-shadow: 3px 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .coach-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            flex-shrink: 0;
        }

        .coach-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 15px;
            border: 4px solid rgba(255,255,255,0.3);
        }

        /* SCROLLABLE MENU */
        .menu-wrapper {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
        }

        .menu-wrapper::-webkit-scrollbar {
            width: 5px;
        }

        .menu-wrapper::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }

        .menu-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.4);
            border-radius: 10px;
        }

        .menu-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.6);
        }

        .coach-menu {
            list-style: none;
            padding: 10px 15px;
            margin: 0;
        }

        .coach-menu li {
            margin-bottom: 5px;
        }

        .coach-menu a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
            border-radius: 10px;
            border-left: 3px solid transparent;
        }

        .coach-menu a:hover,
        .coach-menu a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: white;
        }

        .coach-menu .badge {
            margin-left: auto;
            background: rgba(255,255,255,0.2);
        }

        .sidebar-footer {
            padding: 15px;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
            flex-shrink: 0;
            font-size: 0.85rem;
        }

        /* Main Content */
        .coach-main {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .coach-header {
            background: var(--card-bg);
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-title h1 {
            color: var(--primary-color);
            margin: 0;
            font-size: 1.8rem;
        }

        .header-title p {
            color: var(--text-light);
            margin: 5px 0 0 0;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-box:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .dashboard-section {
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .clients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .client-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s;
            position: relative;
        }

        .client-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .client-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .client-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .client-info h5 {
            margin: 0 0 5px 0;
        }

        .client-goal {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .client-metrics {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }

        .metric-item {
            text-align: center;
            padding: 10px;
            background: var(--light-bg);
            border-radius: 8px;
        }

        .metric-value {
            font-weight: bold;
            font-size: 1.1rem;
            display: block;
        }

        .metric-label {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .client-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-excellent { background: rgba(40, 167, 69, 0.1); color: var(--success-color); }
        .status-good { background: rgba(23, 162, 184, 0.1); color: var(--info-color); }
        .status-warning { background: rgba(255, 193, 7, 0.1); color: var(--warning-color); }
        .status-danger { background: rgba(220, 53, 69, 0.1); color: var(--danger-color); }

        .workout-planning {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .exercise-library {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 15px;
        }

        .exercise-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .exercise-item:last-child {
            border-bottom: none;
        }

        .nutrition-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            height: 100%;
        }

        .macro-targets {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .macro-box {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            background: var(--light-bg);
        }

        .macro-value {
            font-size: 1.5rem;
            font-weight: bold;
            display: block;
        }

        .chart-container {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            height: 350px;
        }

        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .package-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            border-top: 5px solid var(--primary-color);
        }

        .package-theme {
            width: 100%;
            height: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .btn-coach {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            color: white;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-coach:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 111, 165, 0.3);
            color: white;
        }

        .btn-outline-coach {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-outline-coach:hover {
            background: var(--primary-color);
            color: white;
        }

        .theme-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--primary-color);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .form-control, .form-select, textarea {
            background-color: var(--light-bg);
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            color: var(--text-color);
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] textarea {
            background-color: #2d2d2d;
            border-color: #444;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background: var(--light-bg);
        }

        .progress-bar {
            border-radius: 4px;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }

        .sidebar-overlay.active {
            display: block;
        }

        @media (max-width: 992px) {
            .coach-sidebar {
                transform: translateX(-100%);
            }
            
            .coach-sidebar.open {
                transform: translateX(0);
            }
            
            .coach-main {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block !important;
            }
            
            .clients-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-stats {
                grid-template-columns: 1fr;
            }
            
            .macro-targets {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .packages-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .chart-container {
                height: 280px;
            }
            
            .header-actions {
                width: 100%;
            }
            
            .header-actions .input-group {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="coach-sidebar" id="coachSidebar">
        <div class="coach-brand">
            <div class="coach-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
            <h5 class="mb-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
            <p class="small opacity-75 mb-0">Certified Personal Trainer</p>
            <span class="badge bg-light text-dark mt-2">Premium Coach</span>
        </div>

        <!-- SCROLLABLE MENU WRAPPER -->
        <div class="menu-wrapper">
            <ul class="coach-menu">
                <li><a href="#" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="#"><i class="bi bi-people"></i> Clients <span class="badge"><?php echo $totalClients; ?></span></a></li>
                <li><a href="#"><i class="bi bi-activity"></i> Workouts <?php if ($pendingCount > 0): ?><span class="badge bg-danger"><?php echo $pendingCount; ?></span><?php endif; ?></a></li>
                <li><a href="#"><i class="bi bi-egg-fried"></i> Nutrition</a></li>
                <li><a href="#"><i class="bi bi-graph-up"></i> Progress</a></li>
                <li><a href="#"><i class="bi bi-clipboard-check"></i> Fitness Tests</a></li>
                <li><a href="#"><i class="bi bi-box-seam"></i> Packages <span class="badge">5 active</span></a></li>
                <li><a href="#"><i class="bi bi-cash"></i> Payments <?php if ($pendingTotal > 0): ?><span class="badge bg-warning">$<?php echo number_format($pendingTotal); ?></span><?php endif; ?></a></li>
                <li><a href="#"><i class="bi bi-bar-chart"></i> Analytics</a></li>
                <li><a href="#"><i class="bi bi-person-plus"></i> Editors <span class="badge">2 assistants</span></a></li>
                <li><a href="#"><i class="bi bi-calendar-check"></i> Schedule</a></li>
                <li><a href="#"><i class="bi bi-file-text"></i> Reports</a></li>
                <li><a href="#"><i class="bi bi-messenger"></i> Messages <span class="badge bg-danger">3</span></a></li>
                <li class="mt-3"><a href="/appF/index.php"><i class="bi bi-globe"></i> Public Website</a></li>
                <li><a href="/appF/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <small class="opacity-75">Weekly Revenue: $1,850</small>
            <div class="progress mt-2" style="height: 5px;"><div class="progress-bar bg-success" style="width: 75%"></div></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="coach-main">
        <div class="coach-header">
            <button class="menu-toggle" id="menuToggle"><i class="bi bi-list"></i></button>
            <div class="header-title">
                <h1>Coach Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Here's your coaching overview for today.</p>
            </div>
            <div class="header-actions">
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control" placeholder="Search clients, workouts...">
                    <button class="btn btn-coach"><i class="bi bi-search"></i></button>
                </div>
                <button class="btn btn-coach" data-bs-toggle="modal" data-bs-target="#newClientModal"><i class="bi bi-plus-circle"></i> New Client</button>
                <div class="dropdown">
                    <button class="btn btn-outline-coach dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="badge bg-danger">3</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">New lead from website</a></li>
                        <li><a class="dropdown-item" href="#">Client missed workout</a></li>
                        <li><a class="dropdown-item" href="#">Payment due reminder</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="quick-stats">
            <div class="stat-box"><div class="stat-icon" style="background: rgba(74,111,165,0.1); color:var(--primary-color);"><i class="bi bi-people"></i></div><div class="stat-value"><?php echo $totalClients; ?></div><div class="stat-label">Active Clients</div></div>
            <div class="stat-box"><div class="stat-icon" style="background: rgba(40,167,69,0.1); color:var(--success-color);"><i class="bi bi-check-circle"></i></div><div class="stat-value"><?php echo $completionRate; ?>%</div><div class="stat-label">Workout Completion</div></div>
            <div class="stat-box"><div class="stat-icon" style="background: rgba(255,193,7,0.1); color:var(--warning-color);"><i class="bi bi-clock-history"></i></div><div class="stat-value"><?php echo $pendingCount; ?></div><div class="stat-label">Pending Workouts</div></div>
            <div class="stat-box"><div class="stat-icon" style="background: rgba(220,53,69,0.1); color:var(--danger-color);"><i class="bi bi-currency-dollar"></i></div><div class="stat-value">$<?php echo number_format($pendingTotal); ?></div><div class="stat-label">Pending Payments</div></div>
        </div>

        <div class="dashboard-section">
            <div class="section-header"><div class="section-title"><i class="bi bi-people"></i><h3 class="mb-0">Client Management</h3></div><div><button class="btn btn-outline-coach"><i class="bi bi-filter"></i> Filter</button><button class="btn btn-outline-coach"><i class="bi bi-download"></i> Export</button></div></div>
            <div class="clients-grid">
                <?php foreach ($clients as $client): ?>
                <div class="client-card">
                    <div class="client-header"><div class="client-avatar"><?php echo strtoupper(substr($client['name'], 0, 2)); ?></div><div class="client-info"><h5><?php echo htmlspecialchars($client['name']); ?></h5><div class="client-goal"><?php echo $client['current_weight'] ? $client['current_weight'] . ' kg' : 'No data'; ?></div><span class="client-status status-good">Active</span></div></div>
                    <div class="client-metrics"><div class="metric-item"><span class="metric-value"><?php echo $client['workouts_completed'] ?? 0; ?></span><span class="metric-label">Workouts</span></div><div class="metric-item"><span class="metric-value"><?php echo $client['meals_today'] ?? 0; ?></span><span class="metric-label">Meals Today</span></div></div>
                    <div class="d-flex justify-content-between mt-3"><button class="btn btn-sm btn-outline-coach"><i class="bi bi-activity"></i> Workout</button><button class="btn btn-sm btn-outline-coach"><i class="bi bi-chat"></i> Message</button><button class="btn btn-sm btn-coach"><i class="bi bi-graph-up"></i> Progress</button></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                <div class="client-card text-center"><p class="text-muted mb-0">No clients assigned yet.</p><button class="btn btn-coach mt-3" data-bs-toggle="modal" data-bs-target="#newClientModal"><i class="bi bi-plus-circle"></i> Add Your First Client</button></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8"><div class="chart-container"><div class="section-header mb-3"><div class="section-title"><i class="bi bi-graph-up"></i><h4 class="mb-0">Weekly Workout Completion</h4></div></div><canvas id="progressChart"></canvas></div></div>
            <div class="col-lg-4"><div class="chart-container"><div class="section-title mb-3"><i class="bi bi-pie-chart"></i><h4 class="mb-0">Program Distribution</h4></div><canvas id="programChart"></canvas></div></div>
        </div>

        <div class="workout-planning">
            <div class="section-header mb-4"><div class="section-title"><i class="bi bi-activity"></i><h3 class="mb-0">Workout Planning</h3></div><button class="btn btn-coach"><i class="bi bi-plus-circle"></i> Create Template</button></div>
            <div class="row">
                <div class="col-md-6"><h5>Exercise Library</h5><div class="exercise-library"><div class="exercise-item"><div><strong>Bench Press</strong><div class="small text-muted">Chest • Barbell</div></div><button class="btn btn-sm btn-coach">Add</button></div><div class="exercise-item"><div><strong>Squat</strong><div class="small text-muted">Legs • Barbell</div></div><button class="btn btn-sm btn-coach">Add</button></div><div class="exercise-item"><div><strong>Deadlift</strong><div class="small text-muted">Back • Barbell</div></div><button class="btn btn-sm btn-coach">Add</button></div><div class="exercise-item"><div><strong>Pull-ups</strong><div class="small text-muted">Back • Bodyweight</div></div><button class="btn btn-sm btn-coach">Add</button></div></div></div>
                <div class="col-md-6"><h5>Create Workout Group</h5><div class="mb-3"><label class="form-label">Workout Name</label><input type="text" class="form-control" placeholder="e.g., Push Day, Leg Day"></div><div class="mb-3"><label class="form-label">Training Type</label><select class="form-select"><option>Strength</option><option>Hypertrophy</option><option>Endurance</option><option>Power</option></select></div><div class="mb-3"><label class="form-label">Assign to Client</label><select class="form-select" multiple><?php foreach ($clients as $client): ?><option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option><?php endforeach; ?></select></div><button class="btn btn-coach w-100"><i class="bi bi-save"></i> Save Workout Template</button></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6"><div class="nutrition-card"><div class="section-header mb-4"><div class="section-title"><i class="bi bi-egg-fried"></i><h4 class="mb-0">Nutrition Management</h4></div><button class="btn btn-coach btn-sm"><i class="bi bi-gear"></i> Adjust Targets</button></div><div class="macro-targets"><div class="macro-box" style="border-left:4px solid #4A6FA5;"><span class="macro-value">2,150</span><small>Calories</small></div><div class="macro-box" style="border-left:4px solid #28a745;"><span class="macro-value">150g</span><small>Protein</small></div><div class="macro-box" style="border-left:4px solid #ffc107;"><span class="macro-value">250g</span><small>Carbs</small></div><div class="macro-box" style="border-left:4px solid #dc3545;"><span class="macro-value">65g</span><small>Fat</small></div></div><div class="alert alert-info mt-3"><small><i class="bi bi-info-circle"></i> TDEE calculated based on client metrics</small></div></div></div>
            <div class="col-lg-6"><div class="nutrition-card"><div class="section-header mb-4"><div class="section-title"><i class="bi bi-box-seam"></i><h4 class="mb-0">Active Packages</h4></div><button class="btn btn-coach btn-sm"><i class="bi bi-plus"></i> Create</button></div><div class="packages-grid"><div class="package-card"><div class="package-theme" style="background:linear-gradient(45deg,#4A6FA5,#166088);"></div><h5>Weight Loss Elite</h5><p class="small text-muted">12-week transformation</p><ul class="small mb-3"><li>3 weekly workouts</li><li>Nutrition plan</li><li>Weekly check-ins</li></ul><div class="d-flex justify-content-between"><span class="fw-bold">$299/mo</span><span class="badge bg-success"><?php echo $totalClients; ?> clients</span></div></div><div class="package-card"><div class="package-theme" style="background:linear-gradient(45deg,#28a745,#20c997);"></div><h5>Muscle Building Pro</h5><p class="small text-muted">Advanced hypertrophy</p><ul class="small mb-3"><li>4 weekly workouts</li><li>Macro tracking</li><li>Progress photos</li></ul><div class="d-flex justify-content-between"><span class="fw-bold">$349/mo</span><span class="badge bg-primary">2 clients</span></div></div></div></div></div>
        </div>
    </div>

    <div class="modal fade" id="newClientModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Add New Client</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="../api/clients.php" method="POST"><input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>"><div class="modal-body"><div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div><div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div><div class="mb-3"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div><div class="mb-3"><label class="form-label">Initial Goal</label><select name="goal" class="form-select"><option value="weight-loss">Weight Loss</option><option value="muscle-gain">Muscle Gain</option><option value="general-fitness">General Fitness</option><option value="sports-specific">Sports Specific</option></select></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-coach">Add Client</button></div></form></div></div></div>

    <button class="theme-toggle" id="themeToggle"><i class="bi bi-moon-fill" id="themeIcon"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle'), themeIcon = document.getElementById('themeIcon'), htmlElement = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        function updateThemeIcon(theme) { themeIcon.className = theme === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill'; }
        updateThemeIcon(savedTheme);
        themeToggle.addEventListener('click', () => { const newTheme = htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light'; htmlElement.setAttribute('data-bs-theme', newTheme); localStorage.setItem('theme', newTheme); updateThemeIcon(newTheme); });

        // Sidebar Toggle
        const menuToggle = document.getElementById('menuToggle'), coachSidebar = document.getElementById('coachSidebar'), sidebarOverlay = document.getElementById('sidebarOverlay');
        function closeSidebar() { if (coachSidebar) coachSidebar.classList.remove('open'); if (sidebarOverlay) sidebarOverlay.classList.remove('active'); }
        function openSidebar() { if (coachSidebar) coachSidebar.classList.add('open'); if (sidebarOverlay) sidebarOverlay.classList.add('active'); }
        if (menuToggle) menuToggle.addEventListener('click', (e) => { e.stopPropagation(); coachSidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
        window.addEventListener('resize', () => { if (window.innerWidth > 992) closeSidebar(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && coachSidebar && coachSidebar.classList.contains('open')) closeSidebar(); });

        // Charts
        new Chart(document.getElementById('progressChart'), { type: 'line', data: { labels: <?php echo json_encode($weekDays); ?>, datasets: [{ label: 'Workout Completion %', data: <?php echo json_encode($weekCompletion); ?>, borderColor: 'rgba(74,111,165,1)', backgroundColor: 'rgba(74,111,165,0.1)', borderWidth: 3, fill: true, tension: 0.4 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } } } });
        new Chart(document.getElementById('programChart'), { type: 'doughnut', data: { labels: <?php echo json_encode($programLabels); ?>, datasets: [{ data: <?php echo json_encode($programCounts); ?>, backgroundColor: ['rgba(74,111,165,0.8)','rgba(40,167,69,0.8)','rgba(255,193,7,0.8)','rgba(220,53,69,0.8)'], borderWidth: 2 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '65%' } });
    </script>
</body>
</html>