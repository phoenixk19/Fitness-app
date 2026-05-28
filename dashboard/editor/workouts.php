<?php
// File: dashboard/editor/workouts.php
require_once 'includes/header.php';

$selectedClient = isset($_GET['client']) ? (int)$_GET['client'] : ($assignedClients[0]['id'] ?? 0);
$selectedClientName = '';
foreach ($assignedClients as $c) { if($c['id'] == $selectedClient) { $selectedClientName = $c['name']; break; } }

// Get today's workout for selected client
$todayWorkout = $db->query("
    SELECT cw.*, wt.name as workout_name
    FROM client_workouts cw
    LEFT JOIN workout_templates wt ON cw.template_id = wt.id
    WHERE cw.client_id = $selectedClient AND cw.start_date <= CURDATE() 
    AND (cw.end_date >= CURDATE() OR cw.end_date IS NULL)
    AND cw.status IN ('scheduled', 'in_progress')
    LIMIT 1
")->fetch_assoc();
?>
<div class="page-title"><i class="bi bi-activity"></i><h2>Log Client Workouts</h2></div>

<div class="client-selector">
    <span><i class="bi bi-person-badge"></i> <strong>Select Client:</strong></span>
    <select id="clientSelect" class="form-select" style="width:250px" onchange="window.location.href='?client='+this.value">
        <?php foreach ($assignedClients as $c): ?>
        <option value="<?php echo $c['id']; ?>" <?php echo $selectedClient == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
        <?php endforeach; ?>
    </select>
</div>

<?php if ($todayWorkout): ?>
<div class="workout-card">
    <div class="client-info">
        <div class="client-avatar"><?php echo strtoupper(substr($selectedClientName, 0, 2)); ?></div>
        <div><h5><?php echo htmlspecialchars($selectedClientName); ?></h5><p class="mb-0 text-muted">Workout: <?php echo htmlspecialchars($todayWorkout['workout_name'] ?? 'Custom'); ?> • Status: <span class="badge bg-warning"><?php echo ucfirst($todayWorkout['status']); ?></span></p></div>
    </div>
    <div class="exercise-item"><div><strong>Bench Press</strong><p class="mb-0 small">3 sets × 8 reps @ 185lbs</p></div><div class="exercise-inputs"><div class="input-group-small"><label>Weight</label><input type="number" value="185"></div><div class="input-group-small"><label>Reps</label><input type="number" value="8"></div><button class="btn btn-editor btn-sm" onclick="alert('Set logged')">Log Set</button></div></div>
    <div class="exercise-item"><div><strong>Incline Dumbbell Press</strong><p class="mb-0 small">3 sets × 10 reps @ 65lbs</p></div><div class="exercise-inputs"><div class="input-group-small"><label>Weight</label><input type="number" value="65"></div><div class="input-group-small"><label>Reps</label><input type="number" value="10"></div><button class="btn btn-editor btn-sm" onclick="alert('Set logged')">Log Set</button></div></div>
    <button class="btn btn-editor w-100 mt-3" onclick="alert('Workout completed!')"><i class="bi bi-check-circle"></i> Complete Workout</button>
</div>
<?php else: ?>
<div class="workout-card text-center"><i class="bi bi-check-circle fs-1 text-success"></i><h5>All caught up!</h5><p>No pending workouts for today.</p></div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>