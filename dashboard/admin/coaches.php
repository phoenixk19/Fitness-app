<?php
// File: dashboard/admin/coaches.php
require_once 'includes/header.php';

// Handle add/edit/delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $db->query("INSERT INTO users (name, email, password_hash, role, status) VALUES ('$name', '$email', '$password', 'coach', 'active')");
        echo '<script>alert("Coach added successfully"); window.location.href="coaches.php";</script>';
    } elseif ($action === 'reset_password') {
        $id = (int)$_POST['id'];
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $db->query("UPDATE users SET password_hash = '$newPassword' WHERE id = $id AND role = 'coach'");
        echo '<script>alert("Password reset successfully"); window.location.href="coaches.php";</script>';
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $db->query("UPDATE users SET status = 'inactive' WHERE id = $id AND role = 'coach'");
        echo '<script>alert("Coach removed"); window.location.href="coaches.php";</script>';
    }
}

$coaches = $db->query("SELECT id, name, email, created_at, status FROM users WHERE role = 'coach' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC) ?? [];
?>
<div class="page-title"><i class="bi bi-people"></i><h2>Manage Coaches</h2><button class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#addCoachModal"><i class="bi bi-plus-circle"></i> Add Coach</button></div>

<div class="data-table">
    <table class="table">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($coaches as $c): ?>
            <tr>
                <td><?php echo $c['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($c['email']); ?></td>
                <td><?php echo formatDate($c['created_at']); ?></td>
                <td><span class="badge bg-success"><?php echo ucfirst($c['status']); ?></span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#resetPasswordModal" onclick="setResetId(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['name']); ?>')"><i class="bi bi-key"></i> Reset Pwd</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCoach(<?php echo $c['id']; ?>)"><i class="bi bi-trash"></i> Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Coach Modal -->
<div class="modal fade" id="addCoachModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Add New Coach</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><input type="hidden" name="action" value="add"><div class="modal-body"><div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required></div><div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div><div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Coach</button></div></form></div></div></div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Reset Coach Password</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><input type="hidden" name="action" value="reset_password"><input type="hidden" name="id" id="resetCoachId"><div class="modal-body"><p>Reset password for: <strong id="resetCoachName"></strong></p><div class="mb-3"><label>New Password</label><input type="text" name="new_password" class="form-control" value="<?php echo bin2hex(random_bytes(3)); ?>" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Reset Password</button></div></form></div></div></div>

<script>
function setResetId(id, name) { document.getElementById('resetCoachId').value = id; document.getElementById('resetCoachName').innerText = name; }
function deleteCoach(id) { if(confirm('Are you sure?')) { var form = document.createElement('form'); form.method='POST'; form.innerHTML='<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="'+id+'">'; document.body.appendChild(form); form.submit(); } }
</script>
<?php require_once 'includes/footer.php'; ?>