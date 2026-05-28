<?php
// File: dashboard/editor/photos.php
require_once 'includes/header.php';

$selectedClient = isset($_GET['client']) ? (int)$_GET['client'] : ($assignedClients[0]['id'] ?? 0);

// Get photos for selected client
$photos = [];
if ($selectedClient) {
    $photos = $db->query("SELECT * FROM progress_photos WHERE client_id = $selectedClient ORDER BY photo_date DESC")->fetch_all(MYSQLI_ASSOC) ?? [];
}
?>
<div class="page-title"><i class="bi bi-images"></i><h2>Manage Progress Photos</h2></div>

<div class="client-selector">
    <span><i class="bi bi-person-badge"></i> <strong>Select Client:</strong></span>
    <select id="clientSelect" class="form-select" style="width:250px" onchange="window.location.href='?client='+this.value">
        <option value="">-- Select Client --</option>
        <?php foreach ($assignedClients as $c): ?>
        <option value="<?php echo $c['id']; ?>" <?php echo $selectedClient == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-editor" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal"><i class="bi bi-cloud-upload"></i> Upload Photos</button>
</div>

<div class="photo-grid">
    <?php foreach ($photos as $p): ?>
    <div class="photo-card"><div class="photo-placeholder"><i class="bi bi-image fs-1"></i></div><div class="photo-info"><h6><?php echo ucfirst($p['body_part'] ?? 'Progress'); ?></h6><p class="small text-muted"><?php echo formatDate($p['photo_date']); ?></p><button class="btn btn-sm btn-outline-primary w-100" onclick="alert('Add comment')"><i class="bi bi-chat"></i> Add Comment</button></div></div>
    <?php endforeach; ?>
    <?php if (empty($photos)): ?><div class="text-center text-muted py-5"><i class="bi bi-camera fs-1"></i><p>No photos yet. Upload your first progress photo!</p></div><?php endif; ?>
</div>

<div class="modal fade" id="uploadPhotoModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Upload Progress Photo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form enctype="multipart/form-data"><div class="modal-body"><div class="mb-3"><label>Select Client</label><select class="form-select" required><?php foreach ($assignedClients as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select></div><div class="mb-3"><label>Photo</label><input type="file" class="form-control" accept="image/*" required></div><div class="mb-3"><label>Body Part</label><select class="form-select"><option>Front View</option><option>Side View</option><option>Back View</option></select></div><div class="mb-3"><label>Comments</label><textarea class="form-control" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-editor">Upload</button></div></form></div></div></div>

<?php require_once 'includes/footer.php'; ?>