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
.chat-header {padding:20px;border-bottom:1px solid #ddd;background:#fff;font-weight:bold;text-align:left;}
.chat-box {flex:1;padding:15px;overflow-y:auto;background:#fafafa;}
.chat-message {margin-bottom:15px;display:flex;flex-direction:column;}
.chat-message.user {align-items:flex-start;}
.chat-message.bot {align-items:flex-end;}
.chat-message .bubble {padding:10px 14px;border-radius:12px;max-width:70%;display:inline-block;text-transform:capitalize;}
.chat-message.user .bubble {background:#ffffff;color:#3f3e3e;margin-right:auto;}
.chat-message.bot .bubble {background:#d9fdd3;color:#3f3e3e;margin-left:auto;}
.chat-time {display:block;font-size:11px;margin-top:4px;color:#666;}
.chat-input {padding:10px;border-top:1px solid #ddd;background:#fff;}
.whatsapp-input-bar {border-radius: 30px;overflow: hidden;background: #fff;border: 1px solid #ccc;padding: 0px;}
.icon-btn {background: transparent;border: none;color: #555;font-size: 18px;padding: 0 10px;cursor: pointer;transition: all 0.3s ease;}
.icon-btn:hover {color: #000;}
#chatSessionsList li.active-session {color: #1604ff;font-weight: 700;}
.live-dot {display:inline-block;width:10px;height:10px;background:#28a745;border-radius:50%;margin-left:6px;animation:blink 1s infinite;vertical-align:middle;}
@keyframes blink{0%,50%,100%{opacity:1}25%,75%{opacity:0}}
.bubble.typing {width:50px;height:16px;display:flex;align-items:center;justify-content:space-between;}
.bubble.typing span {display:block;width:6px;height:6px;background:#666;border-radius:50%;animation:blinkDots 1.4s infinite both;}
.bubble.typing span:nth-child(1) {animation-delay:0s;}
.bubble.typing span:nth-child(2) {animation-delay:0.2s;}
.bubble.typing span:nth-child(3) {animation-delay:0.4s;}
@keyframes blinkDots {0%, 80%, 100% {opacity:0;} 40% {opacity:1;}}
#micBtn {width:25px;height:25px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:none;outline:none;background:#f8f9fa;color:#555;font-size:14px;box-shadow:0 4px 8px rgba(0,0,0,0.2);transition:all 0.3s ease;margin-right:17px;}
#micBtn:hover {background:#007bff;color:#fff;}
#micBtn.active {background:#dc3545;color:#fff;animation:pulse 1.5s infinite;}
@keyframes pulse {0% {transform:scale(1);} 50% {transform:scale(1.1);} 100% {transform:scale(1);}}
@media (max-width:768px){.sidebar{transform:translateX(-100%);}.sidebar.show{transform:translateX(0);}.content{margin-left:0;}.toggle-btn{display:block;}}
#chatSessionsList li small::before {content: "\f017"; font-family: FontAwesome;margin-right:4px;font-size:10px;color:#181717c7;position:relative;top:1px;}
</style>
</head>
<body>
<div class="sidebar" id="sidebar">
    <div style="border-bottom:1px solid #ddd;padding:9px 0px 7px 34px;">
        <h4><i class="fa fa-comments"></i> iHRMS Chat</h4>
    </div>
    <ul>
        <li id="newChatBtn"><i class="fa fa-plus"></i> New Chat</li>
    </ul>
    <p><strong>Chats History</strong></p>
    <ul id="chatSessionsList"></ul>
</div>

<button class="toggle-btn" id="toggleBtn"><i class="fa fa-bars"></i></button>

<div class="content" id="content">
    <div class="chat-header"><span>iHRMS Chat Assistant</span></div>
    <div class="chat-box" id="chatBox"></div>
    <div class="chat-input">
        <input type="hidden" id="indo_code" value="SAM-EC2003">
        <input type="hidden" id="session_id" value="">
        <div class="input-group whatsapp-input-bar">
            <input type="text" id="query" class="form-control input-lg chat-input" autofocus placeholder="Type a message..." style="padding-left:25px;border-radius:30px;">
            <span class="input-group-btn">
                <button class="btn btn-default btn-lg icon-btn" id="micBtn"><i class='fa fa-microphone'></i></button>
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

$('#toggleBtn').click(function(){ $('#sidebar').toggleClass('show'); });

function markActiveSession(li){
    $('#chatSessionsList li').removeClass('active-session').find('.live-dot').remove();
    $(li).addClass('active-session');
    $(li).find('strong').append('<span class="live-dot"></span>');
}

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
                if(res.sessions.length === 0){
                    $('#chatBox').html('<div style="text-align:center;color:#666;margin-top:20px;">No chat history yet</div>');
                    return;
                }
                res.sessions.forEach((session, index) => {
                    let title = session.title || "New Chat";
                    let words = title.split(/\s+/);
                    if(words.length > 5){ title = words.slice(0,5).join(' ') + '.'; }

                    let li = $(`<li data-id="${session.id}" ${index===0 ? 'class="active-session"' : ''}>
                        <strong>${title}${index===0 ? '<span class="live-dot"></span>' : ''}</strong><br>
                        <small style="font-size:10px;color:#181717c7;margin-top:0px;position:relative;">${session.created_at}</small>
                    </li>`);
                    $('#chatSessionsList').append(li);

                    if(index===0){
                        currentSessionId = session.id;
                        $('#session_id').val(currentSessionId);
                        loadHistory(session.id);
                    }
                });
            }
        }
    });
}

$('#chatSessionsList').on('click','li',function(){ markActiveSession(this); loadHistory($(this).data('id')); });

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

function sendMessage(query){
    if(!query) return;
    const empCode = $('#indo_code').val();
    if(!currentSessionId){ alert("Please start a new chat first!"); return; }

    $('#chatBox').append(`<div class="chat-message user"><div class="bubble">${query} <small class="chat-time">${getCurrentTime()}</small></div></div>`);
    $('#query').val('');
    $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);

    const typingDiv = $('<div class="chat-message bot"><div class="bubble typing"><span></span><span></span><span></span></div></div>');
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
}

$('#query').keypress(function(e){ if(e.which===13){ e.preventDefault(); sendMessage($('#query').val().trim()); } });
$('#newChatBtn').click(function(){ newSession(); });
$(document).ready(function(){ loadSessions(); });

// ===== Voice / Speech Recognition =====
let recognition;
if('webkitSpeechRecognition' in window){
    recognition = new webkitSpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';

    recognition.onstart = function(){ $('#micBtn').addClass('active'); };
    recognition.onend = function(){ $('#micBtn').removeClass('active'); };
    recognition.onresult = function(event){
        const transcript = event.results[0][0].transcript;
        $('#query').val(transcript);
        sendMessage(transcript);
    };
}

$('#micBtn').click(function(){
    if(recognition) recognition.start();
});
</script>
</body>
</html>
