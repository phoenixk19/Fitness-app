<?php
// File: dashboard/coach/packages.php
require_once 'includes/header.php';

$packages = $db->query("SELECT * FROM packages WHERE coach_id = $userId ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC) ?? [];
?>
<div class="page-title"><i class="bi bi-box-seam"></i><h2>My Packages</h2><button class="btn btn-coach ms-3" data-bs-toggle="modal" data-bs-target="#createPackageModal"><i class="bi bi-plus-circle"></i> Create Package</button></div>

<div class="packages-grid">
    <?php foreach ($packages as $p): ?>
    <div class="package-card"><div style="height:5px; background:<?php echo htmlspecialchars($p['color_theme'] ?? '#4A6FA5'); ?>; border-radius:5px; margin-bottom:15px;"></div><h5><?php echo htmlspecialchars($p['name']); ?></h5><p class="small text-muted"><?php echo htmlspecialchars($p['description'] ?? 'No description'); ?></p><ul class="small mb-3"><?php $benefits = explode("\n", $p['benefits'] ?? ''); foreach(array_slice($benefits,0,2) as $b): if(trim($b)): ?><li><?php echo htmlspecialchars(trim($b)); ?></li><?php endif; endforeach; ?></ul><div class="d-flex justify-content-between"><span class="fw-bold">$<?php echo number_format($p['price'],2); ?>/<?php echo $p['payment_type']; ?></span><button class="btn btn-sm btn-outline-coach" onclick="editPackage(<?php echo $p['id']; ?>)">Edit</button></div></div>
    <?php endforeach; ?>
</div>

<div class="modal fade" id="createPackageModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Create Package</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form><div class="modal-body"><div class="mb-3"><label>Package Name</label><input type="text" class="form-control"></div><div class="mb-3"><label>Price</label><input type="number" class="form-control"></div><div class="mb-3"><label>Payment Type</label><select class="form-select"><option>monthly</option><option>weekly</option><option>yearly</option></select></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-coach">Create</button></div></form></div></div></div>

<?php
$page_specific_scripts = '<script>function editPackage(id){alert("Edit package "+id);}</script>';
require_once 'includes/footer.php';
?>