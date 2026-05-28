<?php
// File: dashboard/client/photos.php
require_once 'includes/header.php';

$photos = $db->query("SELECT * FROM progress_photos WHERE client_id = $userId ORDER BY photo_date DESC")->fetch_all(MYSQLI_ASSOC) ?? [];
?>
<div class="page-title"><i class="bi bi-images"></i><h2>Progress Photos</h2><button class="btn-client ms-3" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal"><i class="bi bi-cloud-upload"></i> Upload Photo</button></div>

<div class="photos-grid">
    <?php foreach ($photos as $p): ?>
    <div class="photo-card"><div class="photo-placeholder"><i class="bi bi-image fs-1"></i></div><div class="photo-info"><h6><?php echo ucfirst($p['body_part'] ?? 'Progress'); ?></h6><p class="small text-muted"><?php echo formatDate($p['photo_date']); ?></p><button class="btn btn-sm btn-outline-primary w-100" onclick="alert('View photo')">View</button></div></div>
    <?php endforeach; ?>
    <?php if (empty($photos)): ?><div class="text-center text-muted py-5"><i class="bi bi-camera fs-1"></i><p class="mt-2">No photos yet. Upload your first progress photo!</p></div><?php endif; ?>
</div>

<div class="modal fade" id="uploadPhotoModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Upload Progress Photo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form enctype="multipart/form-data"><div class="modal-body"><div class="mb-3"><label>Photo</label><input type="file" class="form-control" accept="image/*"></div><div class="mb-3"><label>Body Part</label><select class="form-select"><option>Front View</option><option>Side View</option><option>Back View</option></select></div><div class="mb-3"><label>Notes</label><textarea class="form-control" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-client">Upload</button></div></form></div></div></div>

<?php require_once 'includes/footer.php'; ?>