<?php
// File: dashboard/editor/nutrition.php
require_once 'includes/header.php';

$selectedClient = isset($_GET['client']) ? (int)$_GET['client'] : ($assignedClients[0]['id'] ?? 0);
$selectedClientName = '';
foreach ($assignedClients as $c) { if($c['id'] == $selectedClient) { $selectedClientName = $c['name']; break; } }
?>
<div class="page-title"><i class="bi bi-egg-fried"></i><h2>Track Client Nutrition</h2></div>

<div class="client-selector">
    <span><i class="bi bi-person-badge"></i> <strong>Select Client:</strong></span>
    <select id="clientSelect" class="form-select" style="width:250px" onchange="window.location.href='?client='+this.value">
        <?php foreach ($assignedClients as $c): ?>
        <option value="<?php echo $c['id']; ?>" <?php echo $selectedClient == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="meal-log-form">
    <div class="mb-3"><label class="form-label">Meal Type</label><select class="form-select"><option>Breakfast</option><option>Lunch</option><option>Dinner</option><option>Snack</option></select></div>
    <div class="food-item"><div class="food-details"><strong>Grilled Chicken Breast</strong><div>Protein: 31g • Carbs: 0g • Fat: 3.6g</div></div><button class="btn btn-sm btn-outline-danger" onclick="alert('Remove')"><i class="bi bi-trash"></i></button></div>
    <div class="food-item"><div class="food-details"><strong>Brown Rice</strong><div>Protein: 5g • Carbs: 45g • Fat: 2g</div></div><button class="btn btn-sm btn-outline-danger" onclick="alert('Remove')"><i class="bi bi-trash"></i></button></div>
    <button class="btn btn-outline-editor w-100 mb-3" onclick="alert('Add food')"><i class="bi bi-plus-circle"></i> Add Food Item</button>
    <div class="row mb-3"><div class="col-3"><label>Calories</label><input type="text" class="form-control" value="485" readonly></div><div class="col-3"><label>Protein</label><input type="text" class="form-control" value="36" readonly></div><div class="col-3"><label>Carbs</label><input type="text" class="form-control" value="45" readonly></div><div class="col-3"><label>Fat</label><input type="text" class="form-control" value="5.6" readonly></div></div>
    <button class="btn btn-editor w-100" onclick="alert('Meal saved!')"><i class="bi bi-save"></i> Save Meal Log</button>
</div>

<?php require_once 'includes/footer.php'; ?>