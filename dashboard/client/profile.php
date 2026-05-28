<?php
// File: dashboard/client/profile.php
require_once 'includes/header.php';
?>
<div class="page-title"><i class="bi bi-person"></i><h2>My Profile</h2></div>

<div class="row">
    <div class="col-md-6"><div class="stat-card"><h5>Personal Information</h5><form><div class="mb-3"><label>Name</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($client['name']); ?>"></div><div class="mb-3"><label>Email</label><input type="email" class="form-control" value="<?php echo htmlspecialchars($client['email']); ?>"></div><div class="mb-3"><label>Phone</label><input type="tel" class="form-control" value="<?php echo htmlspecialchars($client['phone'] ?? ''); ?>"></div><div class="mb-3"><label>Member Since</label><input type="text" class="form-control" value="<?php echo formatDate($client['created_at']); ?>" disabled></div><button type="submit" class="btn btn-client">Update Profile</button></form></div></div>
    <div class="col-md-6"><div class="stat-card"><h5>Change Password</h5><form><div class="mb-3"><label>Current Password</label><input type="password" class="form-control"></div><div class="mb-3"><label>New Password</label><input type="password" class="form-control"></div><div class="mb-3"><label>Confirm Password</label><input type="password" class="form-control"></div><button type="submit" class="btn btn-outline-client">Change Password</button></form></div></div>
</div>

<?php require_once 'includes/footer.php'; ?>