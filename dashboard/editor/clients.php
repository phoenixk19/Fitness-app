<?php
// File: dashboard/editor/clients.php
require_once 'includes/header.php';
?>
<div class="page-title"><i class="bi bi-people"></i><h2>Assigned Clients</h2></div>

<div class="data-table">
    <table class="table">
        <thead><tr><th>Client Name</th><th>Email</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($assignedClients as $c): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($c['email']); ?></td>
                <td><a href="workouts.php?client=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-editor"><i class="bi bi-activity"></i> Log Workout</a>
                    <a href="nutrition.php?client=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-editor"><i class="bi bi-egg-fried"></i> Log Meal</a>
                    <a href="comments.php?client=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-editor"><i class="bi bi-chat"></i> Comment</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($assignedClients)): ?>
    <p class="text-center text-muted">No clients assigned yet</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>