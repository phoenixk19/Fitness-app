<?php
// File: dashboard/coach/clients.php
require_once 'includes/header.php';

$clients = $db->query("
    SELECT u.id, u.name, u.email, u.phone, u.created_at,
           (SELECT weight FROM body_measurements WHERE client_id = u.id ORDER BY measurement_date DESC LIMIT 1) as weight,
           (SELECT COUNT(*) FROM client_workouts WHERE client_id = u.id AND status = 'completed') as workouts
    FROM users u
    JOIN coaches_clients cc ON u.id = cc.client_id
    WHERE cc.coach_id = $userId AND u.status = 'active'
    ORDER BY u.name
")->fetch_all(MYSQLI_ASSOC) ?? [];
?>
<div class="page-title"><i class="bi bi-people"></i><h2>My Clients</h2><button class="btn btn-coach ms-3" data-bs-toggle="modal" data-bs-target="#newClientModal"><i class="bi bi-plus-circle"></i> Add Client</button></div>

<div class="data-table">
    <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Current Weight</th><th>Workouts</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($clients as $c): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($c['email']); ?></td>
                <td><?php echo htmlspecialchars($c['phone']); ?></td>
                <td><?php echo $c['weight'] ?: '--'; ?> kg</td>
                <td><?php echo $c['workouts']; ?></td>
                <td><?php echo formatDate($c['created_at']); ?></td>
                <td><button class="btn btn-sm btn-outline-coach" onclick="editClient(<?php echo $c['id']; ?>)"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger" onclick="deleteClient(<?php echo $c['id']; ?>)"><i class="bi bi-trash"></i></button></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="newClientModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Add Client</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form><div class="modal-body"><div class="mb-3"><label>Name</label><input type="text" class="form-control"></div><div class="mb-3"><label>Email</label><input type="email" class="form-control"></div><div class="mb-3"><label>Phone</label><input type="tel" class="form-control"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-coach">Add Client</button></div></form></div></div></div>

<?php
$page_specific_scripts = '<script>function editClient(id){alert("Edit client "+id);} function deleteClient(id){if(confirm("Delete?")) alert("Delete "+id);}</script>';
require_once 'includes/footer.php';
?>