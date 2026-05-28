<?php
// File: dashboard/coach/messages.php
require_once 'includes/header.php';

$selectedClient = isset($_GET['client']) ? (int)$_GET['client'] : 0;
?>
<div class="page-title"><i class="bi bi-messenger"></i><h2>Messages</h2></div>

<div class="row">
    <div class="col-md-4"><div class="stat-card"><h5>Conversations</h5><div class="list-group"><?php foreach ($clients as $c): ?><a href="?client=<?php echo $c['id']; ?>" class="list-group-item list-group-item-action <?php echo $selectedClient==$c['id']?'active':''; ?>"><div class="d-flex"><div class="client-avatar me-2" style="width:40px;height:40px;font-size:1rem;"><?php echo strtoupper(substr($c['name'],0,2)); ?></div><div><strong><?php echo htmlspecialchars($c['name']); ?></strong><br><small>Click to message</small></div></div></a><?php endforeach; ?></div></div></div>
    <div class="col-md-8"><div class="stat-card"><h5><?php echo $selectedClient ? htmlspecialchars(array_column($clients, 'name', 'id')[$selectedClient] ?? 'Select a client') : 'Select a client'; ?></h5><div id="chatArea" style="height:400px; overflow-y:auto; background:var(--light-bg); border-radius:10px; padding:15px; margin-bottom:15px;"><div class="text-center text-muted py-5">Select a client to start messaging</div></div><?php if($selectedClient): ?><div class="input-group"><input type="text" id="messageInput" class="form-control" placeholder="Type your message..."><button class="btn btn-coach" onclick="sendMessage()"><i class="bi bi-send"></i> Send</button></div><?php endif; ?></div></div>
</div>

<?php
$page_specific_scripts = '
<script>function sendMessage(){let input=document.getElementById("messageInput");if(input.value.trim()){alert("Message sent to client!");input.value="";}}</script>';
require_once 'includes/footer.php';
?>