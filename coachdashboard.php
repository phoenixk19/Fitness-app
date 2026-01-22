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

        /* Sidebar */
        .coach-sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding-top: 20px;
            z-index: 1000;
            box-shadow: 3px 0 15px rgba(0,0,0,0.1);
        }

        .coach-brand {
            padding: 0 1.5rem 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
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

        .coach-menu {
            list-style: none;
            padding: 0 1rem;
        }

        .coach-menu li {
            margin-bottom: 8px;
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

        /* Main Content */
        .coach-main {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
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
        }

        /* Quick Stats */
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

        .stat-trend {
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .trend-up { color: var(--success-color); }
        .trend-down { color: var(--danger-color); }

        /* Dashboard Sections */
        .dashboard-section {
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Clients Grid */
        .clients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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

        /* Workout Planning */
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

        /* Nutrition Management */
        .nutrition-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
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

        /* Charts */
        .chart-container {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            height: 350px;
        }

        /* Packages & Payments */
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

        /* Buttons */
        .btn-coach {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            color: white;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-coach:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 111, 165, 0.3);
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
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 1.5rem;
        }

        /* Form Controls */
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

        /* Progress */
        .progress {
            height: 8px;
            border-radius: 4px;
            background: var(--light-bg);
        }

        .progress-bar {
            border-radius: 4px;
        }

        /* Badges */
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Notifications */
        .notification-dot {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--danger-color);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.7; }
            70% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="coach-sidebar">
        <div class="coach-brand">
            <div class="coach-avatar">SC</div>
            <h5 class="mb-1">Coach Sarah Chen</h5>
            <p class="small opacity-75 mb-0">Certified Personal Trainer</p>
            <span class="badge bg-light text-dark mt-2">Premium Coach</span>
        </div>

        <ul class="coach-menu">
            <li>
                <a href="#" class="active">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-people"></i> Clients
                    <span class="badge">12</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-activity"></i> Workouts
                    <span class="badge">3 new</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-egg-fried"></i> Nutrition
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-graph-up"></i> Progress
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-clipboard-check"></i> Fitness Tests
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-box-seam"></i> Packages
                    <span class="badge">5 active</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-cash"></i> Payments
                    <span class="badge">$2,450 due</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-bar-chart"></i> Analytics
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="bi bi-person-plus"></i> Editors
                    <span class="badge">2 assistants</span>
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

        <div class="sidebar-footer mt-4 p-3 text-center">
            <small class="opacity-75">Weekly Revenue: $1,850</small>
            <div class="progress mt-2" style="height: 5px;">
                <div class="progress-bar bg-success" style="width: 75%"></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="coach-main">
        <!-- Header -->
        <div class="coach-header">
            <button class="menu-toggle" id="menuToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="header-title">
                <h1>Coach Dashboard</h1>
                <p>Welcome back, Sarah! Here's your coaching overview for today.</p>
            </div>
            
            <div class="header-actions">
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control" placeholder="Search clients, workouts...">
                    <button class="btn btn-coach">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <button class="btn btn-coach">
                    <i class="bi bi-plus-circle"></i> New Client
                </button>
                <button class="btn btn-outline-coach">
                    <i class="bi bi-bell"></i>
                    <span class="badge bg-danger">3</span>
                </button>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-box">
                <div class="stat-icon" style="background: rgba(74, 111, 165, 0.1); color: var(--primary-color);">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value">12</div>
                <div class="stat-label">Active Clients</div>
                <div class="stat-trend trend-up">
                    <i class="bi bi-arrow-up"></i> 2 new this week
                </div>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon" style="background: rgba(40, 167, 69, 0.1); color: var(--success-color);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value">87%</div>
                <div class="stat-label">Workout Completion</div>
                <div class="stat-trend trend-up">
                    <i class="bi bi-arrow-up"></i> 5% improvement
                </div>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: var(--warning-color);">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-value">4</div>
                <div class="stat-label">Pending Workouts</div>
                <div class="stat-trend trend-down">
                    <i class="bi bi-exclamation-triangle"></i> Needs attention
                </div>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon" style="background: rgba(220, 53, 69, 0.1); color: var(--danger-color);">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-value">$2,450</div>
                <div class="stat-label">Pending Payments</div>
                <div class="stat-trend trend-down">
                    <i class="bi bi-clock"></i> 3 overdue
                </div>
            </div>
        </div>

        <!-- Client Management Section -->
        <div class="dashboard-section">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-people"></i>
                    <h3 class="mb-0">Client Management</h3>
                </div>
                <div>
                    <button class="btn btn-coach">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    <button class="btn btn-coach">
                        <i class="bi bi-download"></i> Export
                    </button>
                </div>
            </div>

            <div class="clients-grid">
                <!-- Client 1 -->
                <div class="client-card">
                    <div class="notification-dot"></div>
                    <div class="client-header">
                        <div class="client-avatar">MW</div>
                        <div class="client-info">
                            <h5>Michael Wong</h5>
                            <div class="client-goal">Weight Loss • 8 weeks in</div>
                            <span class="client-status status-excellent">Excellent Progress</span>
                        </div>
                    </div>
                    
                    <div class="client-metrics">
                        <div class="metric-item">
                            <span class="metric-value">-5.2kg</span>
                            <span class="metric-label">Weight Loss</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">92%</span>
                            <span class="metric-label">Adherence</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">15</span>
                            <span class="metric-label">Workouts</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">28</span>
                            <span class="metric-label">Meals Logged</span>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-sm btn-outline-coach">
                            <i class="bi bi-activity"></i> View Workout
                        </button>
                        <button class="btn btn-sm btn-outline-coach">
                            <i class="bi bi-chat"></i> Message
                        </button>
                        <button class="btn btn-sm btn-coach">
                            <i class="bi bi-graph-up"></i> Progress
                        </button>
                    </div>
                </div>

                <!-- Client 2 -->
                <div class="client-card">
                    <div class="client-header">
                        <div class="client-avatar">ED</div>
                        <div class="client-info">
                            <h5>Emma Davis</h5>
                            <div class="client-goal">Muscle Gain • 12 weeks in</div>
                            <span class="client-status status-good">Good Progress</span>
                        </div>
                    </div>
                    
                    <div class="client-metrics">
                        <div class="metric-item">
                            <span class="metric-value">+3.5kg</span>
                            <span class="metric-label">Muscle Gain</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">88%</span>
                            <span class="metric-label">Adherence</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">12</span>
                            <span class="metric-label">Workouts</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">32</span>
                            <span class="metric-label">Meals Logged</span>
                        </div>
                    </div>
                    
                    <div class="progress mt-2 mb-3">
                        <div class="progress-bar bg-success" style="width: 88%"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-sm btn-outline-coach">
                            <i class="bi bi-egg-fried"></i> Nutrition
                        </button>
                        <button class="btn btn-sm btn-outline-coach">
                            <i class="bi bi-images"></i> Photos
                        </button>
                        <button class="btn btn-sm btn-coach">
                            <i class="bi bi-pencil"></i> Edit Plan
                        </button>
                    </div>
                </div>

                <!-- Client 3 -->
                <div class="client-card">
                    <div class="notification-dot"></div>
                    <div class="client-header">
                        <div class="client-avatar">DW</div>
                        <div class="client-info">
                            <h5>David Wilson</h5>
                            <div class="client-goal">Sports Performance • 4 weeks in</div>
                            <span class="client-status status-warning">Behind Schedule</span>
                        </div>
                    </div>
                    
                    <div class="client-metrics">
                        <div class="metric-item">
                            <span class="metric-value">65%</span>
                            <span class="metric-label">Completion</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">2</span>
                            <span class="metric-label">Missed</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">$450</span>
                            <span class="metric-label">Due</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">7 days</span>
                            <span class="metric-label">Last Log</span>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3 p-2">
                        <small><i class="bi bi-exclamation-triangle"></i> Needs follow-up: Missed 2 workouts</small>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-sm btn-outline-coach">
                            <i class="bi bi-telephone"></i> Call
                        </button>
                        <button class="btn btn-sm btn-outline-coach">
                            <i class="bi bi-envelope"></i> Email
                        </button>
                        <button class="btn btn-sm btn-coach">
                            <i class="bi bi-bell"></i> Remind
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts & Analytics -->
        <div class="row">
            <div class="col-lg-8">
                <div class="chart-container">
                    <div class="section-header mb-3">
                        <div class="section-title">
                            <i class="bi bi-graph-up"></i>
                            <h4 class="mb-0">Client Progress Trends</h4>
                        </div>
                        <select class="form-select" style="width: 200px;">
                            <option>Last 30 Days</option>
                            <option>Last 3 Months</option>
                            <option>Last 6 Months</option>
                            <option>Year to Date</option>
                        </select>
                    </div>
                    <canvas id="progressChart" height="250"></canvas>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="chart-container">
                    <div class="section-title mb-3">
                        <i class="bi bi-pie-chart"></i>
                        <h4 class="mb-0">Program Distribution</h4>
                    </div>
                    <canvas id="programChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Workout Planning Section -->
        <div class="workout-planning">
            <div class="section-header mb-4">
                <div class="section-title">
                    <i class="bi bi-activity"></i>
                    <h3 class="mb-0">Workout Planning</h3>
                </div>
                <button class="btn btn-coach">
                    <i class="bi bi-plus-circle"></i> Create Template
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h5>Exercise Library (FR-7)</h5>
                    <div class="exercise-library">
                        <div class="exercise-item">
                            <div>
                                <strong>Bench Press</strong>
                                <div class="small text-muted">Chest • Barbell</div>
                            </div>
                            <button class="btn btn-sm btn-coach">Add</button>
                        </div>
                        <div class="exercise-item">
                            <div>
                                <strong>Squat</strong>
                                <div class="small text-muted">Legs • Barbell</div>
                            </div>
                            <button class="btn btn-sm btn-coach">Add</button>
                        </div>
                        <div class="exercise-item">
                            <div>
                                <strong>Deadlift</strong>
                                <div class="small text-muted">Back • Barbell</div>
                            </div>
                            <button class="btn btn-sm btn-coach">Add</button>
                        </div>
                        <div class="exercise-item">
                            <div>
                                <strong>Pull-ups</strong>
                                <div class="small text-muted">Back • Bodyweight</div>
                            </div>
                            <button class="btn btn-sm btn-coach">Add</button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5>Create Workout Group (FR-8)</h5>
                    <div class="mb-3">
                        <label class="form-label">Workout Name</label>
                        <input type="text" class="form-control" placeholder="e.g., Push Day, Leg Day">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Training Type</label>
                        <select class="form-select">
                            <option>Strength</option>
                            <option>Hypertrophy</option>
                            <option>Endurance</option>
                            <option>Power</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign to Client (FR-9)</label>
                        <select class="form-select" multiple>
                            <option>Michael Wong</option>
                            <option>Emma Davis</option>
                            <option>David Wilson</option>
                            <option>James Miller</option>
                        </select>
                    </div>
                    <button class="btn btn-coach w-100">
                        <i class="bi bi-save"></i> Save Workout Template
                    </button>
                </div>
            </div>
        </div>

        <!-- Nutrition & Packages -->
        <div class="row">
            <div class="col-lg-6">
                <div class="nutrition-card">
                    <div class="section-header mb-4">
                        <div class="section-title">
                            <i class="bi bi-egg-fried"></i>
                            <h4 class="mb-0">Nutrition Management</h4>
                        </div>
                        <button class="btn btn-coach btn-sm">
                            <i class="bi bi-gear"></i> Adjust Targets
                        </button>
                    </div>
                    
                    <div class="macro-targets">
                        <div class="macro-box" style="border-left: 4px solid #4A6FA5;">
                            <span class="macro-value">2,150</span>
                            <small>Calories</small>
                        </div>
                        <div class="macro-box" style="border-left: 4px solid #28a745;">
                            <span class="macro-value">150g</span>
                            <small>Protein</small>
                        </div>
                        <div class="macro-box" style="border-left: 4px solid #ffc107;">
                            <span class="macro-value">250g</span>
                            <small>Carbs</small>
                        </div>
                        <div class="macro-box" style="border-left: 4px solid #dc3545;">
                            <span class="macro-value">65g</span>
                            <small>Fat</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <small><i class="bi bi-info-circle"></i> TDEE calculated based on client metrics (FR-33)</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="nutrition-card">
                    <div class="section-header mb-4">
                        <div class="section-title">
                            <i class="bi bi-box-seam"></i>
                            <h4 class="mb-0">Active Packages (FR-44)</h4>
                        </div>
                        <button class="btn btn-coach btn-sm">
                            <i class="bi bi-plus"></i> Create
                        </button>
                    </div>
                    
                    <div class="packages-grid">
                        <div class="package-card">
                            <div class="package-theme" style="background: linear-gradient(45deg, #4A6FA5, #166088);"></div>
                            <h5>Weight Loss Elite</h5>
                            <p class="small text-muted">12-week transformation</p>
                            <ul class="small mb-3">
                                <li>3 weekly workouts</li>
                                <li>Nutrition plan</li>
                                <li>Weekly check-ins</li>
                            </ul>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">$299/mo</span>
                                <span class="badge bg-success">5 clients</span>
                            </div>
                        </div>
                        
                        <div class="package-card">
                            <div class="package-theme" style="background: linear-gradient(45deg, #28a745, #20c997);"></div>
                            <h5>Muscle Building Pro</h5>
                            <p class="small text-muted">Advanced hypertrophy</p>
                            <ul class="small mb-3">
                                <li>4 weekly workouts</li>
                                <li>Macro tracking</li>
                                <li>Progress photos</li>
                            </ul>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">$349/mo</span>
                                <span class="badge bg-primary">3 clients</span>
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
        // Theme Toggle
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
        const sidebar = document.querySelector('.coach-sidebar');
        
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Charts
        // Progress Chart
        const progressCtx = document.getElementById('progressChart').getContext('2d');
        const progressChart = new Chart(progressCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7'],
                datasets: [
                    {
                        label: 'Weight Loss (kg)',
                        data: [0, -1.2, -2.3, -3.1, -4.0, -4.7, -5.2],
                        borderColor: 'rgba(74, 111, 165, 1)',
                        backgroundColor: 'rgba(74, 111, 165, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Workout Completion %',
                        data: [85, 88, 90, 87, 92, 91, 92],
                        borderColor: 'rgba(40, 167, 69, 1)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });

        // Program Distribution Chart
        const programCtx = document.getElementById('programChart').getContext('2d');
        const programChart = new Chart(programCtx, {
            type: 'doughnut',
            data: {
                labels: ['Weight Loss', 'Muscle Gain', 'Sports', 'General'],
                datasets: [{
                    data: [5, 3, 2, 2],
                    backgroundColor: [
                        'rgba(74, 111, 165, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
                    borderWidth: 2,
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
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Client card interactions
        document.querySelectorAll('.client-card .btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const clientName = this.closest('.client-card').querySelector('h5').textContent;
                
                if(this.querySelector('.bi-activity')) {
                    showModal(`Viewing ${clientName}'s Workout`);
                } else if(this.querySelector('.bi-chat')) {
                    showModal(`Messaging ${clientName}`);
                } else if(this.querySelector('.bi-graph-up')) {
                    showModal(`${clientName}'s Progress Report`);
                }
            });
        });

        function showModal(title) {
            // Create modal dynamically
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>This would show detailed information in a real application.</p>
                            <p>The coach can view, edit, and manage all client data from here.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-coach">Save Changes</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
            
            // Remove modal after hiding
            modal.addEventListener('hidden.bs.modal', function () {
                modal.remove();
            });
        }

        // Simulate real-time updates
        function updateStats() {
            const completionStat = document.querySelector('.stat-box:nth-child(2) .stat-value');
            const current = parseInt(completionStat.textContent);
            const newValue = Math.min(100, current + Math.random() * 2);
            completionStat.textContent = Math.round(newValue) + '%';
            
            // Update trend
            const trend = completionStat.closest('.stat-box').querySelector('.stat-trend');
            trend.innerHTML = `<i class="bi bi-arrow-up"></i> ${Math.round(newValue - current)}% improvement`;
        }

        // Update stats every 10 seconds
        setInterval(updateStats, 10000);
    </script>
</body>
</html>