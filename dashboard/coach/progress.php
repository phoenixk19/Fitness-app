<?php
// File: dashboard/coach/progress.php
require_once 'includes/header.php';

$clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;

// Get clients for dropdown
$clientsList = $db->query("
    SELECT u.id, u.name 
    FROM users u
    JOIN coaches_clients cc ON u.id = cc.client_id
    WHERE cc.coach_id = $userId AND u.status = 'active'
    ORDER BY u.name
")->fetch_all(MYSQLI_ASSOC) ?? [];

// Get weight data for selected client
$weightLabels = [];
$weightValues = [];
$measurements = [];

if ($clientId > 0) {
    // Weight progress data
    $weightResult = $db->query("
        SELECT measurement_date, weight 
        FROM body_measurements 
        WHERE client_id = $clientId 
        ORDER BY measurement_date ASC 
        LIMIT 12
    ");
    if ($weightResult) {
        while ($row = $weightResult->fetch_assoc()) {
            $weightLabels[] = date('M d', strtotime($row['measurement_date']));
            $weightValues[] = (float)$row['weight'];
        }
    }
    
    // Latest measurements
    $measResult = $db->query("
        SELECT * FROM body_measurements 
        WHERE client_id = $clientId 
        ORDER BY measurement_date DESC 
        LIMIT 1
    ");
    $measurements = $measResult ? $measResult->fetch_assoc() : [];
}
?>
<div class="page-title">
    <i class="bi bi-graph-up"></i>
    <h2 class="mb-0">Client Progress Tracking</h2>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="stat-card">
            <h5>Select Client</h5>
            <select id="clientSelect" class="form-select" onchange="window.location.href='?client_id='+this.value">
                <option value="">-- Select Client --</option>
                <?php foreach ($clientsList as $c): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo $clientId == $c['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($clientId > 0 && $measurements): ?>
        <div class="stat-card mt-3">
            <h5>Latest Measurements</h5>
            <table class="table table-sm">
                <tr><th>Weight</th><td><?php echo $measurements['weight'] ?? '--'; ?> kg</td></tr>
                <tr><th>Body Fat</th><td><?php echo $measurements['body_fat_us_navy'] ?? '--'; ?>%</td></tr>
                <tr><th>Waist</th><td><?php echo $measurements['waist'] ?? '--'; ?> cm</td></tr>
                <tr><th>Chest</th><td><?php echo $measurements['chest'] ?? '--'; ?> cm</td></tr>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-md-8">
        <div class="stat-card">
            <h5>Weight Progress</h5>
            <?php if ($clientId > 0 && !empty($weightValues)): ?>
            <canvas id="weightChart" style="width:100%; height:300px;"></canvas>
            <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-bar-chart fs-1"></i>
                <p class="mt-2"><?php echo $clientId ? 'No weight data for this client yet' : 'Select a client to view progress'; ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($clientId > 0 && !empty($weightValues)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('weightChart');
    if (canvas) {
        // Destroy existing chart if any
        if (canvas.chart) {
            canvas.chart.destroy();
        }
        
        const ctx = canvas.getContext('2d');
        canvas.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($weightLabels); ?>,
                datasets: [{
                    label: 'Weight (kg)',
                    data: <?php echo json_encode($weightValues); ?>,
                    borderColor: '#4A6FA5',
                    backgroundColor: 'rgba(74,111,165,0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#4A6FA5',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: { callbacks: { label: function(ctx) { return ctx.raw + ' kg'; } } }
                },
                scales: {
                    y: { title: { display: true, text: 'Weight (kg)' }, beginAtZero: false }
                }
            }
        });
    }
});
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>