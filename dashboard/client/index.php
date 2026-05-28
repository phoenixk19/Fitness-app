<?php
// File: dashboard/client/index.php
require_once 'includes/header.php';

// Get today's workout
$todayWorkout = $db->query("
    SELECT cw.*, wt.name as workout_name, wt.training_type
    FROM client_workouts cw
    LEFT JOIN workout_templates wt ON cw.template_id = wt.id
    WHERE cw.client_id = $userId 
    AND cw.start_date <= CURDATE() 
    AND (cw.end_date >= CURDATE() OR cw.end_date IS NULL)
    AND cw.status IN ('scheduled', 'in_progress')
    ORDER BY cw.start_date DESC
    LIMIT 1
")->fetch_assoc();

// Get today's nutrition
$nutritionToday = $db->query("
    SELECT COALESCE(SUM(total_calories),0) as calories,
           COALESCE(SUM(total_protein),0) as protein,
           COALESCE(SUM(total_carbs),0) as carbs,
           COALESCE(SUM(total_fat),0) as fat
    FROM meal_logs WHERE client_id = $userId AND meal_date = CURDATE()
")->fetch_assoc();

// Daily goals
$dailyGoals = ['calories' => 2150, 'protein' => 150, 'carbs' => 250, 'fat' => 65];

// Weight progress for chart
$weightData = $db->query("
    SELECT measurement_date, weight FROM body_measurements 
    WHERE client_id = $userId ORDER BY measurement_date DESC LIMIT 8
")->fetch_all(MYSQLI_ASSOC);
$weightData = array_reverse($weightData);
$weightLabels = array_map(function($w) { return date('M d', strtotime($w['measurement_date'])); }, $weightData);
$weightValues = array_column($weightData, 'weight');

// Weekly completion
$weekCompletion = array_fill(0, 7, 0);
$weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$completionData = $db->query("
    SELECT DAYOFWEEK(start_date) as day, 
           SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) * 100 / COUNT(*) as rate
    FROM client_workouts WHERE client_id = $userId AND start_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DAYOFWEEK(start_date)
");
while($row = $completionData->fetch_assoc()) {
    $idx = ($row['day'] + 5) % 7;
    $weekCompletion[$idx] = round($row['rate']);
}
?>
<div class="page-title"><i class="bi bi-speedometer2"></i><h2>Today's Overview</h2></div>

<div class="today-overview">
    <div class="overview-card">
        <div class="card-header"><div class="card-title"><i class="bi bi-activity"></i> Today's Workout</div></div>
        <?php if ($todayWorkout): ?>
            <h5><?php echo htmlspecialchars($todayWorkout['workout_name'] ?? 'Workout Day'); ?></h5>
            <p class="text-muted"><?php echo ucfirst($todayWorkout['training_type']); ?> training • 45 min</p>
            <button class="start-workout-btn" onclick="alert('Start workout session')"><i class="bi bi-play-circle"></i> Start Workout</button>
        <?php else: ?>
            <div class="text-center py-4"><i class="bi bi-calendar-check fs-1 text-muted"></i><p class="mt-2">Rest Day! Enjoy your break.</p></div>
        <?php endif; ?>
    </div>
    <div class="overview-card">
        <div class="card-header"><div class="card-title"><i class="bi bi-egg-fried"></i> Today's Nutrition</div><span>Goal: <?php echo $dailyGoals['calories']; ?> cal</span></div>
        <div class="nutrition-summary">
            <div class="macro-circle"><div class="macro-value"><?php echo number_format($nutritionToday['calories']); ?></div><div>Calories</div><small><?php echo $dailyGoals['calories'] - $nutritionToday['calories']; ?> left</small></div>
            <div class="macro-circle"><div class="macro-value"><?php echo number_format($nutritionToday['protein']); ?>g</div><div>Protein</div><small><?php echo $dailyGoals['protein'] - $nutritionToday['protein']; ?>g left</small></div>
            <div class="macro-circle"><div class="macro-value"><?php echo number_format($nutritionToday['carbs']); ?>g</div><div>Carbs</div><small><?php echo $dailyGoals['carbs'] - $nutritionToday['carbs']; ?>g left</small></div>
            <div class="macro-circle"><div class="macro-value"><?php echo number_format($nutritionToday['fat']); ?>g</div><div>Fat</div><small><?php echo $dailyGoals['fat'] - $nutritionToday['fat']; ?>g left</small></div>
        </div>
        <button class="btn-client w-100" onclick="window.location.href='nutrition.php'"><i class="bi bi-plus-circle"></i> Log Meal</button>
    </div>
</div>

<div class="progress-section">
    <div class="progress-card"><div class="card-header"><div class="card-title">Weight Progress</div></div><div class="chart-container"><canvas id="weightChart"></canvas></div></div>
    <div class="progress-card"><div class="card-header"><div class="card-title">Workout Completion</div></div><div class="chart-container"><canvas id="completionChart"></canvas></div></div>
</div>

<div class="week-calendar">
    <div class="card-header"><div class="card-title"><i class="bi bi-calendar-week"></i> This Week's Schedule</div></div>
    <div class="calendar-grid">
        <?php for ($i = 0; $i < 7; $i++): $isToday = ($i == date('N')-1); ?>
        <div class="day-cell <?php echo $isToday ? 'today' : ''; ?> <?php echo $weekCompletion[$i] > 0 ? 'workout' : 'rest'; ?>">
            <div class="small"><?php echo $weekDays[$i]; ?></div>
            <?php if($weekCompletion[$i] > 0): ?><div class="small text-success"><?php echo $weekCompletion[$i]; ?>%</div><?php else: ?><div class="small text-muted">Rest</div><?php endif; ?>
        </div>
        <?php endfor; ?>
    </div>
</div>

<?php
$page_specific_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    if(document.getElementById("weightChart")) {
        new Chart(document.getElementById("weightChart"), {
            type: "line", data: { labels: ' . json_encode($weightLabels) . ', datasets: [{ label: "Weight (kg)", data: ' . json_encode($weightValues) . ', borderColor: "#4A6FA5", backgroundColor: "rgba(74,111,165,0.1)", fill: true, tension: 0.4 }] },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    if(document.getElementById("completionChart")) {
        new Chart(document.getElementById("completionChart"), {
            type: "bar", data: { labels: ' . json_encode($weekDays) . ', datasets: [{ data: ' . json_encode($weekCompletion) . ', backgroundColor: "rgba(40,167,69,0.7)" }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { max: 100, ticks: { callback: function(v) { return v + "%"; } } } } }
        });
    }
});
</script>';
require_once 'includes/footer.php';
?>