<?php
header("Content-Type: application/json");
$config = require __DIR__ . "/config.php";

$conn = new mysqli(
    $config['db']['host'],
    $config['db']['username'],
    $config['db']['password'],
    $config['db']['database'],
    $config['db']['port']
);
if ($conn->connect_error) {
    echo json_encode(["status"=>"error","message"=>"DB Connection failed: ".$conn->connect_error]);
    exit;
}

$action = $_GET['action'] ?? null;
$input = json_decode(file_get_contents("php://input"), true);

switch($action) {

    case 'get_sessions':
        $indo_code = $input['indo_code'] ?? null;
        if(!$indo_code) { 
            echo json_encode(["status"=>"error","message"=>"indo_code missing"]); 
            exit; 
        }

        $stmt = $conn->prepare("SELECT id, title, created_at FROM chat_sessions WHERE indo_code=? ORDER BY created_at DESC");
        $stmt->bind_param("s", $indo_code);
        $stmt->execute();
        $res = $stmt->get_result();
        $sessions = $res->fetch_all(MYSQLI_ASSOC);

        echo json_encode(["status"=>"success","sessions"=>$sessions]);
        break;

    case 'get_history':
        $sessionId = $input['session_id'] ?? $_GET['session_id'] ?? null;

        if(!$sessionId) { 
            echo json_encode(["status"=>"error","message"=>"session_id missing"]); 
            exit; 
        }

        // Validate session
        $stmt = $conn->prepare("SELECT id, indo_code, title, created_at FROM chat_sessions WHERE id=?");
        $stmt->bind_param("i", $sessionId);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->num_rows === 0){ 
            echo json_encode(["status"=>"error","message"=>"Session not found"]); 
            exit; 
        }
        $session = $res->fetch_assoc();

        // Fetch chat history
        $stmt = $conn->prepare("SELECT id, session_id, message_from, message, message_type, status, created_at FROM chat_history WHERE session_id=? ORDER BY created_at ASC");
        $stmt->bind_param("i", $sessionId);
        $stmt->execute();
        $res = $stmt->get_result();
        $history = [];
        while($row = $res->fetch_assoc()){
            $history[] = [
                "id"=>$row['id'],
                "sender"=>$row['message_from'],
                "message"=>$row['message'],
                "message_type"=>$row['message_type'],
                "status"=>$row['status'],
                "created_at"=>$row['created_at']
            ];
        }

        echo json_encode(["status"=>"success","session"=>$session,"history"=>$history]);
        break;

    default:
        echo json_encode(["status"=>"error","message"=>"Invalid action"]);
}

$conn->close();
