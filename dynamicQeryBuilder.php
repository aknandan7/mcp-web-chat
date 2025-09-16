<?php
require_once __DIR__ . '/models/Employee.php';

/**
 * Build dynamic query based on natural language and synonyms
 * @param Employee $employee
 * @param array $metadata - table => [columns]
 * @param string $indo_code
 * @param string $userQuery
 * @param array $synonyms - column => [synonyms]
 * @return array ['response' => 'formatted string']
 */
function buildDynamicQuery($employee, $metadata, $indo_code, $userQuery, $synonyms = []) {
    $userQueryLower = strtolower($userQuery);
    $requestedFields = [];

    // Step 1: detect fields using synonyms
    foreach ($metadata as $table => $columns) {
        foreach ($columns as $col) {
            $allNames = [$col];
            if (isset($synonyms[$col])) {
                $allNames = array_merge($allNames, $synonyms[$col]);
            }

            foreach ($allNames as $name) {
                $nameNormalized = strtolower(str_replace('_', ' ', $name));
                if (strpos($userQueryLower, $nameNormalized) !== false) {
                    $requestedFields[$table][] = $col;
                    break;
                }
            }
        }
    }

    if (empty($requestedFields)) {
        throw new Exception("Couldn't detect any field from your query.");
    }

    // Step 2: run queries for detected tables
    $allResults = [];
    foreach ($requestedFields as $table => $cols) {
        $colsStr = implode(', ', $cols);
        $query = "SELECT $colsStr FROM $table WHERE indo_code='$indo_code'";
        $result = $employee->query($indo_code, $query);

        if (is_array($result) && count($result) > 0) {
            $allResults[$table] = $result[0]; // first row per table
        }
    }

    // Step 3: format response dynamically
    $botReply = [];
    foreach ($allResults as $table => $row) {
        foreach ($row as $key => $value) {
            if (strpos($key, 'dob') !== false || strpos($key, 'date') !== false) {
                $value = date('d/m/Y', strtotime($value));
            }
            $botReply[] = ucfirst(str_replace('_', ' ', $key)) . ": $value";
        }
    }

    if (empty($botReply)) $botReply[] = "No data found.";

    return ['response' => implode(', ', $botReply)];
}
