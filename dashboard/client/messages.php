<?php
// File: dashboard/client/messages.php
require_once 'includes/header.php';
?>
<div class="page-title"><i class="bi bi-chat-dots"></i><h2>Messages</h2></div>

<div class="row">
    <div class="col-md-12"><div class="stat-card"><div id="chatArea" style="height:400px; overflow-y:auto; background:var(--light-bg); border-radius:10px; padding:15px; margin-bottom:15px;"><div class="text-center text-muted py-5">Your conversation with Coach will appear here</div></div><div class="input-group"><input type="text" id="messageInput" class="form-control" placeholder="Type your message..."><button class="btn btn-client" onclick="sendMessage()"><i class="bi bi-send"></i> Send</button></div></div></div>
</div>

<script>function sendMessage(){let input=document.getElementById("messageInput");if(input.value.trim()){alert("Message sent to your coach!");input.value="";}}</script>
<?php require_once 'includes/footer.php'; ?>