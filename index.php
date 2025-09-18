<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>iHRMS Chat Assistant</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
/* Fix for mic button to look professional and perfectly round */
#micBtn {
width: 25px;
height: 25px;
border-radius: 50% !important;
display: flex;
align-items: center;
justify-content: center;
border: none !important;
outline: none !important;
background: #f8f9fa;
color: #555;
font-size: 14px;
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
transition: all 0.3s ease;
margin-right: 17px;
}

#micBtn:hover {
background: #007bff;
color: #fff;
}

#micBtn.active {
background: #dc3545 !important; /* Red when recording */
color: #fff !important;
animation: pulse 1.5s infinite;
}

/* Pulse animation */
@keyframes pulse {
0% { box-shadow: 0 0 0 0 rgba(220,53,69,0.7); }
70% { box-shadow: 0 0 0 15px rgba(220,53,69,0); }
100% { box-shadow: 0 0 0 0 rgba(220,53,69,0); }
}

body {margin:0;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;background:#fafafa;overflow:hidden;}
.sidebar {position:fixed;top:0;left:0;height:100vh;width:250px;background:#f5f5f5;border-right:1px solid #ddd;padding:15px;overflow-y:auto;transition:transform 0.3s ease;z-index:1000;}
.sidebar h4 {margin-top:0;font-size:16px;font-weight:bold;}
.sidebar ul {list-style:none;padding:0;}
.sidebar ul li {padding:8px 10px;cursor:pointer;border-radius:4px;}
.sidebar ul li:hover {background:#e0e0e0;}
.toggle-btn {position:fixed;top:10px;left:10px;background:#fff;border:1px solid #ddd;border-radius:4px;padding:6px 10px;cursor:pointer;z-index:1100;display:none;}
.content {margin-left:250px;height:100vh;display:flex;flex-direction:column;}
.chat-header {padding:15px;border-bottom:1px solid #ddd;background:#fff;font-weight:bold;display:flex;justify-content:space-between;align-items:center;}
.chat-box {flex:1;padding:15px;overflow-y:auto;background:#fafafa; background-image: url('image/indo-backimage-chat.jpg');background-size: contain;}
.chat-message {margin-bottom:15px;display:flex;flex-direction:column;}
.chat-message.user {align-items:flex-start;}
.chat-message.bot {align-items:flex-end;}
.chat-message .bubble {padding:10px 14px;border-radius:12px;max-width:70%;display:inline-block;}
.chat-message.user .bubble {background:#ffffff;color:#3f3e3e;margin-right:auto;}
.chat-message.bot .bubble {background:#d9fdd3;color:#3f3e3e;margin-left:auto;}
.chat-time {display:block;font-size:11px;margin-top:4px;color:#666;}
.chat-input {padding:10px;border-top:1px solid #ddd;background:#fff;}
/* Container tweaks */
.whatsapp-input-bar {border-radius: 30px;overflow: hidden;background: #fff;border: 1px solid #ccc;padding: 0px;}
.icon-btn {background: transparent;border: none;color: #555;font-size: 18px;padding: 0 10px;cursor: pointer;transition: all 0.3s ease;}
.chat-input {border: none !important;box-shadow: none !important;font-size: 16px;background: transparent;}
.icon-btn:hover {color: #000;}
#chatSessionsList li.active-session {
    background: #007bff;
    color: #fff;
}
#chatSessionsList li.active-session:hover {
    background: #0056b3;
}

@media (max-width:768px){
    .sidebar{transform:translateX(-100%);}
    .sidebar.show{transform:translateX(0);}
    .content{margin-left:0;}
    .toggle-btn{display:block;}
    .chat-header {padding: 15px 84px;}
    .sidebar h4 {text-align: center;}
    #newChatBtn{margin-bottom: 3px;border-bottom: 1px solid #ddd;border-radius: 2px;}
}

/* Mic active recording state */
#micBtn.active {
  background: #dc3545 !important;
  border-radius: 50%;
  color: #fff !important;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
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
        <div class="input-group whatsapp-input-bar">
            <input type="text" id="query" class="form-control input-lg chat-input" placeholder="Type a message..." style="padding-left: 25px;border-radius: 30px;">
            <span class="input-group-btn">
                <!-- Mic button -->
                <button class="btn btn-default btn-lg icon-btn" id="micBtn">
                    <i class='fa fa-microphone'></i>
                </button>
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
                // Highlight latest session
                const latestSessionId = res.sessions[0].id;
                $('#chatSessionsList li').removeClass('active-session');
                $(`#chatSessionsList li[data-id="${latestSessionId}"]`).addClass('active-session');
                loadHistory(latestSessionId);
            }
        }
    });
}

// Click on session
$('#chatSessionsList').on('click','li',function(){
    const sessionId = $(this).data('id');
    // Highlight selected session
    $('#chatSessionsList li').removeClass('active-session');
    $(this).addClass('active-session');
    loadHistory(sessionId);
});


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

// ===== Auto-send message function =====
function sendMessage(query){
    if(!query) return;
    const empCode = $('#indo_code').val();
    if(!currentSessionId){ alert("Please start a new chat first!"); return; }

    // Show user message
    $('#chatBox').append(`<div class="chat-message user"><div class="bubble">${query} <small class="chat-time">${getCurrentTime()}</small></div></div>`);
    $('#query').val('');
    $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);

    // Show typing indicator
    const typingDiv = $('<div class="chat-message bot"><div class="bubble">Bot is typing...</div></div>');
    $('#chatBox').append(typingDiv);
    $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);

    // AJAX call
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
}

// Auto-send on Enter key
$('#query').keypress(function(e){
    if(e.which===13){
        e.preventDefault();
        const query = $('#query').val().trim();
        sendMessage(query);
    }
});

$('#newChatBtn').click(function(){ newSession(); });
$('#chatSessionsList').on('click','li',function(){ loadHistory($(this).data('id')); });
$(document).ready(function(){ loadSessions(); });

// ===== Speech Recognition Toggle =====
const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
let recognition;
let isListening = false;
let listeningDiv = null;

if(SpeechRecognition){
    recognition = new SpeechRecognition();
    recognition.lang = 'en-US';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    $('#micBtn').click(function(){
        if(!isListening){
            try {
                recognition.start();
            } catch(e) {
                console.warn("Recognition already started.");
            }
        } else {
            recognition.stop();
        }
    });

    recognition.onstart = function(){
        isListening = true;
        $('#micBtn').addClass('active');
        $('#micBtn i').removeClass('fa-microphone').addClass('fa-times');

        if(listeningDiv) listeningDiv.remove();
        listeningDiv = $('<div class="chat-message bot"><div class="bubble"><i class="fa fa-microphone"></i> Listening...</div></div>');
        $('#chatBox').append(listeningDiv);
        $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
    };

    //  AUto Send Message when mic enable
    recognition.onresult = function(event){
        const resultIndex = event.resultIndex;
        const result = event.results[resultIndex];

        // Only send when final transcript is ready
        if(result.isFinal){
            const speechResult = result[0].transcript.trim();
            if(speechResult !== ''){
                sendMessage(speechResult); // Auto-send
            }
        }
    };


    
    recognition.onend = function(){
        isListening = false;
        $('#micBtn').removeClass('active');
        $('#micBtn i').removeClass('fa-times').addClass('fa-microphone');
        if(listeningDiv) listeningDiv.remove();
        listeningDiv = null;
    };

    recognition.onerror = function(event){
        isListening = false;
        $('#micBtn').removeClass('active');
        $('#micBtn i').removeClass('fa-times').addClass('fa-microphone');
        if(listeningDiv) listeningDiv.remove();
        listeningDiv = null;
        console.error("Speech recognition error: ", event.error);
    };
} else {
    alert("Sorry, your browser does not support Speech Recognition.");
}
</script>
</body>
</html>
