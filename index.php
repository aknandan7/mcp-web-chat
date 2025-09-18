<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>iHRMS Chat Assistant</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    body {margin:0;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;background:#fafafa;overflow:hidden;}
    .sidebar {position:fixed;top:0;left:0;height:100vh;width:250px;background:#f5f5f5;border-right:1px solid #ddd;padding:15px;overflow-y:auto;transition:transform 0.3s ease;z-index:1000;}
    .sidebar h4 {margin-top:0;font-size:16px;font-weight:bold;}
    .sidebar ul {list-style:none;padding:0;}
    .sidebar ul li {padding:8px 10px;cursor:pointer;border-radius:4px;}
    .sidebar ul li:hover {background:#e0e0e0;}
    .toggle-btn {position:fixed;top:10px;left:10px;background:#fff;border:1px solid #ddd;border-radius:4px;padding:6px 10px;cursor:pointer;z-index:1100;display:none;}
    .content {margin-left:250px;height:100vh;display:flex;flex-direction:column;}
    .chat-header {padding:15px;border-bottom:1px solid #ddd;background:#fff;font-weight:bold;display:flex;justify-content:space-between;align-items:center;}
    .chat-box {flex:1;padding:15px;overflow-y:auto;background:#fafafa;}
    .chat-message {margin-bottom:15px;display:flex;flex-direction:column;}
    .chat-message.user {align-items:flex-start;}
    .chat-message.bot {align-items:flex-end;}
    .chat-message .bubble {padding:10px 14px;border-radius:12px;max-width:70%;display:inline-block;}
    .chat-message.user .bubble {background:#eaeaea;color:#000;margin-right:auto;}
    .chat-message.bot .bubble {background:#007bff;color:#fff;margin-left:auto;}
    .chat-time {display:block;font-size:11px;margin-top:4px;color:#666;}
    .chat-input {padding:10px;border-top:1px solid #ddd;background:#fff;}
    @media (max-width:768px){.sidebar{transform:translateX(-100%);}
    .sidebar.show{transform:translateX(0);}
    .content{margin-left:0;}
    .toggle-btn{display:block;}
    .chat-header {padding: 15px 84px;}
    .sidebar h4 {text-align: center;}
    #newChatBtn{margin-bottom: 3px;border-bottom: 1px solid #ddd;border-radius: 2px;}
}
</style>
</head>

<body>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div style="border-bottom:1px solid #ddd;padding:2px 0px 7px 34px;">
        <h4><i class="fa fa-comments"></i> iHRMS Chat</h4>
    </div>
    <ul>
        <li id="newChatBtn"><i class="fa fa-plus"></i> New Chat</li>
    </ul>
    <p><strong>Chats History</strong></p>
    <ul id="chatSessionsList"></ul>
</div>

<!-- Toggle button (mobile) -->
<button class="toggle-btn" id="toggleBtn"><i class="fa fa-bars"></i></button>

<!-- Main Content -->
<div class="content" id="content">
    <div class="chat-header"><span>iHRMS Chat Assistant</span></div>
    <div class="chat-box" id="chatBox"></div>
    <div class="chat-input">
        <input type="hidden" id="indo_code" value="SAM-EC2003">
        <input type="hidden" id="session_id" value="">
        <div class="input-group">
            <input type="text" id="query" class="form-control" placeholder="Type a message...">
            <span class="input-group-btn">
                <button class="btn btn-primary" id="sendBtn">Send</button>
            </span>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>
let currentSessionId = null;

function getCurrentTime(){
    const now = new Date();
    return now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
}

// Sidebar toggle
$('#toggleBtn').click(function(){ $('#sidebar').toggleClass('show'); });

// Load sessions
function loadSessions(){
    const empCode = $('#indo_code').val();
    $.ajax({
        url:'chat_process.php?action=get_sessions',
        method:'POST',
        contentType:'application/json',
        data:JSON.stringify({indo_code:empCode}),
        success:function(res){
            if(res.status==='success'){
                $('#chatSessionsList').html('');
                if(res.sessions.length===0){
                    $('#chatBox').html('<div style="text-align:center;color:#666;margin-top:20px;">No chat history yet</div>');
                    return;
                }
                res.sessions.forEach(s=>{
                    $('#chatSessionsList').append(`<li data-id="${s.id}">${s.title} <small>${s.created_at}</small></li>`);
                });
                // Load latest session
                const latestSessionId = res.sessions[0].id;
                loadHistory(latestSessionId);
            }
        }
    });
}

// Load chat history
function loadHistory(sessionId){
    if(!sessionId) return;
    currentSessionId = sessionId;
    $('#session_id').val(sessionId);
    $('#chatBox').html('');
    const empCode = $('#indo_code').val();
    $.ajax({
        url:'chat_process.php?action=get_history',
        method:'POST',
        contentType:'application/json',
        data:JSON.stringify({session_id:currentSessionId,indo_code:empCode}),
        success:function(res){
            $('#chatBox').html('');
            if(res.status==='success'){
                const history = res.history || res.messages || [];
                if(history.length===0){
                    $('#chatBox').html('<div style="text-align:center;color:#666;margin-top:20px;">No chat history yet</div>');
                } else {
                    history.forEach(m=>{
                        const cls = m.sender==='user' ? 'user' : 'bot';
                        $('#chatBox').append(`<div class="chat-message ${cls}"><div class="bubble">${m.message} <small class="chat-time">${m.created_at}</small></div></div>`);
                    });
                }
                $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
            } else alert(res.message);
        }
    });
}

// New session
function newSession(){
    const empCode = $('#indo_code').val();
    $.ajax({
        url:'chat_process.php?action=new_session',
        method:'POST',
        contentType:'application/json',
        data:JSON.stringify({indo_code:empCode}),
        success:function(res){
            if(res.status==='success'){
                currentSessionId=res.session_id;
                $('#session_id').val(currentSessionId);
                $('#chatBox').html(`<div class="chat-message bot"><div class="bubble">Hello! How can I help you today? <small>${getCurrentTime()}</small></div></div>`);
                loadSessions();
            } else alert(res.message || "Failed to create session");
        }
    });
}

// Send message
$('#sendBtn').click(function(){
    const query = $('#query').val().trim();
    if(!query) return;
    const empCode = $('#indo_code').val();
    if(!currentSessionId){ alert("Please start a new chat first!"); return; }

    // User left
    $('#chatBox').append(`<div class="chat-message user"><div class="bubble">${query} <small class="chat-time">${getCurrentTime()}</small></div></div>`);
    $('#query').val('');
    $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);

    // Bot typing
    const typingDiv = $('<div class="chat-message bot"><div class="bubble">Bot is typing...</div></div>');
    $('#chatBox').append(typingDiv);
    $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);

    $.ajax({
        url:'chat_process.php?action=send_message',
        method:'POST',
        contentType:'application/json',
        data:JSON.stringify({indo_code:empCode,session_id:currentSessionId,query:query}),
        success:function(res){
            typingDiv.remove();
            if(res.status==='success'){
                $('#chatBox').append(`<div class="chat-message bot"><div class="bubble">${res.response} <small class="chat-time">${getCurrentTime()}</small></div></div>`);
                $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
            } else alert(res.message);
        }
    });
});

$('#query').keypress(function(e){ if(e.which===13) $('#sendBtn').click(); });
$('#newChatBtn').click(function(){ newSession(); });
$('#chatSessionsList').on('click','li',function(){ loadHistory($(this).data('id')); });

$(document).ready(function(){ loadSessions(); });
</script>
</body>
</html>
