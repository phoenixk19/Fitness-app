<?php
// File: dashboard/coach/workouts.php
require_once 'includes/header.php';

$templates = $db->query("SELECT * FROM workout_templates WHERE coach_id = $userId ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC) ?? [];
$exercises = $db->query("SELECT * FROM exercises ORDER BY name")->fetch_all(MYSQLI_ASSOC) ?? [];
?>
<div class="page-title"><i class="bi bi-activity"></i><h2>Workout Plans</h2><button class="btn btn-coach ms-3" data-bs-toggle="modal" data-bs-target="#createTemplateModal"><i class="bi bi-plus-circle"></i> Create Template</button></div>

<div class="data-table">
    <h5>Workout Templates</h5>
    <table class="table"><thead><tr><th>Name</th><th>Type</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody><?php foreach ($templates as $t): ?>
        <tr><td><strong><?php echo htmlspecialchars($t['name']); ?></strong></td><td><?php echo ucfirst($t['training_type']); ?></td><td><?php echo formatDate($t['created_at']); ?></td><td><button class="btn btn-sm btn-outline-coach" onclick="editTemplate(<?php echo $t['id']; ?>)"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-primary" onclick="assignTemplate(<?php echo $t['id']; ?>)"><i class="bi bi-person-plus"></i></button><button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate(<?php echo $t['id']; ?>)"><i class="bi bi-trash"></i></button></td></tr>
    <?php endforeach; ?></tbody>
    </table>
</div>

<div class="modal fade" id="createTemplateModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Create Workout Template</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form><div class="modal-body"><div class="mb-3"><label>Template Name</label><input type="text" class="form-control"></div><div class="mb-3"><label>Training Type</label><select class="form-select"><option>Strength</option><option>Hypertrophy</option><option>Endurance</option><option>Power</option></select></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-coach">Create</button></div></form></div></div></div>

<?php
$page_specific_scripts = '<script>function editTemplate(id){alert("Edit "+id);} function assignTemplate(id){alert("Assign "+id);} function deleteTemplate(id){if(confirm("Delete?")) alert("Delete "+id);}</script>';
require_once 'includes/footer.php';
?>