<?php
// File: dashboard/coach/reports.php
require_once 'includes/header.php';

$type = $_GET['type'] ?? 'clients';
$start = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$end = $_GET['end'] ?? date('Y-m-d');
?>
<div class="page-title"><i class="bi bi-file-text"></i><h2>Reports</h2></div>

<div class="row">
    <div class="col-md-3"><div class="stat-card"><h5>Report Types</h5><div class="list-group"><a href="?type=clients" class="list-group-item list-group-item-action <?php echo $type=='clients'?'active':''; ?>">Client Summary</a><a href="?type=workouts" class="list-group-item list-group-item-action <?php echo $type=='workouts'?'active':''; ?>">Workout Report</a><a href="?type=payments" class="list-group-item list-group-item-action <?php echo $type=='payments'?'active':''; ?>">Payment Report</a></div></div>
    <div class="stat-card mt-3"><h5>Date Range</h5><form method="GET"><input type="hidden" name="type" value="<?php echo $type; ?>"><div class="mb-2"><label>From</label><input type="date" name="start" class="form-control" value="<?php echo $start; ?>"></div><div class="mb-2"><label>To</label><input type="date" name="end" class="form-control" value="<?php echo $end; ?>"></div><button type="submit" class="btn btn-coach w-100">Apply</button><button type="button" class="btn btn-outline-coach w-100 mt-2" onclick="alert('Export PDF')"><i class="bi bi-file-pdf"></i> Export PDF</button></form></div></div>
    <div class="col-md-9"><div class="data-table"><h5><?php echo ucfirst($type); ?> Report (<?php echo formatDate($start); ?> - <?php echo formatDate($end); ?>)</h5>
    <?php if($type=='clients'): ?>
    <table class="table"><thead><tr><th>Client</th><th>Workouts</th><th>Completion</th><th>Meals</th></tr></thead><tbody><?php foreach ($clients as $c): $completed = $db->query("SELECT COUNT(*) FROM client_workouts WHERE client_id={$c['id']} AND status='completed' AND start_date BETWEEN '$start' AND '$end'")->fetch_row()[0]; $total = $db->query("SELECT COUNT(*) FROM client_workouts WHERE client_id={$c['id']} AND start_date BETWEEN '$start' AND '$end'")->fetch_row()[0]; $rate = $total>0?round(($completed/$total)*100):0; ?><tr><td><?php echo htmlspecialchars($c['name']); ?></td><td><?php echo $completed; ?>/<?php echo $total; ?></td><td><div class="progress" style="height:20px"><div class="progress-bar bg-success" style="width:<?php echo $rate; ?>%"><?php echo $rate; ?>%</div></div></td><td><?php echo $db->query("SELECT COUNT(*) FROM meal_logs WHERE client_id={$c['id']} AND meal_date BETWEEN '$start' AND '$end'")->fetch_row()[0]; ?></td></tr><?php endforeach; ?></tbody></table>
    <?php elseif($type=='workouts'): ?>
    <table class="table"><thead></td><th>Date</th><th>Client</th><th>Workout</th><th>Status</th></tr></thead><tbody><?php $rows = $db->query("SELECT cw.*, u.name as client_name FROM client_workouts cw JOIN users u ON cw.client_id=u.id WHERE cw.client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id=$userId) AND cw.start_date BETWEEN '$start' AND '$end' ORDER BY cw.start_date DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC) ?? []; foreach($rows as $r): ?><tr><td><?php echo formatDate($r['start_date']); ?></td><td><?php echo htmlspecialchars($r['client_name']); ?></td><td><?php echo htmlspecialchars($r['workout_name']??'Custom'); ?></td><td><span class="badge bg-<?php echo $r['status']=='completed'?'success':'warning'; ?>"><?php echo $r['status']; ?></span></td></tr><?php endforeach; ?></tbody></table>
    <?php else: ?>
    <table class="table"><thead><tr><th>Date</th><th>Client</th><th>Amount</th><th>Status</th></table></thead><tbody><?php $rows = $db->query("SELECT p.*, u.name as client_name FROM payments p JOIN users u ON p.client_id=u.id WHERE p.client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id=$userId) AND p.payment_date BETWEEN '$start' AND '$end' ORDER BY p.payment_date DESC")->fetch_all(MYSQLI_ASSOC) ?? []; foreach($rows as $r): ?><tr><td><?php echo formatDate($r['payment_date']); ?></td><td><?php echo htmlspecialchars($r['client_name']); ?></td><td>$<?php echo number_format($r['amount'],2); ?></td><td><span class="badge bg-<?php echo $r['status']=='paid'?'success':'warning'; ?>"><?php echo ucfirst($r['status']); ?></span></td></tr><?php endforeach; ?></tbody></table>
    <?php endif; ?></div></div>
</div>

<?php require_once 'includes/footer.php'; ?>