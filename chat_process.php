<?php
header('Content-Type: application/json');

require_once __DIR__ . '/models/Employee.php';
require_once __DIR__ . '/dynamicQeryBuilder.php';
require_once __DIR__ . '/helpers/llm_helper.php'; // LLM helper

$config = require __DIR__ . '/config.php';
$employee = new Employee($config);
$metadata = require __DIR__ . '/metadata.php';
$synonyms = require __DIR__ . '/fieldSynonyms.php';
$apiKey = $config['google_llm_api_key'];

$data = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? ($data['action'] ?? null);

try {
    $indo_code = $data['indo_code'] ?? null;
    if (!$indo_code) {
        throw new Exception('indo_code is missing.');
    }

    // Database connection
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['database']};port={$config['db']['port']}",
        $config['db']['username'],
        $config['db']['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all chat sessions for user
    if ($action === 'get_sessions') {
        $stmt = $pdo->prepare("SELECT id, title, created_at FROM chat_sessions WHERE indo_code=? ORDER BY created_at DESC");
        $stmt->execute([$indo_code]);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status'=>'success','sessions'=>$sessions]);
        exit;
    }

    // Get chat history of a session
    if ($action === 'get_history') {
        $session_id = $_GET['session_id'] ?? null;
        if (!$session_id) throw new Exception('session_id missing');

        $stmt = $pdo->prepare("SELECT message_from,message,created_at FROM chat_history WHERE session_id=? ORDER BY created_at ASC");
        $stmt->execute([$session_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status'=>'success','messages'=>$messages]);
        exit;
    }

    // Create new chat session
    if ($action === 'new_session') {
        $stmt = $pdo->prepare("INSERT INTO chat_sessions (indo_code, title) VALUES (?, 'New Chat')");
        $stmt->execute([$indo_code]);
        $session_id = $pdo->lastInsertId();
        echo json_encode(['status'=>'success','session_id'=>$session_id]);
        exit;
    }

    // Send a message
    if ($action === 'send_message') {
        $userQuery = $data['query'] ?? '';
        $session_id = $data['session_id'] ?? null;

        if (!$userQuery || !$session_id) throw new Exception('query or session_id missing');

        // Store user message
        $stmt = $pdo->prepare("INSERT INTO chat_history (session_id,message_from,message) VALUES (?,?,?)");
        $stmt->execute([$session_id,'user',$userQuery]);

        // Generate bot response
        $responseText = '';
        try {
            $builderResult = buildDynamicQuery($employee, $metadata, $indo_code, $userQuery, $synonyms);
            $dbResponse = $builderResult['response'] ?? '';

            $prompt = "Provide a friendly explanation for this employee data:\n$dbResponse\nUser asked: $userQuery";
            $llmResponse = callLLM($prompt, $apiKey);
            $responseText = $llmResponse ?: $dbResponse;
        } catch (Exception $e) {
            $responseText = callLLM($userQuery, $apiKey) ?: "Sorry, I couldn't understand your query.";
        }

        // Store bot response
        $stmt = $pdo->prepare("INSERT INTO chat_history (session_id,message_from,message) VALUES (?,?,?)");
        $stmt->execute([$session_id,'bot',$responseText]);

        echo json_encode(['status'=>'success','response'=>$responseText]);
        exit;
    }

    throw new Exception('Invalid action');

} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
