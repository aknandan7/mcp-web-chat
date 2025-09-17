<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Responsive HRMS Chat</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/custom.css">
  <style>
    .chat-message .bubble {
      display: inline-block;
      padding: 10px 15px;
      border-radius: 18px;
      max-width: 70%;
      word-wrap: break-word;
    }

    .chat-message.user .bubble {
      background: #3498db;
      color: #fff;
      text-align: left;
    }

    .chat-message.bot .bubble {
      background: #ecf0f1;
      color: #2c3e50;
      text-align: left;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <ul>
      <li><span class="glyphicon glyphicon-edit"></span> New Chats</li>
    </ul>
    <div class="hidden-xs hidden-sm">
     <p class="text-center">Chats History</p>
    </div>
  </div>

  <!-- Toggle Button -->
  <button class="toggle-btn" id="toggleBtn">☰</button>

  <!-- Main Content -->
  <div class="content" id="content">
    <div class="row">
        <div class="col-md-12">
            <div class="chat-top-header">
                <h5>iHRMS Chat Assistant</h5>
            </div>
        </div>
      <!-- Chat -->
      <div class="col-xs-12 col-md-12">
        <div class="chat-box" id="chatBox">
          <div class="chat-message bot"><div class="bubble">Hello! How can I help you today? <span style="font-size:10px; margin-left:5px;"><i class="fa fa-clock-o"></i> 09:00</span></div></div>
        </div>
        <div class="input-group form-group-lg">
          <!-- hidden empCode -->
          <input type="hidden" id="indo_code" value="SAM-EC2003">
          <input type="text" class="form-control" id="query" placeholder="Type a message...">
          <span class="input-group-btn">
            <button class="btn btn-primary btn-lg" id="sendBtn">Send</button>
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script>
    // Sidebar toggle
    $('#toggleBtn').click(function () {
      if ($(window).width() < 768) {
        $('#sidebar').toggleClass('show');
      } else {
        $('#sidebar').toggleClass('collapsed');
        $('#content').toggleClass('expanded');
      }
    });

    // Helper: get current time in HH:MM format
    function getCurrentTime() {
      const now = new Date();
      const hours = now.getHours().toString().padStart(2, '0');
      const minutes = now.getMinutes().toString().padStart(2, '0');
      return `${hours}:${minutes}`;
    }

    // Dynamic chat fetch
    document.getElementById('sendBtn').addEventListener('click', () => {
        const query = document.getElementById('query').value.trim();
        const empCode = document.getElementById('indo_code').value;
        if (!query) return;

        const chatBox = document.getElementById('chatBox');

        // Show user message with time
        chatBox.innerHTML += `<div class="chat-message user">
                                <div class="bubble">You: ${query} <span style="font-size:10px; margin-left:5px;"><i class="fa fa-clock-o"></i> ${getCurrentTime()}</span></div>
                              </div>`;
        chatBox.scrollTop = chatBox.scrollHeight;

        // Clear input
        document.getElementById('query').value = "";

        // Show typing indicator
        const typingIndicator = document.createElement("div");
        typingIndicator.className = "chat-message bot";
        typingIndicator.id = "typingIndicator";
        typingIndicator.innerHTML = `<div class="bubble">Bot is typing...</div>`;
        chatBox.appendChild(typingIndicator);
        chatBox.scrollTop = chatBox.scrollHeight;

        // Fetch from PHP
        fetch('chat_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ indo_code: empCode, query })
        })
        .then(res => res.json())
        .then(data => {
            // Remove typing indicator
            const typingDiv = document.getElementById('typingIndicator');
            if (typingDiv) typingDiv.remove();

            let botReply = "Sorry, I couldn't understand.";

            if (data.status === "success" && data.response) {
                botReply = data.response; // Response from PHP
            } else if (data.status === "error") {
                botReply = "Error: " + data.message;
            }

            chatBox.innerHTML += `<div class="chat-message bot">
                                    <div class="bubble">Bot: ${botReply} <span style="font-size:10px; margin-left:5px;"><i class="fa fa-clock-o"></i> ${getCurrentTime()}</span></div>
                                  </div>`;
            chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(err => {
            const typingDiv = document.getElementById('typingIndicator');
            if (typingDiv) typingDiv.remove();
            chatBox.innerHTML += `<div class="chat-message bot">
                                    <div class="bubble">Bot: Error - ${err.message} <span style="font-size:10px; margin-left:5px;"><i class="fa fa-clock-o"></i> ${getCurrentTime()}</span></div>
                                  </div>`;
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    });

    // Enter key to send
    document.getElementById("query").addEventListener("keydown", function(e) {
      if (e.key === "Enter") {
        e.preventDefault();
        document.getElementById("sendBtn").click();
      }
    });
  </script>
</body>
</html>
