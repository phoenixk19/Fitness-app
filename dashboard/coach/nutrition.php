<?php
// File: dashboard/coach/nutrition.php
require_once 'includes/header.php';
?>
<div class="page-title"><i class="bi bi-egg-fried"></i><h2>Nutrition Management</h2></div>

<div class="row">
    <div class="col-md-6"><div class="nutrition-card"><h5>Client Nutrition Overview</h5><select class="form-select mb-3"><option>Select Client</option><?php foreach ($clients as $c): ?><option><?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select><div id="nutritionData"><p class="text-muted">Select a client to view nutrition data</p></div></div></div>
    <div class="col-md-6"><div class="nutrition-card"><h5>Set Daily Targets</h5><div class="row"><div class="col-6"><label>Calories</label><input type="number" class="form-control" value="2150"></div><div class="col-6"><label>Protein (g)</label><input type="number" class="form-control" value="150"></div><div class="col-6 mt-2"><label>Carbs (g)</label><input type="number" class="form-control" value="250"></div><div class="col-6 mt-2"><label>Fat (g)</label><input type="number" class="form-control" value="65"></div></div><button class="btn btn-coach mt-3 w-100" onclick="alert('Targets updated')">Save Targets</button></div></div>
</div>

<?php require_once 'includes/footer.php'; ?>