<?php
#########################################################
# Dynamic Query Builder Function
#########################################################

function buildDynamicQuery($employee, $metadata, $indo_code, $userQuery) {
    $userQueryLower = strtolower($userQuery);
    $requestedFields = [];

    // Step 1: Detect requested columns from user query
    foreach ($metadata as $table => $columns) {
        foreach ($columns as $col) {
            $colNormalized = str_replace('_', ' ', strtolower($col));
            if (strpos($userQueryLower, $colNormalized) !== false) {
                $requestedFields[$table][] = $col;
            }
        }
    }

    if (empty($requestedFields)) {
        throw new Exception('Could not detect any fields from query.');
    }

    // Step 2: Execute queries per table
    $allResults = [];
    foreach ($requestedFields as $table => $cols) {
        $colsStr = implode(', ', $cols);
        $query = "SELECT $colsStr FROM $table WHERE indo_code='$indo_code'";
        $result = $employee->query($indo_code, $query);
        if (is_array($result) && count($result) > 0) {
            $allResults[$table] = $result[0]; // Take first row per table
        }
    }

    // Step 3: Format response dynamically
    $botReply = [];
    foreach ($allResults as $table => $row) {
        foreach ($row as $key => $value) {
            if (strpos($key, 'dob') !== false || strpos($key, 'date') !== false) {
                $value = date('d/m/Y', strtotime($value));
            }
            $botReply[] = ucfirst(str_replace('_', ' ', $key)) . ": $value";
        }
    }

    if (empty($botReply)) {
        $botReply[] = "No data found.";
    }

    return ['response' => implode(', ', $botReply)];
}
