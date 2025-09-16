<?php
header('Content-Type: application/json');

require_once __DIR__ . '/models/Employee.php';
require_once __DIR__ . '/dynamicQeryBuilder.php';

$config = require __DIR__ . '/config.php';
$employee = new Employee($config);
$metadata = require __DIR__ . '/metadata.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    $indo_code = $data['indo_code'] ?? null;
    $userQuery = $data['query'] ?? '';

    if (!$indo_code || !$userQuery) {
        throw new Exception('indo_code or query is missing.');
    }

    // Build dynamic query and get response
    $builderResult = buildDynamicQuery($employee, $metadata, $indo_code, $userQuery);

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
