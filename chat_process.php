<?php
header('Content-Type: application/json');

// Include config and Employee model
$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/models/Employee.php';

// Create Employee instance
$employee = new Employee($config);

try {
    // Hardcoded indo_code for testing
    $indo_code = "SAM-EC2003";

    // Hardcoded query for testing
    $query = "SELECT resource_name,gender,dob,designation FROM emp_personal_info WHERE indo_code='$indo_code'";

    // Send query via MCPClient
    $result = $employee->query($indo_code, $query);

    // Ensure result is always an array
    if (!is_array($result)) {
        $result = ['result' => $result];
    }

    // Return JSON response
    echo json_encode([
        'status'    => 'success',
        'indo_code' => $indo_code,
        'query'     => $query,
        'data'      => $result
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
