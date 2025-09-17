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

try {
    $indo_code = $data['indo_code'] ?? null;
    $userQuery = $data['query'] ?? '';

    if (!$indo_code || !$userQuery) {
        throw new Exception('indo_code or query is missing.');
    }

    $responseText = "";

    try {
        $builderResult = buildDynamicQuery($employee, $metadata, $indo_code, $userQuery, $synonyms);
        $dbResponse = $builderResult['response'];

        $prompt = "Provide a friendly explanation for this employee data:\n$dbResponse\nUser asked: $userQuery";
        $llmResponse = callLLM($prompt, $apiKey);

        $responseText = $llmResponse ?: $dbResponse;

    } catch (Exception $e) {
        $responseText = callLLM($userQuery, $apiKey) ?: "Sorry, I couldn't understand your query.";
    }

    echo json_encode([
        'status' => 'success',
        'indo_code' => $indo_code,
        'response' => $responseText
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
