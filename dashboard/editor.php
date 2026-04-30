<?php
// File: C:\xampp\htdocs\appF\dashboard\editor.php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/middleware.php';
require_once '../includes/roles.php';

// Apply middleware - only editor can access
applyMiddleware(['authMiddleware', 'noCacheMiddleware'], function() {
    roleMiddleware(['editor']);
});

$userId = $_SESSION['user_id'];
$db = getDB();

// Get editor's assigned clients
$assignedClients = $db->prepare("
    SELECT u.id, u.name, u.email, 
           (SELECT COUNT(*) FROM client_workouts WHERE client_id = u.id AND status = 'completed') as workouts_completed,
           (SELECT COUNT(*) FROM meal_logs WHERE client_id = u.id AND meal_date = CURDATE()) as meals_today,
           (SELECT MAX(start_date) FROM client_workouts WHERE client_id = u.id) as last_workout
    FROM users u
    JOIN editor_assignments ea ON u.id = ea.client_id
    WHERE ea.editor_id = ?
    ORDER BY u.name
");
$assignedClients->bind_param("i", $userId);
$assignedClients->execute();
$clientsResult = $assignedClients->get_result();
$clients = $clientsResult ? $clientsResult->fetch_all(MYSQLI_ASSOC) : [];

// Get total counts
$totalClients = count($clients);

$workoutsLogged = $db->prepare("
    SELECT COUNT(*) FROM client_workout_exercises cwe
    JOIN client_workouts cw ON cwe.client_workout_id = cw.id
    WHERE cw.client_id IN (SELECT client_id FROM editor_assignments WHERE editor_id = ?)
    AND cwe.logged_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$workoutsLogged->bind_param("i", $userId);
$workoutsLogged->execute();
$workoutsResult = $workoutsLogged->get_result();
$workoutsCount = $workoutsResult ? ($workoutsResult->fetch_row()[0] ?? 0) : 0;

$mealsTracked = $db->prepare("
    SELECT COUNT(*) FROM meal_logs
    WHERE client_id IN (SELECT client_id FROM editor_assignments WHERE editor_id = ?)
    AND logged_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$mealsTracked->bind_param("i", $userId);
$mealsTracked->execute();
$mealsResult = $mealsTracked->get_result();
$mealsCount = $mealsResult ? ($mealsResult->fetch_row()[0] ?? 0) : 0;

$commentsAdded = $db->prepare("
    SELECT COUNT(*) FROM activity_logs
    WHERE user_id = ? AND action = 'add_comment'
");
$commentsAdded->bind_param("i", $userId);
$commentsAdded->execute();
$commentsResult = $commentsAdded->get_result();
$commentsCount = $commentsResult ? ($commentsResult->fetch_row()[0] ?? 0) : 0;

// Get today's pending workouts for assigned clients
$pendingWorkouts = $db->prepare("
    SELECT cw.*, u.name as client_name, wt.name as workout_name
    FROM client_workouts cw
    JOIN users u ON cw.client_id = u.id
    LEFT JOIN workout_templates wt ON cw.template_id = wt.id
    WHERE cw.client_id IN (SELECT client_id FROM editor_assignments WHERE editor_id = ?)
    AND cw.start_date <= CURDATE()
    AND cw.status IN ('scheduled', 'in_progress')
    ORDER BY cw.start_date
    LIMIT 3
");
$pendingWorkouts->bind_param("i", $userId);
$pendingWorkouts->execute();
$pendingResult = $pendingWorkouts->get_result();
$pendingWorkouts = $pendingResult ? $pendingResult->fetch_all(MYSQLI_ASSOC) : [];

// Get recent comments
$recentComments = $db->prepare("
    SELECT al.*, u.name as user_name
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    WHERE (al.user_id IN (SELECT client_id FROM editor_assignments WHERE editor_id = ?) OR al.user_id = ?)
    AND al.action = 'add_comment'
    ORDER BY al.created_at DESC
    LIMIT 5
");
$recentComments->bind_param("ii", $userId, $userId);
$recentComments->execute();
$commentsResult = $recentComments->get_result();
$recentComments = $commentsResult ? $commentsResult->fetch_all(MYSQLI_ASSOC) : [];

// Get current client for forms (default to first client)
$currentClientId = isset($_GET['client']) ? (int)$_GET['client'] : ($clients[0]['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Dashboard - FitCoach Pro</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* Keep all your existing CSS styles - they're fine */
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
        }

        .editor-nav {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .editor-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .editor-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: white;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .editor-details h4 {
            margin: 0;
            font-size: 1.1rem;
        }

        .editor-details small {
            opacity: 0.8;
        }

        .nav-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .editor-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--accent-color), #20c997);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: rgba(74, 111, 165, 0.1);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
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

        .client-selector {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .client-selector select {
            max-width: 300px;
        }

        .editor-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            border-bottom: 2px solid rgba(0,0,0,0.1);
            padding-bottom: 10px;
        }

        .tab-btn {
            padding: 10px 20px;
            background: transparent;
            border: none;
            border-radius: 8px;
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn.active {
            background: var(--primary-color);
            color: white;
        }

        .tab-btn:hover:not(.active) {
            background: rgba(0,0,0,0.05);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .workout-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .client-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .client-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .exercise-list {
            margin-bottom: 20px;
        }

        .exercise-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary-color);
            flex-wrap: wrap;
            gap: 15px;
        }

        .exercise-inputs {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .input-group-small {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .input-group-small label {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .input-group-small input {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: var(--card-bg);
            color: var(--text-color);
        }

        .meal-log-form {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .food-item {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: var(--light-bg);
            border-radius: 8px;
            flex-wrap: wrap;
        }

        .food-details {
            flex: 1;
        }

        .comment-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .comment-box {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .comment-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
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

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }

        .btn-secondary {
            background: var(--light-bg);
            border: 1px solid #ddd;
            padding: 10px 25px;
            border-radius: 8px;
            color: var(--text-color);
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .form-control, .form-select, textarea {
            background-color: var(--light-bg);
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 15px;
            color: var(--text-color);
            transition: all 0.3s;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] textarea {
            background-color: #2d2d2d;
            border-color: #444;
        }

        .form-control:focus, .form-select:focus, textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(74, 111, 165, 0.25);
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

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .badge-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .badge-warning {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }

        .badge-info {
            background: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-container {
                flex-direction: column;
                text-align: center;
            }
            
            .editor-tabs {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 5px;
            }
            
            .exercise-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .exercise-inputs {
                width: 100%;
                justify-content: space-between;
            }
            
            .client-selector {
                flex-direction: column;
            }
            
            .client-selector select {
                width: 100%;
                max-width: 100%;
            }
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
    <!-- Top Navigation -->
    <nav class="editor-nav">
        <div class="nav-container">
            <div class="editor-info">
                <div class="editor-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
                <div class="editor-details">
                    <h4><?php echo htmlspecialchars($_SESSION['user_name']); ?></h4>
                    <small>Assistant Editor</small>
                </div>
            </div>
            
            <div class="nav-actions">
                <button class="btn btn-secondary" onclick="window.location.href='/appF/index.php'">
                    <i class="bi bi-house-door me-1"></i> Home
                </button>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> Profile
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-bell me-2"></i> Notifications</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/appF/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="editor-container">
        <!-- Welcome Card -->
        <div class="welcome-card">
            <h2><i class="bi bi-clipboard-check me-2"></i> Editor Dashboard</h2>
            <p class="mb-0">Assist coaches with client management, workout logging, meal tracking, and progress monitoring.</p>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $totalClients; ?></div>
                        <div class="stat-label">Assigned Clients</div>
                    </div>
                </div>
                <small class="text-muted">Clients you assist with</small>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $workoutsCount; ?></div>
                        <div class="stat-label">Workouts Logged</div>
                    </div>
                </div>
                <small class="text-muted">This month</small>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="bi bi-egg-fried"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $mealsCount; ?></div>
                        <div class="stat-label">Meals Tracked</div>
                    </div>
                </div>
                <small class="text-muted">Client nutrition logging</small>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $commentsCount; ?></div>
                        <div class="stat-label">Comments Added</div>
                    </div>
                </div>
                <small class="text-muted">Progress feedback</small>
            </div>
        </div>

        <!-- Client Selector -->
        <div class="client-selector">
            <div>
                <i class="bi bi-person-badge me-2"></i>
                <strong>Working with client:</strong>
            </div>
            <select id="clientSelect" class="form-select" onchange="window.location.href='?client=' + this.value">
                <?php foreach ($clients as $client): ?>
                <option value="<?php echo $client['id']; ?>" <?php echo $currentClientId == $client['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($client['name']); ?> 
                    (<?php echo $client['workouts_completed']; ?> workouts)
                </option>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                <option value="">No clients assigned yet</option>
                <?php endif; ?>
            </select>
        </div>

        <!-- Tabs Navigation -->
        <div class="editor-tabs">
            <button class="tab-btn active" data-tab="workouts">
                <i class="bi bi-activity"></i> Workout Logging
            </button>
            <button class="tab-btn" data-tab="nutrition">
                <i class="bi bi-egg-fried"></i> Nutrition Tracking
            </button>
            <button class="tab-btn" data-tab="comments">
                <i class="bi bi-chat-dots"></i> Add Comments
            </button>
            <button class="tab-btn" data-tab="progress">
                <i class="bi bi-images"></i> Progress Photos
            </button>
            <button class="tab-btn" data-tab="clients">
                <i class="bi bi-people"></i> Client List
            </button>
        </div>

        <!-- Workout Logging Tab -->
        <div id="workouts" class="tab-content active">
            <h3 class="mb-4">Log Client Workouts</h3>
            
            <?php if (!empty($pendingWorkouts)): ?>
                <?php foreach ($pendingWorkouts as $workout): ?>
                <div class="workout-card">
                    <div class="client-info">
                        <div class="client-avatar"><?php echo strtoupper(substr($workout['client_name'], 0, 2)); ?></div>
                        <div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($workout['client_name']); ?></h5>
                            <p class="mb-0 text-muted">
                                Today's Workout: <?php echo htmlspecialchars($workout['workout_name'] ?? 'Custom Workout'); ?> • 
                                Status: <span class="badge badge-warning"><?php echo ucfirst($workout['status']); ?></span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="exercise-list">
                        <h6>Exercises to Log:</h6>
                        <div class="exercise-item">
                            <div>
                                <strong>Bench Press</strong>
                                <p class="mb-0 small text-muted">3 sets × 8 reps @ 70% 1RM</p>
                            </div>
                            <div class="exercise-inputs">
                                <div class="input-group-small">
                                    <label>Weight (lbs)</label>
                                    <input type="number" value="185">
                                </div>
                                <div class="input-group-small">
                                    <label>Sets</label>
                                    <input type="number" value="3">
                                </div>
                                <div class="input-group-small">
                                    <label>Reps</label>
                                    <input type="number" value="8">
                                </div>
                                <button class="btn btn-primary btn-sm">
                                    <i class="bi bi-check"></i> Log
                                </button>
                            </div>
                        </div>
                        
                        <div class="exercise-item">
                            <div>
                                <strong>Incline Dumbbell Press</strong>
                                <p class="mb-0 small text-muted">3 sets × 10 reps</p>
                            </div>
                            <div class="exercise-inputs">
                                <div class="input-group-small">
                                    <label>Weight (lbs)</label>
                                    <input type="number" value="65">
                                </div>
                                <div class="input-group-small">
                                    <label>Sets</label>
                                    <input type="number" value="3">
                                </div>
                                <div class="input-group-small">
                                    <label>Reps</label>
                                    <input type="number" value="10">
                                </div>
                                <button class="btn btn-primary btn-sm">
                                    <i class="bi bi-check"></i> Log
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-secondary">
                            <i class="bi bi-skip-backward me-1"></i> Previous Client
                        </button>
                        <button class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Complete Workout
                        </button>
                        <button class="btn btn-secondary">
                            Next Client <i class="bi bi-skip-forward ms-1"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="workout-card text-center">
                <i class="bi bi-check-circle fs-1 text-success mb-3 d-block"></i>
                <h5>All caught up!</h5>
                <p class="text-muted mb-0">No pending workouts for today. Check back later or help with nutrition tracking.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Nutrition Tracking Tab -->
        <div id="nutrition" class="tab-content">
            <h3 class="mb-4">Track Client Nutrition</h3>
            
            <div class="meal-log-form">
                <div class="mb-4">
                    <label for="mealClientSelect" class="form-label">Select Client</label>
                    <select class="form-select" id="mealClientSelect">
                        <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Meal Type</label>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mealType" id="breakfast" checked>
                            <label class="form-check-label" for="breakfast">Breakfast</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mealType" id="lunch">
                            <label class="form-check-label" for="lunch">Lunch</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mealType" id="dinner">
                            <label class="form-check-label" for="dinner">Dinner</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mealType" id="snack">
                            <label class="form-check-label" for="snack">Snack</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Food Items</label>
                    <div class="food-item">
                        <div class="food-details">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <strong>Grilled Chicken Breast</strong>
                                <span class="badge badge-info">Custom Entry</span>
                            </div>
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <span>Protein: 31g • Carbs: 0g • Fat: 3.6g</span>
                                <span>200g serving</span>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    
                    <div class="food-item">
                        <div class="food-details">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <strong>Brown Rice</strong>
                                <span class="badge badge-success">Database</span>
                            </div>
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <span>Protein: 5g • Carbs: 45g • Fat: 2g</span>
                                <span>150g cooked</span>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    
                    <button class="btn btn-secondary w-100 mt-2">
                        <i class="bi bi-plus-circle me-1"></i> Add Food Item
                    </button>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-3 col-6">
                        <label class="form-label">Total Calories</label>
                        <input type="text" class="form-control" value="485" readonly>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">Protein (g)</label>
                        <input type="text" class="form-control" value="36" readonly>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">Carbs (g)</label>
                        <input type="text" class="form-control" value="45" readonly>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">Fat (g)</label>
                        <input type="text" class="form-control" value="5.6" readonly>
                    </div>
                </div>
                
                <button class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Meal Log
                </button>
            </div>
        </div>

        <!-- Comments Tab -->
        <div id="comments" class="tab-content">
            <h3 class="mb-4">Add Progress Comments</h3>
            
            <div class="comment-card">
                <div class="mb-4">
                    <label for="commentClient" class="form-label">Select Client</label>
                    <select class="form-select" id="commentClient">
                        <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="commentType" class="form-label">Comment Type</label>
                    <select class="form-select" id="commentType">
                        <option selected>Workout Performance</option>
                        <option>Nutrition Adherence</option>
                        <option>Progress Photo Feedback</option>
                        <option>General Feedback</option>
                        <option>Motivation</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="commentText" class="form-label">Your Comment</label>
                    <textarea class="form-control" id="commentText" rows="5" placeholder="Add your feedback for the client..."></textarea>
                </div>
                
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <button class="btn btn-secondary">
                        <i class="bi bi-paperclip me-1"></i> Attach Photo
                    </button>
                    <button class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Post Comment
                    </button>
                </div>
            </div>
            
            <h4 class="mb-3">Recent Comments</h4>
            <?php foreach ($recentComments as $comment): ?>
            <div class="comment-box">
                <div class="comment-meta">
                    <span>
                        <strong><?php echo htmlspecialchars($comment['user_name'] ?? 'Client'); ?></strong> 
                        • <?php echo htmlspecialchars($comment['details'] ?? 'General feedback'); ?>
                    </span>
                    <span><?php echo formatDate($comment['created_at']); ?></span>
                </div>
                <p class="mb-0"><?php echo htmlspecialchars($comment['details'] ?? 'Great progress!'); ?></p>
            </div>
            <?php endforeach; ?>
            <?php if (empty($recentComments)): ?>
            <p class="text-center text-muted">No comments yet. Add your first comment above!</p>
            <?php endif; ?>
        </div>

        <!-- Progress Photos Tab -->
        <div id="progress" class="tab-content">
            <h3 class="mb-4">Manage Progress Photos</h3>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <label class="form-label mb-0">Client: <strong>Current Selection</strong></label>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                        <i class="bi bi-cloud-upload me-1"></i> Upload New Photos
                    </button>
                </div>
            </div>
            
            <div class="photo-grid">
                <div class="photo-card">
                    <div class="photo-placeholder">
                        <i class="bi bi-image fs-1"></i>
                    </div>
                    <div class="photo-info">
                        <h6 class="mb-1">Front View</h6>
                        <p class="mb-0 small text-muted"><?php echo date('M d, Y'); ?></p>
                        <button class="btn btn-sm btn-outline-primary mt-2 w-100">
                            <i class="bi bi-chat me-1"></i> Add Comment
                        </button>
                    </div>
                </div>
                
                <div class="photo-card">
                    <div class="photo-placeholder">
                        <i class="bi bi-image fs-1"></i>
                    </div>
                    <div class="photo-info">
                        <h6 class="mb-1">Side View</h6>
                        <p class="mb-0 small text-muted"><?php echo date('M d, Y', strtotime('-1 week')); ?></p>
                        <button class="btn btn-sm btn-outline-primary mt-2 w-100">
                            <i class="bi bi-chat me-1"></i> Add Comment
                        </button>
                    </div>
                </div>
                
                <div class="photo-card">
                    <div class="photo-placeholder">
                        <i class="bi bi-image fs-1"></i>
                    </div>
                    <div class="photo-info">
                        <h6 class="mb-1">Back View</h6>
                        <p class="mb-0 small text-muted"><?php echo date('M d, Y', strtotime('-2 weeks')); ?></p>
                        <button class="btn btn-sm btn-outline-primary mt-2 w-100">
                            <i class="bi bi-chat me-1"></i> Add Comment
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4 flex-wrap gap-2">
                <button class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Previous Week
                </button>
                <span class="text-muted">Week of <?php echo date('M d, Y'); ?></span>
                <button class="btn btn-secondary">
                    Next Week <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <!-- Client List Tab -->
        <div id="clients" class="tab-content">
            <h3 class="mb-4">Assigned Clients</h3>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Last Workout</th>
                            <th>Last Meal Log</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="client-avatar me-2" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                        <?php echo strtoupper(substr($client['name'], 0, 2)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($client['name']); ?></strong>
                                        <div class="small text-muted"><?php echo $client['workouts_completed']; ?> workouts completed</div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $client['last_workout'] ? formatDate($client['last_workout']) : 'Never'; ?></td>
                            <td><?php echo $client['meals_today'] ?? 0; ?> today</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="window.location.href='?client=<?php echo $client['id']; ?>#workouts'">
                                    <i class="bi bi-activity"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="window.location.href='?client=<?php echo $client['id']; ?>#nutrition'">
                                    <i class="bi bi-egg-fried"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($clients)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No clients assigned yet.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                <form action="/appF/api/upload-photo.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Client</label>
                            <select name="client_id" class="form-select" required>
                                <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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
                            <label class="form-label">Comments (Optional)</label>
                            <textarea name="comments" class="form-control" rows="3" placeholder="Add notes about the progress..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
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

        // Tab Switching
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.getAttribute('data-tab');
                
                tabButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === tabId) {
                        content.classList.add('active');
                    }
                });
            });
        });

        // Handle URL hash for tab navigation
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const targetTab = document.querySelector(`.tab-btn[data-tab="${hash}"]`);
            if (targetTab) {
                targetTab.click();
            }
        }

        // Simulate workout logging
        const logButtons = document.querySelectorAll('.exercise-inputs .btn');
        logButtons.forEach(button => {
            button.addEventListener('click', function() {
                const exerciseItem = this.closest('.exercise-item');
                const exerciseName = exerciseItem.querySelector('strong').textContent;
                const clientName = this.closest('.workout-card')?.querySelector('.client-info h5')?.textContent || 'client';
                
                this.innerHTML = '<i class="bi bi-check-circle"></i> Logged';
                this.classList.remove('btn-primary');
                this.classList.add('btn-success');
                this.disabled = true;
                
                showNotification(`Logged ${exerciseName} for ${clientName}`);
            });
        });

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'alert alert-success position-fixed';
            notification.style.cssText = `
                top: 80px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                animation: slideIn 0.3s ease;
            `;
            notification.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 3000);
        }

        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>