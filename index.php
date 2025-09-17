<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Responsive HRMS Chat</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/custom.css">
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
          <div class="chat-message bot"><div class="bubble">Hello! How can I help you today?</div></div>
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

    // Dynamic chat fetch
    document.getElementById('sendBtn').addEventListener('click', () => {
        const query = document.getElementById('query').value.trim();
        const empCode = document.getElementById('indo_code').value;
        if (!query) return;

        const chatBox = document.getElementById('chatBox');

        // Show user message
        chatBox.innerHTML += `<div class="chat-message user"><div class="bubble">You: ${query}</div></div>`;

        // Clear input
        document.getElementById('query').value = "";

        // Fetch from PHP
        fetch('chat_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ indo_code: empCode, query })
        })
        .then(res => res.json())
        .then(data => {
            let botReply = "Sorry, I couldn't understand.";

            if (data.status === "success" && data.response) {
                botReply = data.response; // Response from PHP
            } else if (data.status === "error") {
                botReply = "Error: " + data.message;
            }

            chatBox.innerHTML += `<div class="chat-message bot"><div class="bubble">Bot: ${botReply}</div></div>`;
            chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(err => {
            chatBox.innerHTML += `<div class="chat-message bot"><div class="bubble">Bot: Error - ${err.message}</div></div>`;
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
