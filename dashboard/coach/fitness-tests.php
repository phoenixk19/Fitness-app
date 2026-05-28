<?php
// File: dashboard/coach/fitness-tests.php
require_once 'includes/header.php';
?>
<div class="page-title"><i class="bi bi-clipboard-check"></i><h2>Fitness Tests</h2><button class="btn btn-coach ms-3" data-bs-toggle="modal" data-bs-target="#addTestModal"><i class="bi bi-plus-circle"></i> Add Test Result</button></div>

<div class="data-table">
    <h5>Recent Tests</h5>
    <table class="table"><thead><tr><th>Client</th><th>Test Type</th><th>Result</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody><tr><td colspan="5" class="text-center text-muted">No tests recorded yet</td></tr></tbody>
    </table>
</div>

<div class="modal fade" id="addTestModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Add Test Result</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form><div class="modal-body"><div class="mb-3"><label>Client</label><select class="form-select"><?php foreach ($clients as $c): ?><option><?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select></div><div class="mb-3"><label>Test Type</label><select class="form-select"><option>1RM Bench Press</option><option>1RM Squat</option><option>Cooper Test</option><option>Beep Test</option></select></div><div class="row"><div class="col-6"><label>Result</label><input type="number" class="form-control"></div><div class="col-6"><label>Unit</label><input type="text" class="form-control" placeholder="kg, cm, sec"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-coach">Save</button></div></form></div></div></div>

<?php require_once 'includes/footer.php'; ?>