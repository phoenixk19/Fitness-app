<?php
// File: dashboard/coach/index.php
require_once 'includes/header.php';

// Get clients data
$clients = [];
$clientsResult = $db->query("
    SELECT u.id, u.name, u.email, 
           (SELECT weight FROM body_measurements WHERE client_id = u.id ORDER BY measurement_date DESC LIMIT 1) as current_weight,
           (SELECT COUNT(*) FROM client_workouts WHERE client_id = u.id AND status = 'completed') as workouts_completed,
           (SELECT COUNT(*) FROM meal_logs WHERE client_id = u.id AND meal_date = CURDATE()) as meals_today
    FROM users u
    JOIN coaches_clients cc ON u.id = cc.client_id
    WHERE cc.coach_id = $userId AND u.status = 'active'
    LIMIT 6
");
if ($clientsResult) { $clients = $clientsResult->fetch_all(MYSQLI_ASSOC); }

$totalClients = count($clients);

$completionRate = $db->query("
    SELECT ROUND(AVG(CASE WHEN status = 'completed' THEN 100 ELSE 0 END), 1) as rate
    FROM client_workouts
    WHERE client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND start_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetch_row()[0] ?? 0;

$pendingWorkouts = $db->query("
    SELECT COUNT(*) FROM client_workouts
    WHERE client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND status = 'scheduled' AND start_date <= CURDATE()
")->fetch_row()[0] ?? 0;

$pendingPayments = $db->query("
    SELECT COALESCE(SUM(amount), 0) FROM payments
    WHERE client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND status = 'pending'
")->fetch_row()[0] ?? 0;

// Weekly completion chart
$weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$weekCompletion = array_fill(0, 7, 0);
$weeklyResult = $db->query("
    SELECT DAYOFWEEK(start_date) as day, 
           ROUND(AVG(CASE WHEN status = 'completed' THEN 100 ELSE 0 END), 1) as rate
    FROM client_workouts
    WHERE client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND start_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DAYOFWEEK(start_date)
");
if ($weeklyResult) {
    while($row = $weeklyResult->fetch_assoc()) {
        $idx = ($row['day'] + 5) % 7;
        $weekCompletion[$idx] = $row['rate'];
    }
}
?>
<div class="quick-stats">
    <div class="stat-box"><div class="stat-icon" style="background:rgba(74,111,165,0.1)"><i class="bi bi-people"></i></div><div class="stat-value"><?php echo $totalClients; ?></div><div class="stat-label">Active Clients</div></div>
    <div class="stat-box"><div class="stat-icon" style="background:rgba(40,167,69,0.1)"><i class="bi bi-check-circle"></i></div><div class="stat-value"><?php echo $completionRate; ?>%</div><div class="stat-label">Workout Completion</div></div>
    <div class="stat-box"><div class="stat-icon" style="background:rgba(255,193,7,0.1)"><i class="bi bi-clock-history"></i></div><div class="stat-value"><?php echo $pendingWorkouts; ?></div><div class="stat-label">Pending Workouts</div></div>
    <div class="stat-box"><div class="stat-icon" style="background:rgba(220,53,69,0.1)"><i class="bi bi-currency-dollar"></i></div><div class="stat-value">$<?php echo number_format($pendingPayments); ?></div><div class="stat-label">Pending Payments</div></div>
</div>

<div class="dashboard-section">
    <div class="section-header"><div class="section-title"><i class="bi bi-people"></i><h3>Recent Clients</h3></div><a href="clients.php" class="btn btn-sm btn-outline-coach">View All</a></div>
    <div class="clients-grid">
        <?php foreach ($clients as $client): ?>
        <div class="client-card">
            <div class="client-header"><div class="client-avatar"><?php echo strtoupper(substr($client['name'],0,2)); ?></div><div><h5><?php echo htmlspecialchars($client['name']); ?></h5><div><?php echo $client['current_weight'] ? $client['current_weight'].' kg' : 'No data'; ?></div></div></div>
            <div class="client-metrics"><div class="metric-item"><span class="metric-value"><?php echo $client['workouts_completed']; ?></span><span>Workouts</span></div><div class="metric-item"><span class="metric-value"><?php echo $client['meals_today']; ?></span><span>Meals Today</span></div></div>
            <div class="d-flex gap-2"><button class="btn btn-sm btn-outline-coach" onclick="alert('View workout')"><i class="bi bi-activity"></i></button><button class="btn btn-sm btn-coach" onclick="alert('View progress')"><i class="bi bi-graph-up"></i></button></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="dashboard-section">
    <div class="section-header">
        <div class="section-title"><i class="bi bi-person-plus"></i><h3>New Applications</h3></div>
        <a href="leads.php" class="btn btn-sm btn-outline-coach">View All</a>
    </div>
    <div class="clients-grid">
        <?php
        $recentLeads = $db->query("SELECT * FROM leads WHERE status = 'new' ORDER BY created_at DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC) ?? [];
        foreach ($recentLeads as $lead): ?>
        <div class="client-card">
            <div class="client-header">
                <div class="client-avatar"><?php echo strtoupper(substr($lead['first_name'], 0, 1) . substr($lead['last_name'], 0, 1)); ?></div>
                <div><h5><?php echo htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']); ?></h5><div class="client-goal"><?php echo htmlspecialchars($lead['fitness_goal'] ?? 'No goal specified'); ?></div></div>
            </div>
            <div class="client-metrics">
                <div class="metric-item"><span class="metric-value"><?php echo htmlspecialchars($lead['email']); ?></span><span class="metric-label">Email</span></div>
                <div class="metric-item"><span class="metric-value"><?php echo htmlspecialchars($lead['phone']); ?></span><span class="metric-label">Phone</span></div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-sm btn-coach" onclick="window.location.href='leads.php'"><i class="bi bi-person-check"></i> Review</button>
                <button class="btn btn-sm btn-outline-coach" onclick="window.location.href='mailto:<?php echo $lead['email']; ?>'"><i class="bi bi-envelope"></i> Contact</button>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($recentLeads)): ?>
        <div class="client-card text-center"><p class="text-muted mb-0">No new applications</p></div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-8"><div class="chart-container"><h5>Weekly Workout Completion</h5><canvas id="progressChart" style="height:250px"></canvas></div></div>
    <div class="col-lg-4"><div class="chart-container"><h5>Quick Actions</h5><button class="btn btn-coach w-100 mb-2" data-bs-toggle="modal" data-bs-target="#newClientModal"><i class="bi bi-person-plus"></i> Add New Client</button><button class="btn btn-outline-coach w-100 mb-2" onclick="alert('Create workout')"><i class="bi bi-activity"></i> Create Workout</button><button class="btn btn-outline-coach w-100" onclick="alert('Record payment')"><i class="bi bi-cash"></i> Record Payment</button></div></div>
</div>

<div class="modal fade" id="newClientModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Add New Client</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form><div class="modal-body"><div class="mb-3"><label>Name</label><input type="text" class="form-control"></div><div class="mb-3"><label>Email</label><input type="email" class="form-control"></div><div class="mb-3"><label>Phone</label><input type="tel" class="form-control"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-coach" onclick="alert('Client added')">Add Client</button></div></form></div></div></div>

<?php
$page_specific_scripts = '
<script>new Chart(document.getElementById("progressChart"),{type:"line",data:{labels:["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],datasets:[{label:"Completion %",data:'.json_encode($weekCompletion).',borderColor:"#4A6FA5",backgroundColor:"rgba(74,111,165,0.1)",fill:true}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{max:100,ticks:{callback:v=>v+"%"}}}}})</script>';
require_once 'includes/footer.php';
?>