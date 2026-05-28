<?php
// File: dashboard/coach/editors.php
require_once 'includes/header.php';

$editors = $db->query("
    SELECT DISTINCT u.id, u.name, u.email 
    FROM users u
    JOIN editor_assignments ea ON u.id = ea.editor_id
    WHERE ea.coach_id = $userId
")->fetch_all(MYSQLI_ASSOC) ?? [];
?>
<div class="page-title"><i class="bi bi-person-plus"></i><h2>Editor Management</h2><button class="btn btn-coach ms-3" data-bs-toggle="modal" data-bs-target="#addEditorModal"><i class="bi bi-plus-circle"></i> Add Editor</button></div>

<div class="data-table"><table class="table"><thead><tr><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
<tbody><?php foreach ($editors as $e): ?>
<tr><td><?php echo htmlspecialchars($e['name']); ?></td><td><?php echo htmlspecialchars($e['email']); ?></td><td><button class="btn btn-sm btn-outline-danger" onclick="removeEditor(<?php echo $e['id']; ?>)"><i class="bi bi-trash"></i> Remove</button></td></tr>
<?php endforeach; ?></tbody></table></div>

<div class="modal fade" id="addEditorModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Add Editor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form><div class="modal-body"><div class="mb-3"><label>Name</label><input type="text" class="form-control"></div><div class="mb-3"><label>Email</label><input type="email" class="form-control"></div><div class="mb-3"><label>Temporary Password</label><input type="text" class="form-control" value="<?php echo bin2hex(random_bytes(4)); ?>"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-coach">Add Editor</button></div></form></div></div></div>

<?php
$page_specific_scripts = '<script>function removeEditor(id){if(confirm("Remove this editor?")) alert("Remove "+id);}</script>';
require_once 'includes/footer.php';
?>