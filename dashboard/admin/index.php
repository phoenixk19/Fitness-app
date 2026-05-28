<?php
// File: dashboard/admin/index.php
require_once 'includes/header.php';

// Get dashboard stats
$totalCoaches = $db->query("SELECT COUNT(*) FROM users WHERE role = 'coach'")->fetch_row()[0] ?? 0;
$totalClients = $db->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetch_row()[0] ?? 0;

// System status (mock data for now)
$serverLoad = '65%';
$storage = '42%';
$apiResponse = '98%';
$uptime = '99.9%';
?>
<div class="page-title"><i class="bi bi-speedometer2"></i><h2>System Dashboard</h2><span class="badge bg-primary ms-2">System Overview</span></div>

<div class="row">
    <div class="col-md-6"><div class="stat-card"><div class="stat-icon"><i class="bi bi-people"></i></div><div class="stat-number"><?php echo $totalCoaches; ?></div><div>Active Coaches</div></div></div>
    <div class="col-md-6"><div class="stat-card"><div class="stat-icon"><i class="bi bi-person-check"></i></div><div class="stat-number"><?php echo $totalClients; ?></div><div>Total Clients</div></div></div>
</div>

<div class="data-table">
    <h5>System Status</h5>
    <div class="row">
        <div class="col-md-3 mb-3"><div class="d-flex justify-content-between"><span>Server Load</span><span><?php echo $serverLoad; ?></span></div><div class="progress"><div class="progress-bar bg-success" style="width:65%"></div></div></div>
        <div class="col-md-3 mb-3"><div class="d-flex justify-content-between"><span>Storage</span><span><?php echo $storage; ?></span></div><div class="progress"><div class="progress-bar bg-info" style="width:42%"></div></div></div>
        <div class="col-md-3 mb-3"><div class="d-flex justify-content-between"><span>API Response</span><span><?php echo $apiResponse; ?></span></div><div class="progress"><div class="progress-bar bg-primary" style="width:98%"></div></div></div>
        <div class="col-md-3 mb-3"><div class="d-flex justify-content-between"><span>Uptime</span><span><?php echo $uptime; ?></span></div><div class="progress"><div class="progress-bar bg-success" style="width:99.9%"></div></div></div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>