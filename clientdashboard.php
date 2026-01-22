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

        /* Client Header */
        .client-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }

        .client-profile {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 0 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .client-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            border: 5px solid rgba(255,255,255,0.3);
        }

        .client-info h2 {
            margin: 0 0 5px 0;
            font-weight: 600;
        }

        .client-info p {
            margin: 0;
            opacity: 0.9;
        }

        .client-stats {
            display: flex;
            gap: 30px;
            margin-top: 15px;
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
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Main Content */
        .client-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }

        /* Today's Overview */
        .today-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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

        /* Workout Card */
        .workout-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending { background: rgba(255, 193, 7, 0.1); color: var(--warning-color); }
        .status-completed { background: rgba(40, 167, 69, 0.1); color: var(--success-color); }
        .status-missed { background: rgba(220, 53, 69, 0.1); color: var(--danger-color); }

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
        }

        .start-workout-btn {
            background: linear-gradient(45deg, var(--success-color), #20c997);
            border: none;
            padding: 12px 30px;
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

        /* Nutrition Card */
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
            position: relative;
        }

        .macro-progress {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px;
            position: relative;
        }

        .macro-circle canvas {
            position: absolute;
            top: 0;
            left: 0;
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
            height: 100%;
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
            transition: transform 0.3s;
        }

        .photo-card:hover {
            transform: translateY(-5px);
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
            transition: transform 0.3s;
        }

        .leaderboard-item:hover {
            transform: translateX(5px);
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

        /* Navigation */
        .client-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border-top: 1px solid rgba(0,0,0,0.1);
            padding: 10px 0;
            z-index: 1000;
            display: flex;
            justify-content: space-around;
        }

        .nav-item {
            text-align: center;
            color: var(--text-light);
            text-decoration: none;
            padding: 10px;
            border-radius: 10px;
            transition: all 0.3s;
            flex: 1;
            max-width: 100px;
        }

        .nav-item.active {
            color: var(--primary-color);
            background: rgba(74, 111, 165, 0.1);
        }

        .nav-item:hover {
            color: var(--primary-color);
        }

        .nav-icon {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 5px;
        }

        .nav-label {
            font-size: 0.8rem;
        }

        /* Buttons */
        .btn-client {
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

        .btn-client:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 111, 165, 0.3);
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
            top: 20px;
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

        /* Responsive */
        @media (max-width: 768px) {
            .client-profile {
                flex-direction: column;
                text-align: center;
            }
            
            .client-stats {
                justify-content: center;
            }
            
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
            
            .client-nav {
                display: flex;
            }
        }

        @media (min-width: 769px) {
            .client-container {
                padding-bottom: 80px; /* Space for fixed nav on mobile */
            }
            
            .client-nav {
                display: none; /* Hide bottom nav on desktop */
            }
        }

        /* Workout Execution Modal */
        .workout-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .workout-modal.active {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .workout-panel {
            background: var(--card-bg);
            border-radius: 20px;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
        }

        .timer-display {
            font-size: 3rem;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            font-family: monospace;
        }

        .timer-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .set-log {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: var(--light-bg);
            border-radius: 10px;
        }

        .set-number {
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
    </style>
</head>
<body>
    <!-- Client Header -->
    <header class="client-header">
        <div class="client-profile">
            <div class="client-avatar-large">MW</div>
            <div class="client-info">
                <h2>Michael Wong</h2>
                <p>Weight Loss Program • Week 8 of 12</p>
                <div class="client-stats">
                    <div class="stat-item">
                        <span class="stat-number">-5.2kg</span>
                        <span class="stat-label">Weight Lost</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">92%</span>
                        <span class="stat-label">Adherence</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">15</span>
                        <span class="stat-label">Workouts</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">28</span>
                        <span class="stat-label">Meals Logged</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="client-container">
        <!-- Today's Overview -->
        <section class="today-overview">
            <!-- Today's Workout Card (FR-16, FR-17) -->
            <div class="overview-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-activity"></i>
                        <h3 class="mb-0">Today's Workout</h3>
                    </div>
                    <div class="workout-status">
                        <span class="status-badge status-pending">Pending</span>
                        <span class="text-muted"><i class="bi bi-clock"></i> 45 min</span>
                    </div>
                </div>
                
                <h5>Push Day - Chest & Shoulders</h5>
                <p class="text-muted mb-3">Focus on strength and hypertrophy</p>
                
                <div class="exercise-list">
                    <div class="exercise-item">
                        <div>
                            <strong>Bench Press</strong>
                            <div class="small text-muted">3 sets × 8 reps @ 185lbs</div>
                        </div>
                        <span class="badge bg-secondary">Not Started</span>
                    </div>
                    <div class="exercise-item">
                        <div>
                            <strong>Incline Dumbbell Press</strong>
                            <div class="small text-muted">3 sets × 10 reps @ 65lbs</div>
                        </div>
                        <span class="badge bg-secondary">Not Started</span>
                    </div>
                    <div class="exercise-item">
                        <div>
                            <strong>Shoulder Press</strong>
                            <div class="small text-muted">3 sets × 12 reps @ 40lbs</div>
                        </div>
                        <span class="badge bg-secondary">Not Started</span>
                    </div>
                </div>
                
                <button class="start-workout-btn" id="startWorkout">
                    <i class="bi bi-play-circle"></i> Start Workout
                </button>
            </div>

            <!-- Nutrition Summary Card (FR-30, FR-32) -->
            <div class="overview-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-egg-fried"></i>
                        <h3 class="mb-0">Today's Nutrition</h3>
                    </div>
                    <span class="text-muted">Goal: 2,150 calories</span>
                </div>
                
                <div class="nutrition-summary">
                    <div class="macro-circle">
                        <div class="macro-progress">
                            <canvas id="calorieChart" width="60" height="60"></canvas>
                        </div>
                        <span class="macro-value">1,850</span>
                        <span class="macro-label">Calories</span>
                        <small class="text-success">300 under</small>
                    </div>
                    <div class="macro-circle">
                        <div class="macro-progress">
                            <canvas id="proteinChart" width="60" height="60"></canvas>
                        </div>
                        <span class="macro-value">142g</span>
                        <span class="macro-label">Protein</span>
                        <small class="text-warning">8g under</small>
                    </div>
                    <div class="macro-circle">
                        <div class="macro-progress">
                            <canvas id="carbChart" width="60" height="60"></canvas>
                        </div>
                        <span class="macro-value">210g</span>
                        <span class="macro-label">Carbs</span>
                        <small class="text-success">40g under</small>
                    </div>
                    <div class="macro-circle">
                        <div class="macro-progress">
                            <canvas id="fatChart" width="60" height="60"></canvas>
                        </div>
                        <span class="macro-value">58g</span>
                        <span class="macro-label">Fat</span>
                        <small class="text-success">7g under</small>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button class="btn-client w-100">
                        <i class="bi bi-plus-circle"></i> Log Meal
                    </button>
                </div>
            </div>
        </section>

        <!-- Progress Tracking -->
        <section class="progress-section">
            <!-- Weight Progress Chart (FR-27) -->
            <div class="progress-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-graph-down"></i>
                        <h3 class="mb-0">Weight Progress</h3>
                    </div>
                    <span class="text-success">-5.2kg total</span>
                </div>
                <div class="chart-container">
                    <canvas id="weightChart"></canvas>
                </div>
            </div>

            <!-- Workout Completion -->
            <div class="progress-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-check-circle"></i>
                        <h3 class="mb-0">Workout Completion</h3>
                    </div>
                    <span class="text-success">92% rate</span>
                </div>
                <div class="chart-container">
                    <canvas id="completionChart"></canvas>
                </div>
            </div>
        </section>

        <!-- Week Calendar (FR-16) -->
        <section class="week-calendar">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-calendar-week"></i>
                    <h3 class="mb-0">This Week's Schedule</h3>
                </div>
                <button class="btn-outline-client">
                    <i class="bi bi-calendar-plus"></i> Mark Unavailable
                </button>
            </div>
            
            <div class="calendar-grid">
                <div class="day-cell">
                    <div class="small">Mon</div>
                    <div class="fw-bold">14</div>
                    <div class="small text-success">✓ Completed</div>
                </div>
                <div class="day-cell">
                    <div class="small">Tue</div>
                    <div class="fw-bold">15</div>
                    <div class="small text-success">✓ Completed</div>
                </div>
                <div class="day-cell today workout">
                    <div class="small">Wed</div>
                    <div class="fw-bold">16</div>
                    <div class="small">Push Day</div>
                </div>
                <div class="day-cell workout">
                    <div class="small">Thu</div>
                    <div class="fw-bold">17</div>
                    <div class="small">Pull Day</div>
                </div>
                <div class="day-cell workout">
                    <div class="small">Fri</div>
                    <div class="fw-bold">18</div>
                    <div class="small">Leg Day</div>
                </div>
                <div class="day-cell rest">
                    <div class="small">Sat</div>
                    <div class="fw-bold">19</div>
                    <div class="small">Rest Day</div>
                </div>
                <div class="day-cell rest">
                    <div class="small">Sun</div>
                    <div class="fw-bold">20</div>
                    <div class="small">Rest Day</div>
                </div>
            </div>
        </section>

        <!-- Progress Photos (FR-28) -->
        <section class="progress-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-images"></i>
                    <h3 class="mb-0">Progress Photos</h3>
                </div>
                <button class="btn-client">
                    <i class="bi bi-cloud-upload"></i> Upload Photos
                </button>
            </div>
            
            <div class="photos-grid">
                <div class="photo-card">
                    <div class="photo-placeholder">
                        <i class="bi bi-image fs-1"></i>
                    </div>
                    <div class="photo-info">
                        <h6 class="mb-1">Front View</h6>
                        <p class="mb-0 small text-muted">Week 1 • Jan 2</p>
                        <small class="text-muted"><i class="bi bi-chat"></i> Coach: "Great start!"</small>
                    </div>
                </div>
                <div class="photo-card">
                    <div class="photo-placeholder">
                        <i class="bi bi-image fs-1"></i>
                    </div>
                    <div class="photo-info">
                        <h6 class="mb-1">Side View</h6>
                        <p class="mb-0 small text-muted">Week 4 • Jan 23</p>
                        <small class="text-muted"><i class="bi bi-chat"></i> Coach: "Noticeable changes!"</small>
                    </div>
                </div>
                <div class="photo-card">
                    <div class="photo-placeholder">
                        <i class="bi bi-image fs-1"></i>
                    </div>
                    <div class="photo-info">
                        <h6 class="mb-1">Front View</h6>
                        <p class="mb-0 small text-muted">Week 8 • Feb 20</p>
                        <small class="text-muted"><i class="bi bi-chat"></i> Coach: "Excellent progress!"</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- Leaderboard (FR-39, FR-40) -->
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
                        <h6 class="mb-1">Michael Wong (You)</h6>
                        <p class="mb-0 small text-muted">92% completion • -5.2kg</p>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-primary">Weight Loss</span>
                    </div>
                </div>
                <div class="leaderboard-item">
                    <div class="rank">1</div>
                    <div>
                        <h6 class="mb-1">Emma Davis</h6>
                        <p class="mb-0 small text-muted">95% completion • +3.5kg muscle</p>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-success">Muscle Gain</span>
                    </div>
                </div>
                <div class="leaderboard-item">
                    <div class="rank">2</div>
                    <div>
                        <h6 class="mb-1">David Wilson</h6>
                        <p class="mb-0 small text-muted">88% completion • +15% strength</p>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-warning">Sports</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Body Measurements (FR-24, FR-25) -->
        <section class="progress-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-rulers"></i>
                    <h3 class="mb-0">Body Measurements</h3>
                </div>
                <button class="btn-outline-client">
                    <i class="bi bi-plus-circle"></i> Add Measurement
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Weight</td>
                                <td class="text-end">84.8 kg</td>
                                <td class="text-success text-end">-5.2 kg</td>
                            </tr>
                            <tr>
                                <td>Waist</td>
                                <td class="text-end">86 cm</td>
                                <td class="text-success text-end">-8 cm</td>
                            </tr>
                            <tr>
                                <td>Chest</td>
                                <td class="text-end">102 cm</td>
                                <td class="text-success text-end">-3 cm</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Body Fat %</td>
                                <td class="text-end">18.5%</td>
                                <td class="text-success text-end">-4.2%</td>
                            </tr>
                            <tr>
                                <td>Arms</td>
                                <td class="text-end">34 cm</td>
                                <td class="text-warning text-end">+1 cm</td>
                            </tr>
                            <tr>
                                <td>Thighs</td>
                                <td class="text-end">58 cm</td>
                                <td class="text-success text-end">-3 cm</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- Workout Execution Modal (FR-17, FR-18) -->
    <div class="workout-modal" id="workoutModal">
        <div class="workout-panel">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-activity"></i>
                    <h3 class="mb-0">Workout Session - Push Day</h3>
                </div>
                <button class="btn-close" id="closeWorkout"></button>
            </div>
            
            <div class="timer-display" id="timerDisplay">00:00</div>
            
            <div class="timer-controls">
                <button class="btn-client" id="startTimer">
                    <i class="bi bi-play-fill"></i> Start
                </button>
                <button class="btn-outline-client" id="pauseTimer">
                    <i class="bi bi-pause-fill"></i> Pause
                </button>
                <button class="btn-outline-client" id="resetTimer">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
            </div>
            
            <h5 class="mt-4">Exercise 1: Bench Press</h5>
            <p class="text-muted">3 sets × 8 reps @ 185lbs</p>
            
            <div class="set-log">
                <div class="set-number">1</div>
                <input type="number" class="form-control" placeholder="Weight (lbs)" value="185">
                <input type="number" class="form-control" placeholder="Reps" value="8">
                <button class="btn-client btn-sm">Log Set</button>
            </div>
            
            <div class="set-log">
                <div class="set-number">2</div>
                <input type="number" class="form-control" placeholder="Weight (lbs)" value="185">
                <input type="number" class="form-control" placeholder="Reps">
                <button class="btn-outline-client btn-sm">Log Set</button>
            </div>
            
            <div class="set-log">
                <div class="set-number">3</div>
                <input type="number" class="form-control" placeholder="Weight (lbs)">
                <input type="number" class="form-control" placeholder="Reps">
                <button class="btn-outline-client btn-sm">Log Set</button>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <button class="btn-outline-client">
                    <i class="bi bi-arrow-left"></i> Previous
                </button>
                <button class="btn-client">
                    Complete Exercise <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <nav class="client-nav">
        <a href="#" class="nav-item active">
            <i class="bi bi-house-door nav-icon"></i>
            <span class="nav-label">Dashboard</span>
        </a>
        <a href="#" class="nav-item">
            <i class="bi bi-activity nav-icon"></i>
            <span class="nav-label">Workouts</span>
        </a>
        <a href="#" class="nav-item">
            <i class="bi bi-egg-fried nav-icon"></i>
            <span class="nav-label">Nutrition</span>
        </a>
        <a href="#" class="nav-item">
            <i class="bi bi-graph-up nav-icon"></i>
            <span class="nav-label">Progress</span>
        </a>
        <a href="#" class="nav-item">
            <i class="bi bi-person nav-icon"></i>
            <span class="nav-label">Profile</span>
        </a>
    </nav>

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

        // Workout Execution Modal
        const startWorkoutBtn = document.getElementById('startWorkout');
        const workoutModal = document.getElementById('workoutModal');
        const closeWorkoutBtn = document.getElementById('closeWorkout');
        
        startWorkoutBtn.addEventListener('click', () => {
            workoutModal.classList.add('active');
        });
        
        closeWorkoutBtn.addEventListener('click', () => {
            workoutModal.classList.remove('active');
        });
        
        workoutModal.addEventListener('click', (e) => {
            if (e.target === workoutModal) {
                workoutModal.classList.remove('active');
            }
        });

        // Timer Functionality
        const timerDisplay = document.getElementById('timerDisplay');
        const startTimerBtn = document.getElementById('startTimer');
        const pauseTimerBtn = document.getElementById('pauseTimer');
        const resetTimerBtn = document.getElementById('resetTimer');
        
        let timerInterval;
        let seconds = 0;
        let isRunning = false;
        
        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        
        startTimerBtn.addEventListener('click', () => {
            if (!isRunning) {
                isRunning = true;
                timerInterval = setInterval(() => {
                    seconds++;
                    timerDisplay.textContent = formatTime(seconds);
                }, 1000);
            }
        });
        
        pauseTimerBtn.addEventListener('click', () => {
            isRunning = false;
            clearInterval(timerInterval);
        });
        
        resetTimerBtn.addEventListener('click', () => {
            isRunning = false;
            clearInterval(timerInterval);
            seconds = 0;
            timerDisplay.textContent = formatTime(seconds);
        });

        // Macro Progress Charts
        function createMacroChart(canvasId, value, goal, color) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            const percentage = Math.min((value / goal) * 100, 100);
            
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
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });
        }
        
        createMacroChart('calorieChart', 1850, 2150, '#4A6FA5');
        createMacroChart('proteinChart', 142, 150, '#28a745');
        createMacroChart('carbChart', 210, 250, '#ffc107');
        createMacroChart('fatChart', 58, 65, '#dc3545');

        // Weight Progress Chart
        const weightCtx = document.getElementById('weightChart').getContext('2d');
        new Chart(weightCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8'],
                datasets: [{
                    label: 'Weight (kg)',
                    data: [90.0, 89.2, 88.5, 87.8, 87.0, 86.3, 85.5, 84.8],
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
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        title: { display: true, text: 'Weight (kg)' }
                    },
                    x: {
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                }
            }
        });

        // Completion Chart
        const completionCtx = document.getElementById('completionChart').getContext('2d');
        new Chart(completionCtx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Completion %',
                    data: [100, 100, 85, 90, 95, 0, 0],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(108, 117, 125, 0.7)',
                        'rgba(108, 117, 125, 0.7)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(108, 117, 125, 1)',
                        'rgba(108, 117, 125, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                }
            }
        });

        // Set logging functionality
        document.querySelectorAll('.set-log button').forEach(button => {
            button.addEventListener('click', function() {
                const setLog = this.closest('.set-log');
                const setNumber = setLog.querySelector('.set-number').textContent;
                const weight = setLog.querySelector('input[type="number"]:first-child').value;
                const reps = setLog.querySelector('input[type="number"]:last-child').value;
                
                if (weight && reps) {
                    this.innerHTML = '<i class="bi bi-check"></i> Logged';
                    this.classList.remove('btn-outline-client');
                    this.classList.add('btn-client');
                    this.disabled = true;
                    
                    // Show notification
                    showNotification(`Logged Set ${setNumber}: ${weight}lbs × ${reps} reps`);
                }
            });
        });

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'alert alert-success position-fixed';
            notification.style.cssText = `
                top: 20px;
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

        // Add animation style
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

        // Leaderboard opt-in toggle
        const leaderboardToggle = document.getElementById('leaderboardToggle');
        leaderboardToggle.addEventListener('change', function() {
            if (this.checked) {
                showNotification('You are now visible on the leaderboard!');
            } else {
                showNotification('You are now hidden from the leaderboard.');
            }
        });
    </script>
</body>
</html>