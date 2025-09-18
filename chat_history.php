<?php
header("Content-Type: application/json");

// Load config
$config = require __DIR__ . "/config.php";

// Database connection
$dbConfig = $config['db'];
$conn = new mysqli(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['database'],
    $dbConfig['port']
);

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "DB Connection failed: " . $conn->connect_error
    ]);
    exit;
}

// Get request payload (JSON)
$input = json_decode(file_get_contents("php://input"), true);
$sessionId = $input['session_id'] ?? null;

if (!$sessionId) {
    echo json_encode([
        "status" => "error",
        "message" => "Session ID missing"
    ]);
    exit;
}

// Validate session
$sessionQuery = $conn->prepare("
    SELECT id, indo_code, title, created_at 
    FROM chat_sessions 
    WHERE id = ?
");
$sessionQuery->bind_param("i", $sessionId);
$sessionQuery->execute();
$sessionResult = $sessionQuery->get_result();

if ($sessionResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Session not found"
    ]);
    exit;
}

$session = $sessionResult->fetch_assoc();

// Fetch chat history
$historyQuery = $conn->prepare("
    SELECT id, session_id, message_from, message, message_type, status, created_at 
    FROM chat_history 
    WHERE session_id = ? 
    ORDER BY created_at ASC
");
$historyQuery->bind_param("i", $sessionId);
$historyQuery->execute();
$historyResult = $historyQuery->get_result();

$history = [];
while ($row = $historyResult->fetch_assoc()) {
    $history[] = [
        "id"          => $row['id'],
        "sender"      => $row['message_from'], // user | bot
        "message"     => $row['message'],
        "message_type"=> $row['message_type'],
        "status"      => $row['status'],
        "created_at"  => $row['created_at']
    ];
}

// Response
echo json_encode([
    "status"  => "success",
    "session" => $session,
    "history" => $history
], JSON_PRETTY_PRINT);

$conn->close();
