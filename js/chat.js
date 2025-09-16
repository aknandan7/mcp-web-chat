document.getElementById('sendBtn').addEventListener('click', () => {
    const query = document.getElementById('query').value;
    const empCode = document.getElementById('indo_code').value;
    if (!query) return;

    const chatBox = document.getElementById('chatBox');

    // Show user message
    chatBox.innerHTML += `<div class="message user">You: ${query}</div>`;

    fetch('chat_process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ indo_code: empCode, query })
    })
    .then(res => res.json())
    .then(data => {
        let botReply = "Sorry, I couldn't understand.";

        if (data.status === "success" && data.response) {
            botReply = data.response; // Use response from PHP
        } else if (data.status === "error") {
            botReply = "Error: " + data.message;
        }

        chatBox.innerHTML += `<div class="message response">Bot: ${botReply}</div>`;
        chatBox.scrollTop = chatBox.scrollHeight;
    })
    .catch(err => {
        chatBox.innerHTML += `<div class="message response">Bot: Error - ${err.message}</div>`;
    });
});
