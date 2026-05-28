<?php
// File: dashboard/editor/index.php
require_once 'includes/header.php';

// Get stats
$workoutsLogged = $db->query("
    SELECT COUNT(*) FROM client_workout_exercises cwe
    JOIN client_workouts cw ON cwe.client_workout_id = cw.id
    WHERE cw.client_id IN (SELECT client_id FROM editor_assignments WHERE editor_id = $userId)
    AND cwe.logged_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetch_row()[0] ?? 0;

$mealsLogged = $db->query("
    SELECT COUNT(*) FROM meal_logs
    WHERE client_id IN (SELECT client_id FROM editor_assignments WHERE editor_id = $userId)
    AND logged_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetch_row()[0] ?? 0;

$commentsAdded = $db->query("
    SELECT COUNT(*) FROM activity_logs
    WHERE user_id = $userId AND action = 'add_comment'
")->fetch_row()[0] ?? 0;

// Get recent comments
$recentComments = $db->query("
    SELECT al.*, u.name as client_name
    FROM activity_logs al
    JOIN users u ON al.user_id = u.id
    WHERE al.user_id IN (SELECT client_id FROM editor_assignments WHERE editor_id = $userId)
    AND al.action = 'add_comment'
    ORDER BY al.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC) ?? [];
?>
<div class="page-title"><i class="bi bi-speedometer2"></i><h2>Overview</h2></div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-people"></i></div><div class="stat-number"><?php echo $totalClients; ?></div><div>Assigned Clients</div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-check-circle"></i></div><div class="stat-number"><?php echo $workoutsLogged; ?></div><div>Workouts Logged (30d)</div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-egg-fried"></i></div><div class="stat-number"><?php echo $mealsLogged; ?></div><div>Meals Tracked (30d)</div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-chat-dots"></i></div><div class="stat-number"><?php echo $commentsAdded; ?></div><div>Comments Added</div></div>
</div>

<div class="client-selector">
    <span><i class="bi bi-person-badge"></i> <strong>Quick Actions:</strong></span>
    <a href="workouts.php" class="btn btn-sm btn-editor"><i class="bi bi-activity"></i> Log Workout</a>
    <a href="nutrition.php" class="btn btn-sm btn-outline-editor"><i class="bi bi-egg-fried"></i> Log Meal</a>
    <a href="comments.php" class="btn btn-sm btn-outline-editor"><i class="bi bi-chat"></i> Add Comment</a>
</div>

<div class="data-table">
    <h5>Recent Comments</h5>
    <?php foreach ($recentComments as $c): ?>
    <div class="comment-box"><div class="comment-meta"><span><strong><?php echo htmlspecialchars($c['client_name']); ?></strong> • <?php echo formatDate($c['created_at']); ?></span></div><p><?php echo htmlspecialchars($c['details'] ?? 'No comment details'); ?></p></div>
    <?php endforeach; ?>
    <?php if (empty($recentComments)): ?><p class="text-center text-muted">No recent comments</p><?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>