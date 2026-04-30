<?php
// File: C:\xampp\htdocs\appF\dashboard\admin.php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/middleware.php';
require_once '../includes/roles.php';

// Apply middleware - only admin can access
applyMiddleware(['authMiddleware', 'noCacheMiddleware'], function() {
    roleMiddleware(['admin']);
});

// Get dashboard data
$db = getDB();

// Get counts
$totalCoaches = $db->query("SELECT COUNT(*) FROM users WHERE role = 'coach'")->fetch_row()[0] ?? 0;
$totalClients = $db->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetch_row()[0] ?? 0;
$newLeads = $db->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetch_row()[0] ?? 0;
$monthlyRevenue = $db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE MONTH(payment_date) = MONTH(NOW()) AND YEAR(payment_date) = YEAR(NOW())")->fetch_row()[0] ?? 0;

// Get recent activity
$activityResult = $db->query("
    SELECT al.*, u.name as user_name 
    FROM activity_logs al 
    LEFT JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 10
");
$recentActivity = $activityResult ? $activityResult->fetch_all(MYSQLI_ASSOC) : [];

// Get recent leads
$leadsResult = $db->query("
    SELECT * FROM leads 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recentLeads = $leadsResult ? $leadsResult->fetch_all(MYSQLI_ASSOC) : [];

// Get monthly revenue data for chart
$revenueResult = $db->query("
    SELECT 
        MONTH(payment_date) as month,
        SUM(amount) as total
    FROM payments 
    WHERE YEAR(payment_date) = YEAR(NOW())
    GROUP BY MONTH(payment_date)
    ORDER BY month
");
$revenueData = $revenueResult ? $revenueResult->fetch_all(MYSQLI_ASSOC) : [];

// Prepare chart data
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$revenueValues = array_fill(0, 12, 0);

foreach ($revenueData as $data) {
    $revenueValues[$data['month'] - 1] = (float)$data['total'];
}

// Get client distribution
$distResult = $db->query("
    SELECT 
        CASE 
            WHEN goal = 'weight-loss' THEN 'Weight Loss'
            WHEN goal = 'muscle-gain' THEN 'Muscle Gain'
            WHEN goal = 'general-fitness' THEN 'General Fitness'
            WHEN goal = 'sports-specific' THEN 'Sports'
            ELSE 'Other'
        END as program,
        COUNT(*) as count
    FROM leads 
    WHERE converted_to_client_id IS NOT NULL
    GROUP BY program
");
$clientDistribution = $distResult ? $distResult->fetch_all(MYSQLI_ASSOC) : [];

$programLabels = [];
$programCounts = [];

foreach ($clientDistribution as $dist) {
    $programLabels[] = $dist['program'];
    $programCounts[] = (int)$dist['count'];
}

// If no data, show default
if (empty($programLabels)) {
    $programLabels = ['No Data'];
    $programCounts = [1];
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FitCoach Pro</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
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
            --sidebar-width: 250px;
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
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding-top: 20px;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 0 1rem 2rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: white;
        }

        .sidebar-menu .badge {
            margin-left: auto;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        .top-navbar {
            background: var(--card-bg);
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .search-box {
            max-width: 400px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Cards */
        .stat-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Charts */
        .chart-container {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            height: 450px;
            position: relative;
        }

        .chart-container h5 {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0,0,0,0.1);
            color: var(--primary-color);
            font-weight: 600;
        }

        .chart-wrapper {
            width: 100%;
            height: calc(100% - 60px);
            position: relative;
        }

        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* Tables */
        .data-table {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--text-color);
            border-bottom: 2px solid var(--primary-color);
        }

        .table td {
            vertical-align: middle;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-active {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .action-btn {
            flex: 1;
            min-width: 200px;
            background: var(--card-bg);
            border: 2px dashed var(--accent-color);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            color: var(--accent-color);
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .action-btn:hover {
            background: var(--accent-color);
            color: white;
            transform: translateY(-3px);
        }

        /* Theme Toggle */
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

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block !important;
            }
            
            .chart-container {
                height: 350px;
            }
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 1.5rem;
        }

        .page-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-control, .form-select {
            background-color: var(--light-bg);
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            color: var(--text-color);
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #2d2d2d;
            border-color: #444;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 500;
            color: white;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-details {
            line-height: 1.2;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .user-role {
            font-size: 0.85rem;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-activity me-2"></i> FitCoach Pro</h4>
            <p class="mb-0 small opacity-75">Admin Panel</p>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="#" class="active">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-people"></i> Coaches
                    <span class="badge bg-light text-dark"><?php echo $totalCoaches; ?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-person-plus"></i> Leads
                    <?php if ($newLeads > 0): ?>
                        <span class="badge bg-danger notification-badge"><?php echo $newLeads; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-box-seam"></i> Packages
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-cash"></i> Payments
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-bar-chart"></i> Analytics
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-palette"></i> Branding
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-headset"></i> Support
                </a>
            </li>
            <li class="mt-4">
                <a href="/appF/index.php">
                    <i class="bi bi-globe"></i> Public Website
                </a>
            </li>
            <li>
                <a href="/appF/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer mt-auto p-3">
            <div class="d-flex align-items-center">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
                <div class="ms-2">
                    <div class="small fw-bold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div class="x-small opacity-75">Administrator</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <button class="menu-toggle" id="menuToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="search-box">
                <input type="text" class="form-control" placeholder="Search users, packages, reports...">
            </div>
            
            <div class="user-profile">
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
                </div>
            </div>
        </div>

        <!-- Page Title -->
        <div class="page-title">
            <i class="bi bi-speedometer2"></i>
            <h2 class="mb-0">Admin Dashboard</h2>
            <span class="badge bg-primary ms-2">System Overview</span>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="#" class="action-btn" data-bs-toggle="modal" data-bs-target="#addCoachModal">
                <i class="bi bi-plus-circle fs-3"></i>
                <span>Add New Coach</span>
            </a>
            <a href="#" class="action-btn" data-bs-toggle="modal" data-bs-target="#editWebsiteModal">
                <i class="bi bi-pencil-square fs-3"></i>
                <span>Edit Website</span>
            </a>
            <a href="#" class="action-btn">
                <i class="bi bi-cash-coin fs-3"></i>
                <span>View Revenue</span>
            </a>
            <a href="#" class="action-btn">
                <i class="bi bi-file-earmark-text fs-3"></i>
                <span>Generate Reports</span>
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(74, 111, 165, 0.1); color: var(--primary-color);">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-number"><?php echo $totalClients; ?></div>
                    <div class="stat-label">Total Clients</div>
                    <div class="small mt-2 text-success">
                        <i class="bi bi-arrow-up"></i> Active clients
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(40, 167, 69, 0.1); color: var(--success-color);">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="stat-number"><?php echo $totalCoaches; ?></div>
                    <div class="stat-label">Active Coaches</div>
                    <div class="small mt-2 text-muted">
                        Managing clients
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: var(--warning-color);">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div class="stat-number"><?php echo $newLeads; ?></div>
                    <div class="stat-label">New Leads</div>
                    <div class="small mt-2 text-warning">
                        Need follow-up
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(220, 53, 69, 0.1); color: var(--danger-color);">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-number">$<?php echo number_format($monthlyRevenue); ?></div>
                    <div class="stat-label">Monthly Revenue</div>
                    <div class="small mt-2 text-success">
                        <i class="bi bi-arrow-up"></i> This month
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-lg-8">
                <div class="chart-container">
                    <h5 class="mb-3">Monthly Revenue Trend</h5>
                    <div class="chart-wrapper">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-container">
                    <h5 class="mb-3">Client Distribution</h5>
                    <div class="chart-wrapper">
                        <canvas id="clientChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Leads -->
        <div class="row">
            <div class="col-lg-8">
                <div class="data-table">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Recent Activity</h5>
                        <a href="#" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <table>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentActivity as $activity): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                <?php echo $activity['user_name'] ? strtoupper(substr($activity['user_name'], 0, 2)) : '?'; ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($activity['details'] ?? '', 0, 30)); ?></td>
                                    <td><?php echo formatDate($activity['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentActivity)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent activity</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="data-table">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>New Leads</h5>
                        <a href="#" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    
                    <div class="list-group">
                        <?php foreach ($recentLeads as $lead): ?>
                        <div class="list-group-item border-0 mb-2" style="background: rgba(255, 193, 7, 0.05); border-radius: 10px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']); ?></h6>
                                    <p class="mb-1 small text-muted"><?php echo htmlspecialchars($lead['fitness_goal'] ?? 'No goal specified'); ?></p>
                                    <span class="badge bg-warning"><?php echo ucfirst($lead['status']); ?></span>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" onclick="alert('Contact lead: <?php echo htmlspecialchars($lead['email']); ?>')">
                                    <i class="bi bi-telephone"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($recentLeads)): ?>
                        <p class="text-center text-muted">No new leads</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="data-table">
                    <h5 class="mb-3">System Status</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Server Load</span>
                                <span>65%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 65%"></div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Storage</span>
                                <span>42%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: 42%"></div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>API Response</span>
                                <span>98%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" style="width: 98%"></div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Uptime</span>
                                <span>99.9%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 99.9%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Coach Modal -->
    <div class="modal fade" id="addCoachModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Coach</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../api/coaches.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Coach</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Website Modal -->
    <div class="modal fade" id="editWebsiteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Website Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../api/settings.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" class="form-control" value="FitCoach Pro">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Primary Color</label>
                            <input type="color" name="primary_color" class="form-control form-control-color" value="#4A6FA5">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle">
        <i class="bi bi-moon-fill" id="themeIcon"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;
        
        const savedTheme = localStorage.getItem('theme') || 
                          (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            themeIcon.className = theme === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
        }

        // Mobile Menu Toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');
        
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });

        // Charts
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Revenue ($)',
                    data: <?php echo json_encode($revenueValues); ?>,
                    borderColor: 'rgba(74, 111, 165, 1)',
                    backgroundColor: 'rgba(74, 111, 165, 0.1)',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(74, 111, 165, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Revenue: $${context.raw.toLocaleString()}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Client Distribution Chart
        const clientCtx = document.getElementById('clientChart').getContext('2d');
        const clientChart = new Chart(clientCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($programLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($programCounts); ?>,
                    backgroundColor: [
                        'rgba(74, 111, 165, 0.9)',
                        'rgba(40, 167, 69, 0.9)',
                        'rgba(255, 193, 7, 0.9)',
                        'rgba(220, 53, 69, 0.9)',
                        'rgba(108, 117, 125, 0.9)'
                    ],
                    borderWidth: 3,
                    borderColor: 'var(--card-bg)',
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 13
                            },
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} clients (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });

        function resizeCharts() {
            revenueChart.resize();
            clientChart.resize();
        }

        window.addEventListener('resize', resizeCharts);
    </script>
</body>
</html>