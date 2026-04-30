<?php
// File: C:\xampp\htdocs\appF\dashboard\client.php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/middleware.php';
require_once '../includes/roles.php';

// Apply middleware - only client can access
applyMiddleware(['authMiddleware', 'noCacheMiddleware'], function() {
    roleMiddleware(['client']);
});

$userId = $_SESSION['user_id'];
$db = getDB();

// Get client data
$client = $db->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$client->bind_param("i", $userId);
$client->execute();
$client = $client->get_result()->fetch_assoc();

// Get today's workout
$todayWorkout = $db->prepare("
    SELECT cw.*, wt.name as workout_name, wt.training_type
    FROM client_workouts cw
    LEFT JOIN workout_templates wt ON cw.template_id = wt.id
    WHERE cw.client_id = ? 
    AND cw.start_date <= CURDATE() 
    AND (cw.end_date >= CURDATE() OR cw.end_date IS NULL)
    AND cw.status IN ('scheduled', 'in_progress')
    ORDER BY cw.start_date DESC
    LIMIT 1
");
$todayWorkout->bind_param("i", $userId);
$todayWorkout->execute();
$todayWorkout = $todayWorkout->get_result()->fetch_assoc();

// Get exercises for today's workout
$exercises = [];
if ($todayWorkout) {
    $exercisesQuery = $db->prepare("
        SELECT cwe.*, e.name, e.muscle_group, e.equipment
        FROM client_workout_exercises cwe
        JOIN exercises e ON cwe.exercise_id = e.id
        WHERE cwe.client_workout_id = ?
        ORDER BY cwe.set_number
    ");
    $exercisesQuery->bind_param("i", $todayWorkout['id']);
    $exercisesQuery->execute();
    $exercises = $exercisesQuery->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get nutrition summary for today
$nutritionToday = $db->prepare("
    SELECT 
        COALESCE(SUM(total_calories), 0) as total_calories,
        COALESCE(SUM(total_protein), 0) as total_protein,
        COALESCE(SUM(total_carbs), 0) as total_carbs,
        COALESCE(SUM(total_fat), 0) as total_fat
    FROM meal_logs
    WHERE client_id = ? AND meal_date = CURDATE()
");
$nutritionToday->bind_param("i", $userId);
$nutritionToday->execute();
$nutritionToday = $nutritionToday->get_result()->fetch_assoc();

// Daily goals
$dailyGoals = [
    'calories' => 2150,
    'protein' => 150,
    'carbs' => 250,
    'fat' => 65
];

// Get weight progress (last 8 weeks)
$weightProgress = $db->prepare("
    SELECT measurement_date as date, weight
    FROM body_measurements
    WHERE client_id = ?
    ORDER BY measurement_date DESC
    LIMIT 8
");
$weightProgress->bind_param("i", $userId);
$weightProgress->execute();
$weightData = $weightProgress->get_result()->fetch_all(MYSQLI_ASSOC);
$weightData = array_reverse($weightData);

$weightLabels = [];
$weightValues = [];
foreach ($weightData as $data) {
    $weightLabels[] = date('M d', strtotime($data['date']));
    $weightValues[] = (float)$data['weight'];
}

// Get weekly workout completion
$weeklyCompletion = $db->prepare("
    SELECT 
        DATE_FORMAT(start_date, '%a') as day,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM client_workouts
    WHERE client_id = ? AND start_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(start_date)
    ORDER BY start_date
");
$weeklyCompletion->bind_param("i", $userId);
$weeklyCompletion->execute();
$weeklyData = $weeklyCompletion->get_result()->fetch_all(MYSQLI_ASSOC);

$weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$completionRates = array_fill(0, 7, 0);

foreach ($weeklyData as $data) {
    $dayIndex = array_search($data['day'], $weekDays);
    if ($dayIndex !== false && $data['total'] > 0) {
        $completionRates[$dayIndex] = round(($data['completed'] / $data['total']) * 100);
    }
}

// Get body measurements
$measurements = $db->prepare("
    SELECT * FROM body_measurements
    WHERE client_id = ?
    ORDER BY measurement_date DESC
    LIMIT 1
");
$measurements->bind_param("i", $userId);
$measurements->execute();
$measurements = $measurements->get_result()->fetch_assoc();

// Get progress photos
$progressPhotos = $db->prepare("
    SELECT * FROM progress_photos
    WHERE client_id = ?
    ORDER BY photo_date DESC
    LIMIT 3
");
$progressPhotos->bind_param("i", $userId);
$progressPhotos->execute();
$progressPhotos = $progressPhotos->get_result()->fetch_all(MYSQLI_ASSOC);

// Get leaderboard position
$leaderboard = $db->prepare("
    SELECT client_id, score, RANK() OVER (ORDER BY score DESC) as rank
    FROM leaderboard
    WHERE metric_type = 'consistency'
    LIMIT 10
");
$leaderboard->execute();
$leaderboardData = $leaderboard->get_result()->fetch_all(MYSQLI_ASSOC);

$userRank = null;
foreach ($leaderboardData as $index => $entry) {
    if ($entry['client_id'] == $userId) {
        $userRank = $index + 1;
        break;
    }
}

// Calculate macro percentages
$calPercent = min(100, round(($nutritionToday['total_calories'] / $dailyGoals['calories']) * 100));
$proteinPercent = min(100, round(($nutritionToday['total_protein'] / $dailyGoals['protein']) * 100));
$carbsPercent = min(100, round(($nutritionToday['total_carbs'] / $dailyGoals['carbs']) * 100));
$fatPercent = min(100, round(($nutritionToday['total_fat'] / $dailyGoals['fat']) * 100));
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - FitCoach Pro</title>
    
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

        /* Sidebar */
        .client-sidebar {
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

        .client-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            flex-shrink: 0;
        }

        .client-avatar-large {
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

        /* Scrollable Menu */
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

        .client-menu {
            list-style: none;
            padding: 10px 15px;
            margin: 0;
        }

        .client-menu li {
            margin-bottom: 5px;
        }

        .client-menu a {
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

        .client-menu a:hover,
        .client-menu a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: white;
        }

        .client-menu .badge {
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
        .client-main {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .client-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .client-info h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .client-info p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }

        .header-stats {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        /* Today's Overview */
        .today-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .overview-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .overview-card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0,0,0,0.1);
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.2rem;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending { background: rgba(255, 193, 7, 0.1); color: var(--warning-color); }
        .status-completed { background: rgba(40, 167, 69, 0.1); color: var(--success-color); }

        .exercise-list {
            margin-bottom: 20px;
        }

        .exercise-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: var(--light-bg);
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary-color);
            flex-wrap: wrap;
            gap: 10px;
        }

        .start-workout-btn {
            background: linear-gradient(45deg, var(--success-color), #20c997);
            border: none;
            padding: 12px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .start-workout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        }

        /* Nutrition Summary */
        .nutrition-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .macro-circle {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            background: var(--light-bg);
        }

        .macro-progress {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px;
        }

        .macro-value {
            font-size: 1.2rem;
            font-weight: bold;
            display: block;
        }

        .macro-label {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        /* Progress Section */
        .progress-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .progress-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .chart-container {
            height: 250px;
            margin-top: 20px;
        }

        /* Week Calendar */
        .week-calendar {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-top: 20px;
        }

        .day-cell {
            text-align: center;
            padding: 15px 10px;
            border-radius: 10px;
            background: var(--light-bg);
            transition: all 0.3s;
        }

        .day-cell.today {
            background: var(--primary-color);
            color: white;
            transform: scale(1.05);
        }

        .day-cell.workout {
            background: rgba(40, 167, 69, 0.1);
            border: 2px solid var(--success-color);
        }

        .day-cell.rest {
            background: rgba(108, 117, 125, 0.1);
            border: 2px solid var(--text-light);
        }

        /* Progress Photos */
        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .photo-card {
            background: var(--card-bg);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .photo-placeholder {
            height: 150px;
            background: linear-gradient(45deg, var(--light-bg), var(--card-bg));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
        }

        .photo-info {
            padding: 15px;
        }

        /* Leaderboard */
        .leaderboard-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .leaderboard-list {
            margin-top: 20px;
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: var(--light-bg);
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .leaderboard-item.me {
            background: rgba(74, 111, 165, 0.1);
            border: 2px solid var(--primary-color);
        }

        .rank {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }

        /* Buttons */
        .btn-client {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            color: white;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-client:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 111, 165, 0.3);
            color: white;
        }

        .btn-outline-client {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-outline-client:hover {
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

        /* Form Controls */
        .form-control {
            background-color: var(--light-bg);
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            color: var(--text-color);
        }

        [data-bs-theme="dark"] .form-control {
            background-color: #2d2d2d;
            border-color: #444;
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

        /* Responsive */
        @media (max-width: 992px) {
            .client-sidebar {
                transform: translateX(-100%);
            }
            
            .client-sidebar.open {
                transform: translateX(0);
            }
            
            .client-main {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block !important;
            }
        }

        @media (max-width: 768px) {
            .today-overview {
                grid-template-columns: 1fr;
            }
            
            .progress-section {
                grid-template-columns: 1fr;
            }
            
            .nutrition-summary {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .calendar-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .client-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="client-sidebar" id="clientSidebar">
        <div class="client-brand">
            <div class="client-avatar-large"><?php echo strtoupper(substr($client['name'], 0, 2)); ?></div>
            <h5 class="mb-1"><?php echo htmlspecialchars($client['name']); ?></h5>
            <p class="small opacity-75 mb-0">Active Member</p>
        </div>

        <!-- Scrollable Menu -->
        <div class="menu-wrapper">
            <ul class="client-menu">
                <li><a href="#" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="#"><i class="bi bi-activity"></i> My Workouts</a></li>
                <li><a href="#"><i class="bi bi-egg-fried"></i> Nutrition</a></li>
                <li><a href="#"><i class="bi bi-graph-up"></i> Progress</a></li>
                <li><a href="#"><i class="bi bi-images"></i> Progress Photos</a></li>
                <li><a href="#"><i class="bi bi-clipboard-check"></i> Fitness Tests</a></li>
                <li><a href="#"><i class="bi bi-trophy"></i> Leaderboard</a></li>
                <li><a href="#"><i class="bi bi-chat-dots"></i> Messages</a></li>
                <li><a href="#"><i class="bi bi-gear"></i> Settings</a></li>
                <li class="mt-3"><a href="/appF/index.php"><i class="bi bi-globe"></i> Public Website</a></li>
                <li><a href="/appF/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <small class="opacity-75">Member since <?php echo date('M Y'); ?></small>
        </div>
    </div>

    <!-- Main Content -->
    <div class="client-main">
        <!-- Header -->
        <div class="client-header">
            <div>
                <button class="menu-toggle" id="menuToggle" style="color: white; background: rgba(255,255,255,0.2); border-radius: 10px; padding: 8px 12px;">
                    <i class="bi bi-list"></i> Menu
                </button>
                <div class="client-info mt-2">
                    <h1>Welcome back, <?php echo htmlspecialchars($client['name']); ?>!</h1>
                    <p>Keep pushing forward - your fitness journey continues</p>
                </div>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $weightValues ? number_format(end($weightValues), 1) : '--'; ?>kg</span>
                    <span class="stat-label">Current Weight</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo array_sum($completionRates) / 7; ?>%</span>
                    <span class="stat-label">Weekly Adherence</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $todayWorkout ? 1 : 0; ?></span>
                    <span class="stat-label">Workouts Today</span>
                </div>
            </div>
        </div>

        <!-- Today's Overview -->
        <section class="today-overview">
            <!-- Today's Workout Card -->
            <div class="overview-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-activity"></i>
                        <h3 class="mb-0">Today's Workout</h3>
                    </div>
                    <?php if ($todayWorkout): ?>
                        <span class="status-badge status-pending">Ready to Start</span>
                    <?php else: ?>
                        <span class="status-badge status-completed">Rest Day</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($todayWorkout): ?>
                    <h5><?php echo htmlspecialchars($todayWorkout['workout_name']); ?></h5>
                    <p class="text-muted mb-3"><?php echo ucfirst($todayWorkout['training_type']); ?> training • 45 min</p>
                    
                    <div class="exercise-list">
                        <?php foreach ($exercises as $exercise): ?>
                        <div class="exercise-item">
                            <div>
                                <strong><?php echo htmlspecialchars($exercise['name']); ?></strong>
                                <div class="small text-muted"><?php echo $exercise['muscle_group']; ?></div>
                            </div>
                            <span class="badge bg-secondary">Not Started</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button class="start-workout-btn" id="startWorkout">
                        <i class="bi bi-play-circle"></i> Start Workout
                    </button>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-check fs-1 text-muted"></i>
                        <p class="mt-3 mb-0">No workout scheduled for today!</p>
                        <p class="small text-muted">Enjoy your rest day or log your meals.</p>
                        <button class="btn-client mt-3"><i class="bi bi-egg-fried"></i> Log Meals</button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Nutrition Summary Card -->
            <div class="overview-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-egg-fried"></i>
                        <h3 class="mb-0">Today's Nutrition</h3>
                    </div>
                    <span class="text-muted">Goal: <?php echo $dailyGoals['calories']; ?> cal</span>
                </div>
                
                <div class="nutrition-summary">
                    <div class="macro-circle">
                        <div class="macro-progress">
                            <canvas id="calorieChart"></canvas>
                        </div>
                        <span class="macro-value"><?php echo number_format($nutritionToday['total_calories']); ?></span>
                        <span class="macro-label">Calories</span>
                    </div>
                    <div class="macro-circle">
                        <div class="macro-progress">
                            <canvas id="proteinChart"></canvas>
                        </div>
                        <span class="macro-value"><?php echo number_format($nutritionToday['total_protein']); ?>g</span>
                        <span class="macro-label">Protein</span>
                    </div>
                    <div class="macro-circle">
                        <div class="macro-progress">
                            <canvas id="carbChart"></canvas>
                        </div>
                        <span class="macro-value"><?php echo number_format($nutritionToday['total_carbs']); ?>g</span>
                        <span class="macro-label">Carbs</span>
                    </div>
                    <div class="macro-circle">
                        <div class="macro-progress">
                            <canvas id="fatChart"></canvas>
                        </div>
                        <span class="macro-value"><?php echo number_format($nutritionToday['total_fat']); ?>g</span>
                        <span class="macro-label">Fat</span>
                    </div>
                </div>
                
                <button class="btn-client w-100" data-bs-toggle="modal" data-bs-target="#logMealModal">
                    <i class="bi bi-plus-circle"></i> Log Meal
                </button>
            </div>
        </section>

        <!-- Progress Tracking -->
        <section class="progress-section">
            <div class="progress-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-graph-down"></i>
                        <h3 class="mb-0">Weight Progress</h3>
                    </div>
                    <?php if ($weightValues): ?>
                    <span class="text-success">-<?php echo number_format($weightValues[0] - end($weightValues), 1); ?>kg total</span>
                    <?php endif; ?>
                </div>
                <div class="chart-container">
                    <canvas id="weightChart"></canvas>
                </div>
            </div>

            <div class="progress-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-check-circle"></i>
                        <h3 class="mb-0">Workout Completion</h3>
                    </div>
                    <span class="text-success"><?php echo round(array_sum($completionRates) / 7); ?>% avg</span>
                </div>
                <div class="chart-container">
                    <canvas id="completionChart"></canvas>
                </div>
            </div>
        </section>

        <!-- Week Calendar -->
        <section class="week-calendar">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-calendar-week"></i>
                    <h3 class="mb-0">This Week's Schedule</h3>
                </div>
                <button class="btn-outline-client" data-bs-toggle="modal" data-bs-target="#unavailableModal">
                    <i class="bi bi-calendar-plus"></i> Mark Unavailable
                </button>
            </div>
            
            <div class="calendar-grid">
                <?php
                $today = date('N') - 1;
                for ($i = 0; $i < 7; $i++):
                    $dayName = $weekDays[$i];
                    $isToday = ($i == $today);
                    $completion = $completionRates[$i] ?? 0;
                    $hasWorkout = $completion > 0;
                ?>
                <div class="day-cell <?php echo $isToday ? 'today' : ''; ?> <?php echo $hasWorkout ? 'workout' : 'rest'; ?>">
                    <div class="small"><?php echo $dayName; ?></div>
                    <?php if ($hasWorkout): ?>
                        <div class="small text-success"><?php echo $completion; ?>%</div>
                    <?php else: ?>
                        <div class="small text-muted">Rest</div>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        </section>

        <!-- Progress Photos -->
        <section class="progress-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-images"></i>
                    <h3 class="mb-0">Progress Photos</h3>
                </div>
                <button class="btn-client" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                    <i class="bi bi-cloud-upload"></i> Upload Photos
                </button>
            </div>
            
            <div class="photos-grid">
                <?php foreach ($progressPhotos as $photo): ?>
                <div class="photo-card">
                    <div class="photo-placeholder">
                        <i class="bi bi-image fs-1"></i>
                    </div>
                    <div class="photo-info">
                        <h6 class="mb-1"><?php echo ucfirst($photo['body_part'] ?? 'Progress'); ?></h6>
                        <p class="mb-0 small text-muted"><?php echo date('M d, Y', strtotime($photo['photo_date'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($progressPhotos)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-camera fs-1"></i>
                    <p class="mt-2 mb-0">No photos yet</p>
                    <small>Upload your first progress photo!</small>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Leaderboard -->
        <section class="leaderboard-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-trophy"></i>
                    <h3 class="mb-0">Weekly Leaderboard</h3>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="leaderboardToggle" checked>
                    <label class="form-check-label" for="leaderboardToggle">Opt-in</label>
                </div>
            </div>
            
            <p class="text-muted">Ranked by workout consistency and progress</p>
            
            <div class="leaderboard-list">
                <div class="leaderboard-item me">
                    <div class="rank">3</div>
                    <div>
                        <h6 class="mb-1">You</h6>
                        <p class="mb-0 small text-muted">92% completion • -5.2kg</p>
                    </div>
                </div>
                <div class="leaderboard-item">
                    <div class="rank">1</div>
                    <div>
                        <h6 class="mb-1">Emma Davis</h6>
                        <p class="mb-0 small text-muted">95% completion • +3.5kg muscle</p>
                    </div>
                </div>
                <div class="leaderboard-item">
                    <div class="rank">2</div>
                    <div>
                        <h6 class="mb-1">David Wilson</h6>
                        <p class="mb-0 small text-muted">88% completion • +15% strength</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Body Measurements -->
        <section class="progress-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-rulers"></i>
                    <h3 class="mb-0">Body Measurements</h3>
                </div>
                <button class="btn-outline-client" data-bs-toggle="modal" data-bs-target="#measurementModal">
                    <i class="bi bi-plus-circle"></i> Add Measurement
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tbody>
                            <tr><th>Weight</th><td><?php echo $measurements['weight'] ?? '--'; ?> kg</td><td class="text-success">-5.2 kg</td></tr>
                            <tr><th>Waist</th><td><?php echo $measurements['waist'] ?? '--'; ?> cm</td><td class="text-success">-8 cm</td></tr>
                            <tr><th>Chest</th><td><?php echo $measurements['chest'] ?? '--'; ?> cm</td><td class="text-success">-3 cm</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tbody>
                            <tr><th>Body Fat %</th><td><?php echo $measurements['body_fat_us_navy'] ?? '--'; ?>%</td><td class="text-success">-4.2%</td></tr>
                            <tr><th>Arms</th><td><?php echo $measurements['arms'] ?? '--'; ?> cm</td><td class="text-warning">+1 cm</td></tr>
                            <tr><th>Thighs</th><td><?php echo $measurements['thighs'] ?? '--'; ?> cm</td><td class="text-success">-3 cm</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <!-- Log Meal Modal -->
    <div class="modal fade" id="logMealModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Meal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../api/nutrition.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Meal Type</label>
                            <select name="meal_type" class="form-select" required>
                                <option value="breakfast">Breakfast</option>
                                <option value="lunch">Lunch</option>
                                <option value="dinner">Dinner</option>
                                <option value="snack">Snack</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Food Item</label>
                            <input type="text" name="food_name" class="form-control" placeholder="e.g., Grilled Chicken Breast" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Quantity (grams)</label>
                                <input type="number" name="quantity" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Calories</label>
                                <input type="number" name="calories" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-client">Save Meal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Photo Modal -->
    <div class="modal fade" id="uploadPhotoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Progress Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../api/upload-photo.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Body Part</label>
                            <select name="body_part" class="form-select">
                                <option value="front">Front View</option>
                                <option value="side">Side View</option>
                                <option value="back">Back View</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-client">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Measurement Modal -->
    <div class="modal fade" id="measurementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Body Measurements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../api/measurements.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Weight (kg)</label>
                                <input type="number" name="weight" class="form-control" step="0.1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Body Fat %</label>
                                <input type="number" name="body_fat" class="form-control" step="0.1">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Waist (cm)</label>
                                <input type="number" name="waist" class="form-control" step="0.1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Chest (cm)</label>
                                <input type="number" name="chest" class="form-control" step="0.1">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-client">Save Measurements</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Unavailable Modal -->
    <div class="modal fade" id="unavailableModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark Unavailable Days</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../api/unavailable.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason (Optional)</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="e.g., Vacation, Work travel..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-client">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle"><i class="bi bi-moon-fill" id="themeIcon"></i></button>

    <!-- Workout Execution Modal -->
    <div class="modal fade" id="workoutModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-activity"></i> Workout Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="timer-display text-center" style="font-size: 3rem; font-family: monospace;" id="timerDisplay">00:00</div>
                    <div class="text-center mb-4">
                        <button class="btn btn-success" id="startTimerBtn"><i class="bi bi-play-fill"></i> Start</button>
                        <button class="btn btn-warning" id="pauseTimerBtn"><i class="bi bi-pause-fill"></i> Pause</button>
                        <button class="btn btn-secondary" id="resetTimerBtn"><i class="bi bi-arrow-clockwise"></i> Reset</button>
                    </div>
                    
                    <?php foreach ($exercises as $index => $exercise): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5>Exercise <?php echo $index + 1; ?>: <?php echo htmlspecialchars($exercise['name']); ?></h5>
                            <p class="text-muted">Target: <?php echo $exercise['reps']; ?> reps • <?php echo $exercise['weight']; ?> lbs</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Weight (lbs)</label>
                                    <input type="number" class="form-control set-weight" value="<?php echo $exercise['weight']; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label>Reps</label>
                                    <input type="number" class="form-control set-reps" value="<?php echo $exercise['reps']; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary w-100 log-set-btn">Log Set</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="completeWorkoutBtn">Complete Workout</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;
        
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        
        function updateThemeIcon(theme) {
            themeIcon.className = theme === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
        }
        updateThemeIcon(savedTheme);
        
        themeToggle.addEventListener('click', () => {
            const newTheme = htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });

        // Sidebar Toggle
        const menuToggle = document.getElementById('menuToggle');
        const clientSidebar = document.getElementById('clientSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        function closeSidebar() {
            if (clientSidebar) clientSidebar.classList.remove('open');
            if (sidebarOverlay) sidebarOverlay.classList.remove('active');
        }
        
        function openSidebar() {
            if (clientSidebar) clientSidebar.classList.add('open');
            if (sidebarOverlay) sidebarOverlay.classList.add('active');
        }
        
        if (menuToggle) {
            menuToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                clientSidebar.classList.contains('open') ? closeSidebar() : openSidebar();
            });
        }
        
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }
        
        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) closeSidebar();
        });

        // Workout Modal
        const startWorkoutBtn = document.getElementById('startWorkout');
        const workoutModal = new bootstrap.Modal(document.getElementById('workoutModal'));
        
        if (startWorkoutBtn) {
            startWorkoutBtn.addEventListener('click', () => {
                workoutModal.show();
            });
        }

        // Timer
        let timerInterval;
        let seconds = 0;
        let isRunning = false;
        const timerDisplay = document.getElementById('timerDisplay');
        const startTimerBtn = document.getElementById('startTimerBtn');
        const pauseTimerBtn = document.getElementById('pauseTimerBtn');
        const resetTimerBtn = document.getElementById('resetTimerBtn');
        
        function formatTime(secs) {
            const mins = Math.floor(secs / 60);
            const remainingSecs = secs % 60;
            return `${mins.toString().padStart(2, '0')}:${remainingSecs.toString().padStart(2, '0')}`;
        }
        
        if (startTimerBtn) {
            startTimerBtn.addEventListener('click', () => {
                if (!isRunning) {
                    isRunning = true;
                    timerInterval = setInterval(() => {
                        seconds++;
                        timerDisplay.textContent = formatTime(seconds);
                    }, 1000);
                }
            });
        }
        
        if (pauseTimerBtn) {
            pauseTimerBtn.addEventListener('click', () => {
                isRunning = false;
                clearInterval(timerInterval);
            });
        }
        
        if (resetTimerBtn) {
            resetTimerBtn.addEventListener('click', () => {
                isRunning = false;
                clearInterval(timerInterval);
                seconds = 0;
                timerDisplay.textContent = formatTime(seconds);
            });
        }

        // Log Set Functionality
        document.querySelectorAll('.log-set-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.textContent = '✓ Logged';
                this.classList.remove('btn-primary');
                this.classList.add('btn-success');
                this.disabled = true;
            });
        });

        // Complete Workout
        const completeWorkoutBtn = document.getElementById('completeWorkoutBtn');
        if (completeWorkoutBtn) {
            completeWorkoutBtn.addEventListener('click', () => {
                alert('Workout completed! Great job!');
                workoutModal.hide();
            });
        }

        // Macro Progress Charts
        function createMacroChart(canvasId, value, goal, color) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const percentage = Math.min((value / goal) * 100, 100);
            
            canvas.width = 60;
            canvas.height = 60;
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [percentage, 100 - percentage],
                        backgroundColor: [color, 'rgba(0,0,0,0.1)'],
                        borderWidth: 0,
                        circumference: 270,
                        rotation: 135
                    }]
                },
                options: {
                    cutout: '80%',
                    responsive: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } }
                }
            });
        }
        
        createMacroChart('calorieChart', <?php echo $nutritionToday['total_calories']; ?>, <?php echo $dailyGoals['calories']; ?>, '#4A6FA5');
        createMacroChart('proteinChart', <?php echo $nutritionToday['total_protein']; ?>, <?php echo $dailyGoals['protein']; ?>, '#28a745');
        createMacroChart('carbChart', <?php echo $nutritionToday['total_carbs']; ?>, <?php echo $dailyGoals['carbs']; ?>, '#ffc107');
        createMacroChart('fatChart', <?php echo $nutritionToday['total_fat']; ?>, <?php echo $dailyGoals['fat']; ?>, '#dc3545');

        // Weight Progress Chart
        const weightCtx = document.getElementById('weightChart');
        if (weightCtx) {
            new Chart(weightCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($weightLabels); ?>,
                    datasets: [{
                        label: 'Weight (kg)',
                        data: <?php echo json_encode($weightValues); ?>,
                        borderColor: 'rgba(74, 111, 165, 1)',
                        backgroundColor: 'rgba(74, 111, 165, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: false, title: { display: true, text: 'Weight (kg)' } } }
                }
            });
        }

        // Completion Chart
        const completionCtx = document.getElementById('completionChart');
        if (completionCtx) {
            new Chart(completionCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($weekDays); ?>,
                    datasets: [{
                        label: 'Completion %',
                        data: <?php echo json_encode($completionRates); ?>,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } }
                }
            });
        }

        // Leaderboard opt-in toggle
        const leaderboardToggle = document.getElementById('leaderboardToggle');
        if (leaderboardToggle) {
            leaderboardToggle.addEventListener('change', function() {
                alert(this.checked ? 'You are now visible on the leaderboard!' : 'You are now hidden from the leaderboard.');
            });
        }
    </script>
</body>
</html>