<?php
// File: dashboard/editor/comments.php
require_once 'includes/header.php';

$selectedClient = isset($_GET['client']) ? (int)$_GET['client'] : ($assignedClients[0]['id'] ?? 0);
$selectedClientName = '';
foreach ($assignedClients as $c) { if($c['id'] == $selectedClient) { $selectedClientName = $c['name']; break; } }

// Get recent comments for this client
$recentComments = $db->query("
    SELECT al.*, u.name as user_name
    FROM activity_logs al
    JOIN users u ON al.user_id = u.id
    WHERE al.user_id = $selectedClient AND al.action = 'add_comment'
    ORDER BY al.created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC) ?? [];
?>
<div class="page-title"><i class="bi bi-chat-dots"></i><h2>Add Progress Comments</h2></div>

<div class="client-selector">
    <span><i class="bi bi-person-badge"></i> <strong>Select Client:</strong></span>
    <select id="clientSelect" class="form-select" style="width:250px" onchange="window.location.href='?client='+this.value">
        <?php foreach ($assignedClients as $c): ?>
        <option value="<?php echo $c['id']; ?>" <?php echo $selectedClient == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="comment-card">
    <div class="mb-3"><label class="form-label">Comment Type</label><select class="form-select"><option>Workout Performance</option><option>Nutrition Adherence</option><option>Progress Photo Feedback</option><option>General Feedback</option><option>Motivation</option></select></div>
    <div class="mb-3"><label class="form-label">Your Comment</label><textarea class="form-control" rows="4" placeholder="Add your feedback for the client..."></textarea></div>
    <button class="btn btn-editor" onclick="alert('Comment posted!')"><i class="bi bi-send"></i> Post Comment</button>
</div>

<h4 class="mb-3">Recent Comments</h4>
<?php foreach ($recentComments as $c): ?>
<div class="comment-box"><div class="comment-meta"><span><strong><?php echo htmlspecialchars($c['user_name']); ?></strong> • <?php echo formatDate($c['created_at']); ?></span></div><p><?php echo htmlspecialchars($c['details'] ?? 'Great progress!'); ?></p></div>
<?php endforeach; ?>

<?php require_once 'includes/footer.php'; ?>