<?php
// File: dashboard/client/leaderboard.php
require_once 'includes/header.php';

$leaderboard = $db->query("
    SELECT u.name, l.score, l.metric_type
    FROM leaderboard l JOIN users u ON l.client_id = u.id
    WHERE l.metric_type = 'consistency'
    ORDER BY l.score DESC LIMIT 10
")->fetch_all(MYSQLI_ASSOC) ?? [];

$userRank = 0;
foreach ($leaderboard as $i => $l) { if($l['name'] == $client['name']) { $userRank = $i+1; break; } }
?>
<div class="page-title"><i class="bi bi-trophy"></i><h2>Leaderboard</h2></div>

<div class="row">
    <div class="col-md-4"><div class="stat-card text-center"><div class="display-1 text-warning"><i class="bi bi-trophy"></i></div><h3>Your Rank: #<?php echo $userRank ?: 'N/A'; ?></h3><div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" id="optIn" checked><label class="form-check-label">Show me on leaderboard</label></div></div></div>
    <div class="col-md-8"><div class="stat-card"><h5>Top Performers</h5><div class="leaderboard-list"><?php foreach ($leaderboard as $i => $l): ?><div class="leaderboard-item <?php echo $l['name'] == $client['name'] ? 'me' : ''; ?>"><div class="rank"><?php echo $i+1; ?></div><div><h6><?php echo htmlspecialchars($l['name']); ?></h6><p class="mb-0 small text-muted">Score: <?php echo $l['score']; ?> points</p></div></div><?php endforeach; ?></div></div></div>
</div>

<script>document.getElementById('optIn')?.addEventListener('change', function() { alert(this.checked ? 'You are now visible on leaderboard' : 'You are now hidden'); });</script>
<?php require_once 'includes/footer.php'; ?>