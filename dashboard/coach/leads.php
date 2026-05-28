<?php
// File: dashboard/coach/leads.php
require_once 'includes/header.php';

// Handle convert to client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'convert') {
    $leadId = (int)$_POST['lead_id'];
    $lead = $db->query("SELECT * FROM leads WHERE id = $leadId")->fetch_assoc();
    if ($lead) {
        $password = bin2hex(random_bytes(4));
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $db->query("INSERT INTO users (name, email, phone, password_hash, role, status) VALUES ('{$lead['first_name']} {$lead['last_name']}', '{$lead['email']}', '{$lead['phone']}', '$passwordHash', 'client', 'active')");
        $clientId = $db->insert_id;
        // Assign client to this coach
        $db->query("INSERT INTO coaches_clients (coach_id, client_id) VALUES ($userId, $clientId)");
        $db->query("UPDATE leads SET status = 'converted', converted_to_client_id = $clientId WHERE id = $leadId");
        echo '<script>alert("Lead converted to client! Password: ' . $password . '"); window.location.href="leads.php";</script>';
    }
}

// Handle reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'reject') {
    $id = (int)$_POST['id'];
    $db->query("UPDATE leads SET status = 'rejected' WHERE id = $id");
    echo '<script>alert("Lead rejected"); window.location.href="leads.php";</script>';
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update_status') {
    $id = (int)$_POST['id'];
    $status = sanitize($_POST['status']);
    $db->query("UPDATE leads SET status = '$status' WHERE id = $id");
    echo '<script>window.location.href="leads.php";</script>';
}

$leads = $db->query("SELECT * FROM leads ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC) ?? [];
?>
<div class="page-title"><i class="bi bi-person-plus"></i><h2>Member Applications (Leads)</h2></div>

<div class="data-table">
    <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Goal</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($leads as $l): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($l['first_name'] . ' ' . $l['last_name']); ?></strong></td>
                <td><?php echo htmlspecialchars($l['email']); ?></td>
                <td><?php echo htmlspecialchars($l['phone']); ?></td>
                <td><?php echo htmlspecialchars($l['fitness_goal'] ?? 'N/A'); ?></td>
                <td>
                    <form method="POST" style="display:inline-block">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" value="<?php echo $l['id']; ?>">
                        <select name="status" class="form-select form-select-sm" style="width:120px" onchange="this.form.submit()">
                            <option value="new" <?php echo $l['status']=='new'?'selected':''; ?>>New</option>
                            <option value="contacted" <?php echo $l['status']=='contacted'?'selected':''; ?>>Contacted</option>
                            <option value="converted" <?php echo $l['status']=='converted'?'selected':''; ?>>Converted</option>
                            <option value="rejected" <?php echo $l['status']=='rejected'?'selected':''; ?>>Rejected</option>
                        </select>
                    </form>
                </td>
                <td><?php echo formatDate($l['created_at']); ?></td>
                <td>
                    <?php if ($l['status'] != 'converted' && $l['status'] != 'rejected'): ?>
                    <form method="POST" style="display:inline-block">
                        <input type="hidden" name="action" value="convert">
                        <input type="hidden" name="lead_id" value="<?php echo $l['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Convert to client?')"><i class="bi bi-person-check"></i> Convert</button>
                    </form>
                    <form method="POST" style="display:inline-block">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="id" value="<?php echo $l['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Reject this application?')"><i class="bi bi-x-circle"></i> Reject</button>
                    </form>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-outline-primary" onclick="window.location.href='mailto:<?php echo $l['email']; ?>'"><i class="bi bi-envelope"></i> Contact</button>
                 </td>
             </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>