<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Responsive HRMS Chat</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <style>
    body {
      overflow-x: hidden;
      background: #f9fafb;
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      width: 250px;
      background: #ffffff;
      border-right: 1px solid #ddd;
      padding-top: 50px;
      z-index: 1000;
      transition: all 0.3s;
    }
    .sidebar.collapsed { width: 80px; }
    .sidebar ul { list-style: none; margin: 0; padding: 0; }
    .sidebar ul li { padding: 12px 20px; cursor: pointer; }
    .sidebar ul li:hover { background: #e1e2e3; }

    /* Content */
    .content {
      margin-left: 250px;
      transition: margin-left 0.3s;
      padding: 20px;
      min-height: 100vh;
      position: fixed;
    }
    .content.expanded { margin-left: 80px; }

    /* Chat */
    .chat-box {
      background: #fff;
      border-radius: 6px;
      padding: 20px;
      height: calc(100vh - 90px);
      overflow-y: auto;
      margin-bottom: 15px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .chat-message { margin-bottom: 15px; }
    .chat-message.user { text-align: left; }
    .chat-message.bot { text-align: right; }
    .chat-message .bubble {
      display: inline-block;
      padding: 10px 15px;
      border-radius: 18px;
      max-width: 70%;
      word-wrap: break-word;
    }
    .chat-message.user .bubble { background: #3498db; color: #fff; }
    .chat-message.bot .bubble { background: #ecf0f1; color: #2c3e50; }

    /* Toggle button */
    .toggle-btn {
      position: fixed;
      top: 10px;
      left: 10px;
      background: #3498db;
      color: #fff;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      z-index: 1101;
    }

    /* Mobile */
    @media (max-width: 767px) {
      .sidebar { width: 200px; left: -200px; }
      .sidebar.show { left: 0; }
      .content { margin-left: 0; padding-top: 60px; }
      .content.expanded { margin-left: 0; }
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <ul>
      <li><span class="glyphicon glyphicon-comment"></span> Chats</li>
      <li><span class="glyphicon glyphicon-user"></span> Profile</li>
      <li><span class="glyphicon glyphicon-cog"></span> Settings</li>
    </ul>
  </div>

  <!-- Toggle Button -->
  <button class="toggle-btn" id="toggleBtn">☰</button>

  <!-- Main Content -->
  <div class="content" id="content">
    <div class="row">
      <!-- Chat -->
      <div class="col-xs-12 col-md-9">
        <div class="chat-box" id="chatBox">
          <div class="chat-message bot"><div class="bubble">Hello! How can I help you today?</div></div>
        </div>
        <div class="input-group form-group-lg">
          <input type="text" class="form-control" id="query" placeholder="Type a message...">
          <span class="input-group-btn">
            <button class="btn btn-primary btn-lg" id="sendBtn">Send</button>
          </span>
        </div>
      </div>

      <!-- Right Sidebar -->
      <div class="col-xs-12 col-md-3 hidden-xs hidden-sm">
        <div class="panel panel-default">
          <div class="panel-heading">Right Sidebar</div>
          <div class="panel-body">
            Extra content, stats, users list, etc.
          </div>
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

    // Chat
    const chatBox = document.getElementById("chatBox");
    const queryInput = document.getElementById("query");
    const sendBtn = document.getElementById("sendBtn");

    function addMessage(text, sender) {
      const div = document.createElement("div");
      div.className = "chat-message " + sender;
      div.innerHTML = `<div class="bubble">${text}</div>`;
      chatBox.appendChild(div);
      chatBox.scrollTop = chatBox.scrollHeight;
    }

    function botReply(userText) {
      let reply = "";
      if (userText.toLowerCase().includes("name and dob")) {
        reply = "Resource name: Bilal Ahmed Khan, Dob: 10/04/198";
      } else if (userText.toLowerCase().includes("name")) {
        reply = "Resource name: Bilal Ahmed Khan";
      } else {
        reply = "Sorry, I don’t understand.";
      }
      addMessage(reply, "bot");
    }

    sendBtn.addEventListener("click", function() {
      const text = queryInput.value.trim();
      if (text) {
        addMessage("You: " + text, "user");
        queryInput.value = "";
        setTimeout(() => botReply(text), 500);
      }
    });

    queryInput.addEventListener("keydown", function(e) {
      if (e.key === "Enter") {
        e.preventDefault();
        sendBtn.click();
      }
    });
  </script>
</body>
</html>
