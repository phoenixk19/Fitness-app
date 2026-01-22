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
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            height: 100%;
        }

        .chart-container h5 {
            margin-bottom: 1rem;
            font-size: 1rem;
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

        .status-inactive {
            background: rgba(108, 117, 125, 0.1);
            color: var(--text-light);
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

        /* Chart specific adjustments */
        .chart-wrapper {
            height: 300px;
            position: relative;
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
                    <span class="badge bg-light text-dark">3</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-person-plus"></i> Leads
                    <span class="badge bg-danger notification-badge">5</span>
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
                <a href="index.php">
                    <i class="bi bi-globe"></i> Public Website
                </a>
            </li>
            <li>
                <a href="login.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer mt-auto p-3">
            <div class="d-flex align-items-center">
                <div class="user-avatar">AJ</div>
                <div class="ms-2">
                    <div class="small fw-bold">Admin Johnson</div>
                    <div class="x-small opacity-75">Head Coach</div>
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
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-2">AJ</div>
                            <div>
                                <div class="small fw-bold">Admin Johnson</div>
                                <div class="x-small text-muted">Head Coach</div>
                            </div>
                        </div>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="login.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
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
            <a href="#" class="action-btn">
                <i class="bi bi-plus-circle fs-3"></i>
                <span>Add New Coach</span>
            </a>
            <a href="#" class="action-btn">
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
                    <div class="stat-number">47</div>
                    <div class="stat-label">Total Clients</div>
                    <div class="small mt-2 text-success">
                        <i class="bi bi-arrow-up"></i> 12% from last month
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(40, 167, 69, 0.1); color: var(--success-color);">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="stat-number">3</div>
                    <div class="stat-label">Active Coaches</div>
                    <div class="small mt-2 text-muted">
                        2 assistants available
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: var(--warning-color);">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div class="stat-number">5</div>
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
                    <div class="stat-number">$8,450</div>
                    <div class="stat-label">Monthly Revenue</div>
                    <div class="small mt-2 text-success">
                        <i class="bi bi-arrow-up"></i> 18% growth
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row - FIXED VERSION -->
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
                                <tr>
                                    <th>Coach</th>
                                    <th>Action</th>
                                    <th>Client</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">SC</div>
                                            <span>Sarah Chen</span>
                                        </div>
                                    </td>
                                    <td>Assigned New Workout</td>
                                    <td>Michael Wong</td>
                                    <td>2 hours ago</td>
                                    <td><span class="status-badge status-active">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">MJ</div>
                                            <span>Mike Johnson</span>
                                        </div>
                                    </td>
                                    <td>Updated Nutrition Plan</td>
                                    <td>Emma Davis</td>
                                    <td>4 hours ago</td>
                                    <td><span class="status-badge status-active">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">LR</div>
                                            <span>Lisa Rodriguez</span>
                                        </div>
                                    </td>
                                    <td>Processed Payment</td>
                                    <td>David Wilson</td>
                                    <td>1 day ago</td>
                                    <td><span class="status-badge status-pending">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">SC</div>
                                            <span>Sarah Chen</span>
                                        </div>
                                    </td>
                                    <td>Added Progress Photo</td>
                                    <td>James Miller</td>
                                    <td>2 days ago</td>
                                    <td><span class="status-badge status-active">Completed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="data-table">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>New Leads</h5>
                        <a href="#" class="btn btn-sm btn-primary">Contact All</a>
                    </div>
                    
                    <div class="list-group">
                        <div class="list-group-item border-0 mb-2" style="background: rgba(255, 193, 7, 0.05); border-radius: 10px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Robert Garcia</h6>
                                    <p class="mb-1 small text-muted">Weight Loss Program</p>
                                    <span class="badge bg-warning">New</span>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-telephone"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="list-group-item border-0 mb-2" style="background: rgba(255, 193, 7, 0.05); border-radius: 10px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Jennifer Lee</h6>
                                    <p class="mb-1 small text-muted">Muscle Gain Program</p>
                                    <span class="badge bg-warning">New</span>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-envelope"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="list-group-item border-0 mb-2" style="background: rgba(108, 117, 125, 0.05); border-radius: 10px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Thomas Brown</h6>
                                    <p class="mb-1 small text-muted">Already Contacted</p>
                                    <span class="badge bg-secondary">Contacted</span>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-check"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="list-group-item border-0" style="background: rgba(40, 167, 69, 0.05); border-radius: 10px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Maria Gonzalez</h6>
                                    <p class="mb-1 small text-muted">Converted to Client</p>
                                    <span class="badge bg-success">Converted</span>
                                </div>
                                <button class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-person-check"></i>
                                </button>
                            </div>
                        </div>
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
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Revenue ($)',
                    data: [6500, 7200, 8000, 7800, 8200, 8450, 9000],
                    borderColor: 'rgba(74, 111, 165, 1)',
                    backgroundColor: 'rgba(74, 111, 165, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(74, 111, 165, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 6000,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            font: {
                                size: 11
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
                labels: ['Weight Loss', 'Muscle Gain', 'General Fitness', 'Sports'],
                datasets: [{
                    data: [35, 25, 20, 20],
                    backgroundColor: [
                        'rgba(74, 111, 165, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderWidth: 1,
                    borderColor: 'var(--card-bg)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });

        // Auto-refresh notifications (simulated)
        function updateNotifications() {
            const notificationBadge = document.querySelector('.notification-badge');
            const currentCount = parseInt(notificationBadge.textContent);
            
            // Simulate new notifications (random between 0-2)
            const newNotifications = Math.floor(Math.random() * 3);
            if (newNotifications > 0) {
                notificationBadge.textContent = currentCount + newNotifications;
                notificationBadge.style.animation = 'none';
                setTimeout(() => {
                    notificationBadge.style.animation = 'pulse 0.5s';
                }, 10);
            }
        }

        // Update notifications every 30 seconds
        setInterval(updateNotifications, 30000);

        // Add pulse animation for notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }
            .notification-badge {
                animation: pulse 0.5s;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>