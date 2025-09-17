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
          <input type="text" class="form-control" id="query" placeholder="Type a message...">
          <span class="input-group-btn">
            <button class="btn btn-primary btn-lg" id="sendBtn">Send</button>
          </span>
        </div>
      </div>

      <!-- Right Sidebar -->
      <!-- <div class="col-xs-12 col-md-3 hidden-xs hidden-sm hidden-lg">
        <div class="panel panel-default">
          <div class="panel-heading">Right Sidebar</div>
          <div class="panel-body">
            Extra content, stats, users list, etc.
          </div>
        </div>
      </div> -->
      <!-- ================ -->
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
