<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>iHRMS Chat Assistant</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/custom.css">
    <style>
        /* Typing animation dots */
        .typing span {
            display: inline-block;
            width: 6px;
            height: 6px;
            margin: 0 2px;
            background: #333;
            border-radius: 50%;
            animation: blink 1.4s infinite both;
        }

        .typing span:nth-child(2) { animation-delay: 0.2s; }
        .typing span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes blink {
            0%, 80%, 100% { opacity: 0; }
            40% { opacity: 1; }
        }

        .chat-message .bubble {
            display: inline-block;
            white-space: normal; /* prevents marquee/scroll */
        }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div style="border-bottom:1px solid #ddd;padding:0px 0px 7px 34px;">
            <h4><img src="https://indovisionservices.in/info/dev/v15//uploads/ihrms/ihrms-logo.png" height="30px"
                    style="margin-right:10px">iHRMS Chat</h4>
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
                <input type="text" id="query" class="form-control input-lg chat-input" autofocus
                    placeholder="Type a message..." style="padding-left:25px;border-radius:30px;">
                <span class="input-group-btn">
                    <button class="btn btn-default btn-lg icon-btn" id="micBtn"><i
                            class='fa fa-microphone'></i></button>
                </span>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>
        let currentSessionId = null;
        let previousFirstQuestion = null;

        function getCurrentTime() {
            const now = new Date();
            return now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        }

        $('#toggleBtn').click(function () {
            $('#sidebar').toggleClass('show');
        });

        function markActiveSession(li) {
            $('#chatSessionsList li').removeClass('active-session').find('.live-dot').remove();
            $(li).addClass('active-session');
            if (!$(li).find('.live-dot').length) {
                $(li).find('strong').append('<span class="live-dot"></span>');
            }
        }

        function loadSessions() {
            const empCode = $('#indo_code').val();
            $.ajax({
                url: 'chat_process.php?action=get_sessions',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ indo_code: empCode }),
                success: function (res) {
                    if (res.status === 'success') {
                        $('#chatSessionsList').html('');
                        if (res.sessions.length === 0) {
                            $('#chatBox').html('<div style="text-align:center;color:#666;margin-top:20px;">No chat history yet</div>');
                            return;
                        }
                        res.sessions.forEach((session, index) => {
                            let title = session.title || "Untitled";
                            let words = title.split(/\s+/);
                            if (words.length > 5) title = words.slice(0, 5).join(' ') + '.';

                            let li = $(`<li data-id="${session.id}"><strong>${title}</strong><br>
                                <small style="font-size:10px;color:#181717c7;margin-top:0px;position:relative;">
                                    ${session.created_at}
                                </small></li>`);

                            li.on('click', function () {
                                markActiveSession($(this));
                                currentSessionId = session.id;
                                $('#session_id').val(currentSessionId);
                                loadHistory(session.id);
                            });

                            $('#chatSessionsList').append(li);

                            if (index === 0 && !currentSessionId) {
                                markActiveSession(li);
                                currentSessionId = session.id;
                                $('#session_id').val(currentSessionId);
                                loadHistory(session.id);
                            }
                        });
                    }
                }
            });
        }

        function loadHistory(sessionId) {
            if (!sessionId) return;
            currentSessionId = sessionId;
            $('#session_id').val(sessionId);
            $('#chatBox').html('');
            const empCode = $('#indo_code').val();
            $.ajax({
                url: 'chat_process.php?action=get_history',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ session_id: currentSessionId, indo_code: empCode }),
                success: function (res) {
                    $('#chatBox').html('');
                    if (res.status === 'success') {
                        const history = res.history || [];
                        if (history.length === 0) {
                            $('#chatBox').html('<div style="text-align:center;color:#666;margin-top:20px;">No chat history yet</div>');
                        } else {
                            previousFirstQuestion = history[0].message;
                            history.forEach(m => {
                                const cls = m.sender === 'user' ? 'user' : 'bot';
                                $('#chatBox').append(
                                    `<div class="chat-message ${cls}"><div class="bubble">${m.message} <small class="chat-time">${m.created_at}</small></div></div>`
                                );
                            });
                        }
                    } else alert(res.message);
                }
            });
        }

        function newSession() {
            const empCode = $('#indo_code').val();
            $('#query').focus();
            if (previousFirstQuestion) {
                $.ajax({
                    url: 'chat_process.php?action=update_title',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ indo_code: empCode, session_id: currentSessionId, title: previousFirstQuestion })
                });
            }
            currentSessionId = null;
            $('#session_id').val('');
            $('#chatBox').html('');
            previousFirstQuestion = null;
        }

        function sendMessage(query) {
            if (!query) return;
            const empCode = $('#indo_code').val();

            if (!currentSessionId) {
                $.ajax({
                    url: 'chat_process.php?action=new_session',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ indo_code: empCode }),
                    success: function (res) {
                        if (res.status === 'success') {
                            currentSessionId = res.session_id;
                            $('#session_id').val(currentSessionId);
                            loadSessions();
                            sendMessage(query);
                        } else alert(res.message || "Failed to create session");
                    }
                });
                return;
            }

            // user message
            $('#chatBox').append(`<div class="chat-message user"><div class="bubble">${query} <small class="chat-time">${getCurrentTime()}</small></div></div>`);
            $('#query').val('');

            // typing animation
            const typingDiv = $('<div class="chat-message bot"><div class="bubble typing"><span></span><span></span><span></span></div></div>');
            $('#chatBox').append(typingDiv);

            // ajax send
            $.ajax({
                url: 'chat_process.php?action=send_message',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ indo_code: empCode, session_id: currentSessionId, query: query }),
                success: function (res) {
                    typingDiv.remove();
                    if (res.status === 'success') {
                        // direct append without scrolling letters
                        $('#chatBox').append(`<div class="chat-message bot"><div class="bubble">${res.response} <small class="chat-time">${getCurrentTime()}</small></div></div>`);
                    } else alert(res.message);
                }
            });
        }

        $('#query').keypress(function (e) {
            if (e.which === 13) {
                e.preventDefault();
                sendMessage($('#query').val().trim());
            }
        });
        $('#newChatBtn').click(function () { newSession(); });
        $(document).ready(function () { loadSessions(); });

        // Voice / Speech Recognition
        let recognition;
        if ('webkitSpeechRecognition' in window) {
            recognition = new webkitSpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US';
            recognition.onstart = function () { $('#micBtn').addClass('active'); };
            recognition.onend = function () { $('#micBtn').removeClass('active'); };
            recognition.onresult = function (event) {
                const transcript = event.results[0][0].transcript;
                $('#query').val(transcript);
                sendMessage(transcript);
            };
        }
        $('#micBtn').click(function () { if (recognition) recognition.start(); });
    </script>
</body>
</html>
