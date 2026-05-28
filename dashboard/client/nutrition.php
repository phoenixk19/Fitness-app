<?php
// File: dashboard/client/nutrition.php
require_once 'includes/header.php';

$today = date('Y-m-d');
$meals = $db->query("SELECT * FROM meal_logs WHERE client_id = $userId AND meal_date = '$today' ORDER BY FIELD(meal_type, 'breakfast', 'lunch', 'dinner', 'snack')")->fetch_all(MYSQLI_ASSOC) ?? [];
$totals = $db->query("SELECT SUM(total_calories) as cal, SUM(total_protein) as pro, SUM(total_carbs) as carb, SUM(total_fat) as fat FROM meal_logs WHERE client_id = $userId AND meal_date = '$today'")->fetch_assoc();
?>
<div class="page-title"><i class="bi bi-egg-fried"></i><h2>My Nutrition</h2><button class="btn-client ms-3" data-bs-toggle="modal" data-bs-target="#logMealModal"><i class="bi bi-plus-circle"></i> Log Meal</button></div>

<div class="row">
    <div class="col-md-8">
        <div class="data-table">
            <h5>Today's Meals (<?php echo date('F j, Y'); ?>)</h5>
            <table class="table">
                <thead><tr><th>Meal</th><th>Calories</th><th>Protein</th><th>Carbs</th><th>Fat</th></tr></thead>
                <tbody>
                    <?php foreach ($meals as $m): ?>
                    <tr>
                        <td><strong><?php echo ucfirst($m['meal_type']); ?></strong></td>
                        <td><?php echo $m['total_calories']; ?></td>
                        <td><?php echo $m['total_protein']; ?>g</td>
                        <td><?php echo $m['total_carbs']; ?>g</td>
                        <td><?php echo $m['total_fat']; ?>g</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-active"><td><strong>Total</strong></td><td><strong><?php echo $totals['cal'] ?? 0; ?></strong></td><td><strong><?php echo $totals['pro'] ?? 0; ?>g</strong></td><td><strong><?php echo $totals['carb'] ?? 0; ?>g</strong></td><td><strong><?php echo $totals['fat'] ?? 0; ?>g</strong></td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card"><h5>Daily Goals</h5><div class="mb-2"><div class="d-flex justify-content-between"><span>Calories</span><span><?php echo $totals['cal'] ?? 0; ?> / 2150</span></div><div class="progress"><div class="progress-bar bg-primary" style="width: <?php echo min(100, round(($totals['cal'] ?? 0) / 21.5)); ?>%"></div></div></div>
        <div class="mb-2"><div class="d-flex justify-content-between"><span>Protein</span><span><?php echo $totals['pro'] ?? 0; ?>g / 150g</span></div><div class="progress"><div class="progress-bar bg-success" style="width: <?php echo min(100, round(($totals['pro'] ?? 0) / 1.5)); ?>%"></div></div></div>
        <div class="mb-2"><div class="d-flex justify-content-between"><span>Carbs</span><span><?php echo $totals['carb'] ?? 0; ?>g / 250g</span></div><div class="progress"><div class="progress-bar bg-warning" style="width: <?php echo min(100, round(($totals['carb'] ?? 0) / 2.5)); ?>%"></div></div></div>
        <div><div class="d-flex justify-content-between"><span>Fat</span><span><?php echo $totals['fat'] ?? 0; ?>g / 65g</span></div><div class="progress"><div class="progress-bar bg-danger" style="width: <?php echo min(100, round(($totals['fat'] ?? 0) / 0.65)); ?>%"></div></div></div></div>
    </div>
</div>

<div class="modal fade" id="logMealModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Log Meal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form><div class="modal-body"><div class="mb-3"><label>Meal Type</label><select class="form-select"><option>Breakfast</option><option>Lunch</option><option>Dinner</option><option>Snack</option></select></div><div class="mb-3"><label>Food Item</label><input type="text" class="form-control" placeholder="Grilled Chicken Breast"></div><div class="row"><div class="col-6"><label>Quantity (g)</label><input type="number" class="form-control"></div><div class="col-6"><label>Calories</label><input type="number" class="form-control"></div></div><div class="row mt-2"><div class="col-4"><label>Protein (g)</label><input type="number" class="form-control"></div><div class="col-4"><label>Carbs (g)</label><input type="number" class="form-control"></div><div class="col-4"><label>Fat (g)</label><input type="number" class="form-control"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-client">Save Meal</button></div></form></div></div></div>

<?php require_once 'includes/footer.php'; ?>