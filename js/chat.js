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

        if (data.status === "success" && data.data && data.data.length > 0) {
            const emp = data.data[0];
            botReply = `Your name is ${emp.resource_name}, your designation is ${emp.designation}, gender is ${emp.gender}, and DOB is ${new Date(emp.dob).toLocaleDateString()}.`;
        } else if (data.status === "error") {
            botReply = "Error: " + data.message;
        }

        // ✅ Only show clean text (not full JSON)
        chatBox.innerHTML += `<div class="message response">Bot: ${botReply}</div>`;

        chatBox.scrollTop = chatBox.scrollHeight;
    })
    .catch(err => {
        chatBox.innerHTML += `<div class="message response">Bot: Error - ${err.message}</div>`;
    });
});
