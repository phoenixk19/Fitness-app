<?php
// File: dashboard/client/progress.php
require_once 'includes/header.php';

// Get weight history
$weightData = $db->query("
    SELECT measurement_date, weight 
    FROM body_measurements 
    WHERE client_id = $userId 
    ORDER BY measurement_date ASC 
    LIMIT 12
")->fetch_all(MYSQLI_ASSOC) ?? [];

$weightLabels = [];
$weightValues = [];
foreach ($weightData as $w) {
    $weightLabels[] = date('M d', strtotime($w['measurement_date']));
    $weightValues[] = (float)$w['weight'];
}

// Get latest measurements
$measurements = $db->query("
    SELECT * FROM body_measurements 
    WHERE client_id = $userId 
    ORDER BY measurement_date DESC 
    LIMIT 1
")->fetch_assoc();
?>
<div class="page-title">
    <i class="bi bi-graph-up"></i>
    <h2 class="mb-0">My Progress</h2>
    <button class="btn-client ms-3" data-bs-toggle="modal" data-bs-target="#addMeasurementModal">
        <i class="bi bi-plus-circle"></i> Add Measurement
    </button>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="stat-card">
            <h5>Weight History</h5>
            <?php if (!empty($weightValues)): ?>
            <div style="position: relative; height: 300px;">
                <canvas id="weightChart" style="width: 100%; height: 100%;"></canvas>
            </div>
            <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-bar-chart fs-1"></i>
                <p class="mt-2">No weight data yet. Add your first measurement!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h5>Latest Measurements</h5>
            <?php if ($measurements): ?>
            <table class="table table-sm">
                <tr><th>Date</th><td><?php echo formatDate($measurements['measurement_date']); ?></td></tr>
                <tr><th>Weight</th><td><?php echo $measurements['weight'] ?? '--'; ?> kg</td></tr>
                <tr><th>Body Fat</th><td><?php echo $measurements['body_fat_us_navy'] ?? '--'; ?>%</td></tr>
                <tr><th>Waist</th><td><?php echo $measurements['waist'] ?? '--'; ?> cm</td></tr>
                <tr><th>Chest</th><td><?php echo $measurements['chest'] ?? '--'; ?> cm</td></tr>
                <tr><th>Arms</th><td><?php echo $measurements['arms'] ?? '--'; ?> cm</td></tr>
                <tr><th>Thighs</th><td><?php echo $measurements['thighs'] ?? '--'; ?> cm</td></tr>
            </table>
            <?php else: ?>
            <p class="text-muted text-center">No measurements yet</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Measurement Modal -->
<div class="modal fade" id="addMeasurementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Body Measurement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../api/measurements.php">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" name="weight" class="form-control" step="0.1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Body Fat %</label>
                            <input type="number" name="body_fat" class="form-control" step="0.1">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-4">
                            <label class="form-label">Waist (cm)</label>
                            <input type="number" name="waist" class="form-control">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Chest (cm)</label>
                            <input type="number" name="chest" class="form-control">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Arms (cm)</label>
                            <input type="number" name="arms" class="form-control">
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label">Measurement Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-client">Save Measurement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (!empty($weightValues)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var canvas = document.getElementById('weightChart');
    if (canvas) {
        // Destroy existing chart if any
        if (canvas.chart) {
            canvas.chart.destroy();
        }
        
        var ctx = canvas.getContext('2d');
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
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: true, position: 'top' }
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