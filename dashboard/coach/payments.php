<?php
// File: dashboard/coach/payments.php
require_once 'includes/header.php';

$payments = $db->query("
    SELECT p.*, u.name as client_name 
    FROM payments p JOIN users u ON p.client_id = u.id
    WHERE p.client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    ORDER BY p.payment_date DESC
")->fetch_all(MYSQLI_ASSOC) ?? [];

$totalPaid = array_sum(array_filter(array_column($payments, 'amount'), function($k,$v) use ($payments) { return $payments[$v]['status'] == 'paid'; }, ARRAY_FILTER_USE_BOTH));
$totalPending = array_sum(array_filter(array_column($payments, 'amount'), function($k,$v) use ($payments) { return $payments[$v]['status'] == 'pending'; }, ARRAY_FILTER_USE_BOTH));
?>
<div class="page-title"><i class="bi bi-cash"></i><h2>Payments</h2><button class="btn btn-coach ms-3" data-bs-toggle="modal" data-bs-target="#recordPaymentModal"><i class="bi bi-plus-circle"></i> Record Payment</button></div>

<div class="row mb-4"><div class="col-md-4"><div class="stat-box text-center"><div class="stat-value">$<?php echo number_format($totalPaid); ?></div><div>Total Collected</div></div></div><div class="col-md-4"><div class="stat-box text-center"><div class="stat-value">$<?php echo number_format($totalPending); ?></div><div>Pending</div></div></div><div class="col-md-4"><div class="stat-box text-center"><div class="stat-value"><?php echo count($payments); ?></div><div>Transactions</div></div></div></div>

<div class="data-table"><table class="table"><thead><tr><th>Client</th><th>Amount</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
<tbody><?php foreach ($payments as $p): ?>
<tr><td><?php echo htmlspecialchars($p['client_name']); ?></td><td>$<?php echo number_format($p['amount'],2); ?></td><td><?php echo formatDate($p['payment_date']); ?></td><td><span class="badge bg-<?php echo $p['status']=='paid'?'success':'warning'; ?>"><?php echo ucfirst($p['status']); ?></span></td><td><button class="btn btn-sm btn-outline-coach" onclick="markPaid(<?php echo $p['id']; ?>)"><i class="bi bi-check-circle"></i></button></td></tr>
<?php endforeach; ?></tbody></table></div>

<div class="modal fade" id="recordPaymentModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Record Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form><div class="modal-body"><div class="mb-3"><label>Client</label><select class="form-select"><?php foreach ($clients as $c): ?><option><?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select></div><div class="mb-3"><label>Amount</label><input type="number" class="form-control"></div><div class="mb-3"><label>Date</label><input type="date" class="form-control"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-coach">Record</button></div></form></div></div></div>

<?php
$page_specific_scripts = '<script>function markPaid(id){if(confirm("Mark as paid?")) alert("Paid "+id);}</script>';
require_once 'includes/footer.php';
?>