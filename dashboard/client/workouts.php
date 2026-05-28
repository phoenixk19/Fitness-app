<?php
// File: dashboard/client/workouts.php
require_once 'includes/header.php';

$workouts = $db->query("
    SELECT cw.*, wt.name as workout_name, wt.training_type
    FROM client_workouts cw
    LEFT JOIN workout_templates wt ON cw.template_id = wt.id
    WHERE cw.client_id = $userId
    ORDER BY cw.start_date DESC
    LIMIT 20
")->fetch_all(MYSQLI_ASSOC) ?? [];
?>
<div class="page-title"><i class="bi bi-activity"></i><h2>My Workouts</h2></div>

<div class="data-table">
    <table class="table">
        <thead><tr><th>Date</th><th>Workout</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($workouts as $w): ?>
            <tr>
                <td><?php echo formatDate($w['start_date']); ?></td>
                <td><?php echo htmlspecialchars($w['workout_name'] ?? 'Custom Workout'); ?></td>
                <td><?php echo ucfirst($w['training_type'] ?? 'General'); ?></td>
                <td><span class="badge bg-<?php echo $w['status'] == 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($w['status']); ?></span></td>
                <td><button class="btn btn-sm btn-outline-primary" onclick="alert('View workout details')"><i class="bi bi-eye"></i></button></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($workouts)): ?>
    <p class="text-center text-muted py-4">No workouts assigned yet. Your coach will assign workouts soon!</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>