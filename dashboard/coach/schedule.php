<?php
// File: dashboard/coach/schedule.php
require_once 'includes/header.php';

$weekStart = isset($_GET['week']) ? $_GET['week'] : date('Y-m-d', strtotime('monday this week'));
$weekDays = [];
for ($i = 0; $i < 7; $i++) { $weekDays[] = date('Y-m-d', strtotime("$weekStart +$i days")); }

$scheduled = $db->query("
    SELECT cw.*, u.name as client_name, wt.name as workout_name
    FROM client_workouts cw
    JOIN users u ON cw.client_id = u.id
    LEFT JOIN workout_templates wt ON cw.template_id = wt.id
    WHERE cw.client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND cw.start_date BETWEEN '$weekDays[0]' AND '$weekDays[6]'
")->fetch_all(MYSQLI_ASSOC) ?? [];

$workoutsByDate = [];
foreach ($scheduled as $w) { $workoutsByDate[$w['start_date']][] = $w; }
?>
<div class="page-title"><i class="bi bi-calendar-check"></i><h2>Schedule</h2><button class="btn btn-coach ms-3" data-bs-toggle="modal" data-bs-target="#scheduleModal"><i class="bi bi-plus-circle"></i> Schedule Workout</button></div>

<div class="d-flex justify-content-between mb-3"><a href="?week=<?php echo date('Y-m-d', strtotime("$weekStart -7 days")); ?>" class="btn btn-sm btn-outline-coach"><i class="bi bi-chevron-left"></i> Prev</a><a href="?week=<?php echo date('Y-m-d', strtotime('monday this week')); ?>" class="btn btn-sm btn-outline-coach">This Week</a><a href="?week=<?php echo date('Y-m-d', strtotime("$weekStart +7 days")); ?>" class="btn btn-sm btn-outline-coach">Next <i class="bi bi-chevron-right"></i></a></div>

<div class="row">
    <?php foreach ($weekDays as $date): $dayName = date('D', strtotime($date)); ?>
    <div class="col-md"><div class="stat-card"><div class="text-center"><strong><?php echo $dayName; ?></strong><br><small><?php echo date('M d', strtotime($date)); ?></small></div><hr>
    <?php if(isset($workoutsByDate[$date])): foreach($workoutsByDate[$date] as $w): ?>
    <div class="small p-1 mb-1 border rounded"><strong><?php echo htmlspecialchars($w['client_name']); ?></strong><br><?php echo htmlspecialchars($w['workout_name'] ?? 'Custom'); ?><span class="badge bg-<?php echo $w['status']=='completed'?'success':'warning'; ?> float-end"><?php echo $w['status']; ?></span></div>
    <?php endforeach; else: ?><div class="text-center text-muted small py-2">No workouts</div><?php endif; ?></div></div>
    <?php endforeach; ?>
</div>

<div class="modal fade" id="scheduleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Schedule Workout</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form><div class="modal-body"><div class="mb-3"><label>Client</label><select class="form-select"><?php foreach ($clients as $c): ?><option><?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select></div><div class="mb-3"><label>Date</label><input type="date" class="form-control"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-coach">Schedule</button></div></form></div></div></div>

<?php require_once 'includes/footer.php'; ?>