<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>iHRMS Chat Assistant</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {padding:20px;}
    .chat-box {height:400px; overflow-y:auto; border:1px solid #ccc; padding:10px; border-radius:8px; background:#f9f9f9;}
    .chat-message .bubble {display:inline-block; padding:10px 15px; border-radius:18px; max-width:70%; word-wrap:break-word; margin-bottom:5px;}
    .chat-message.user .bubble {background:#3498db; color:#fff;}
    .chat-message.bot .bubble {background:#ecf0f1; color:#2c3e50;}
    .sidebar {width:250px; float:left; margin-right:20px;}
    .sidebar ul {list-style:none; padding:0;}
    .sidebar ul li {padding:10px; border-bottom:1px solid #ccc; cursor:pointer;}
  </style>
</head>
<body>

<div class="row">
  <div class="col-md-3 sidebar" id="sidebar">
    <ul>
      <li id="newChatBtn"><i class="fa fa-plus"></i> New Chat</li>
      <hr>
      <p>Chats History</p>
      <ul id="chatSessionsList"></ul>
    </ul>
  </div>

  <div class="col-md-9">
    <h4>iHRMS Chat Assistant</h4>
    <div class="chat-box" id="chatBox"></div>
    <div class="input-group">
      <input type="hidden" id="indo_code" value="SAM-EC2003">
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

// Helper: get current time
function getCurrentTime() {
  const now = new Date();
  return now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
}

// Load chat sessions
function loadSessions() {
  const empCode = $('#indo_code').val();
  $.ajax({
    url:'chat_process.php?action=get_sessions',
    method:'POST',
    contentType:'application/json',
    data: JSON.stringify({indo_code:empCode}),
    success:function(res){
      if(res.status==='success'){
        $('#chatSessionsList').html('');
        res.sessions.forEach(s=>{
          $('#chatSessionsList').append(`<li data-id="${s.id}">${s.title} <small>${s.created_at}</small></li>`);
        });
      }
    }
  });
}

// Load chat history
function loadHistory(sessionId){
  $('#chatBox').html('');
  $.getJSON(`chat_process.php?action=get_history&session_id=${sessionId}`, function(res){
    if(res.status==='success'){
      res.messages.forEach(m=>{
        const cls = m.message_from==='user' ? 'user' : 'bot';
        $('#chatBox').append(`<div class="chat-message ${cls}"><div class="bubble">${m.message} <small>${m.created_at}</small></div></div>`);
      });
      $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
    }
  });
}

// Create new session
function newSession(callback){
  const empCode = $('#indo_code').val();
  $.ajax({
    url:'chat_process.php?action=new_session',
    method:'POST',
    contentType:'application/json',
    data: JSON.stringify({indo_code:empCode}),
    success:function(res){
      if(res.status==='success'){
        currentSessionId = res.session_id;
        $('#chatBox').html(`<div class="chat-message bot"><div class="bubble">Hello! How can I help you today? <small>${getCurrentTime()}</small></div></div>`);
        loadSessions();
        if(callback) callback();
      }
    }
  });
}

// Send message
$('#sendBtn').click(function(){
  const query = $('#query').val().trim();
  const empCode = $('#indo_code').val();
  if(!query || !currentSessionId) return;
  
  $('#chatBox').append(`<div class="chat-message user"><div class="bubble">${query} <small>${getCurrentTime()}</small></div></div>`);
  $('#query').val('');
  $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);

  // Typing indicator
  const typingDiv = $('<div class="chat-message bot"><div class="bubble">Bot is typing...</div></div>');
  $('#chatBox').append(typingDiv);
  $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);

  $.ajax({
    url:'chat_process.php?action=send_message',
    method:'POST',
    contentType:'application/json',
    data: JSON.stringify({indo_code:empCode, session_id:currentSessionId, query:query}),
    success:function(res){
      typingDiv.remove();
      if(res.status==='success'){
        $('#chatBox').append(`<div class="chat-message bot"><div class="bubble">${res.response} <small>${getCurrentTime()}</small></div></div>`);
        $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
      } else {
        alert(res.message);
      }
    }
  });
});

// Press enter to send
$('#query').keypress(function(e){if(e.which===13){$('#sendBtn').click();}});

// Click new chat
$('#newChatBtn').click(function(){newSession();});

// Click chat session from history
$('#chatSessionsList').on('click','li',function(){
  currentSessionId = $(this).data('id');
  loadHistory(currentSessionId);
});

// Initial load
$(document).ready(function(){
  loadSessions();
  newSession(); // optional: create new chat on page load
});

/*****************[ chat History Reload]****************** */
// Click event to load chat history
document.getElementById("chatSessionsList").addEventListener("click", function (e) {
    if (e.target && e.target.closest("li")) {
        const sessionId = e.target.closest("li").getAttribute("data-id");
        loadChatHistory(sessionId);
    }
});

// Function to fetch & show chat history
function loadChatHistory(sessionId) {
    const chatBox = document.getElementById("chatBox");
    chatBox.innerHTML = `<div class="chat-message bot"><div class="bubble">Loading chat history...</div></div>`;

    fetch("chat_history.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ session_id: sessionId })
    })
    .then(res => res.json())
    .then(data => {
        chatBox.innerHTML = ""; // Clear box
        if (data.status === "success" && data.history.length > 0) {
            data.history.forEach(msg => {
                const time = new Date(msg.created_at).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
                if (msg.sender === "user") {
                    chatBox.innerHTML += `<div class="chat-message user"><div class="bubble">You: ${msg.message} <span class="chat-time"><i class="fa fa-clock-o"></i> ${time}</span></div></div>`;
                } else {
                    chatBox.innerHTML += `<div class="chat-message bot"><div class="bubble">Bot: ${msg.message} <span class="chat-time"><i class="fa fa-clock-o"></i> ${time}</span></div></div>`;
                }
            });
        } else {
            chatBox.innerHTML = `<div class="chat-message bot"><div class="bubble">No history found for this session.</div></div>`;
        }
        chatBox.scrollTop = chatBox.scrollHeight;
    })
    .catch(err => {
        chatBox.innerHTML = `<div class="chat-message bot"><div class="bubble">Error loading history: ${err.message}</div></div>`;
    });
}
  
</script>
</body>
</html>
