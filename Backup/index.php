<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HRMS Chat</title>
    <!-- ✅ Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <style>
        body {
            font-family: Arial;
            margin: 0;
            padding: 0;
            height: 100vh;
            background-color: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        #chatWrapper {
            width: 100%;
            max-width: 700px;
            display: none; /* Initially hidden, shown with toggle */
        }

        #chatBox {
            border: 1px solid #ccc;
            padding: 10px;
            height: 250px;
            overflow-y: scroll;
            background-color: #fff;
        }

        #query {
            width: 100%;
            padding: 5px;
        }

        #sendBtn {
            padding: 5px 10px;
        }

        .message {
            margin: 5px 0;
        }

        .user {
            color: blue;
        }

        .response {
            color: green;
        }
    </style>
</head>
<body>

    <!-- ✅ Toggle Button -->
    <button class="btn btn-primary mb-3" onclick="toggleChat()">Toggle HRMS Chat</button>

    <!-- ✅ Centered Chat UI inside wrapper -->
    <div id="chatWrapper" class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>HRMS Chat</span>
            <button class="btn-close btn-close-white" onclick="toggleChat()"></button>
        </div>
        <div class="card-body">
            <div id="chatBox" class="mb-3"></div>
            <div class="input-group">
                <input type="hidden" id="indo_code" value="SAM-EC2003">
                <input type="text" id="query" class="form-control" placeholder="Type your query..." />
                <button id="sendBtn" class="btn btn-success">Send</button>
            </div>
        </div>
    </div>

    <!-- ✅ Your original JS logic -->
    <script src="js/chat.js"></script>

    <!-- ✅ Chat toggle logic -->
    <script>
        function toggleChat() {
            const wrapper = document.getElementById('chatWrapper');
            wrapper.style.display = (wrapper.style.display === 'none' || wrapper.style.display === '') ? 'block' : 'none';
        }

        // Function to send message when pressing Enter
        document.getElementById("query").addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                event.preventDefault(); // Prevent default Enter key action (form submit)
                document.getElementById("sendBtn").click(); // Trigger the Send button click
            }
        });
    </script>

    <!-- ✅ Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
