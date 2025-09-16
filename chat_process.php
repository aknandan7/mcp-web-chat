<?php
header('Content-Type: application/json');

require_once __DIR__ . '/models/Employee.php';
require_once __DIR__ . '/dynamicQeryBuilder.php'; // Ensure filename matches exactly

$config = require __DIR__ . '/config.php';
$employee = new Employee($config);
$metadata = require __DIR__ . '/metadata.php';
$synonyms = require __DIR__ . '/fieldSynonyms.php'; // include synonyms mapping

$data = json_decode(file_get_contents('php://input'), true);

try {
    // Get indo_code and user query from POST JSON
    $indo_code = $data['indo_code'] ?? null;
    $userQuery = $data['query'] ?? '';

    if (!$indo_code || !$userQuery) {
        throw new Exception('indo_code or query is missing.');
    }

    // Build dynamic query and get AI-like response
    $builderResult = buildDynamicQuery($employee, $metadata, $indo_code, $userQuery, $synonyms);

    // Send JSON response
    echo json_encode([
        'status' => 'success',
        'indo_code' => $indo_code,
        'response' => $builderResult['response']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
