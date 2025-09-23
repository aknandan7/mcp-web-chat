<?php
require_once __DIR__ . '/models/Employee.php';

// Function to log queries
function logQuery($userQuery, $builtQueries) {
    $logFile = __DIR__ . '/log.txt';
    $time = date('Y-m-d H:i:s');

    $logData = "[$time] User Query: $userQuery" . PHP_EOL;
    foreach ($builtQueries as $q) {
        $logData .= "[$time] Built Query: $q" . PHP_EOL;
    }
    $logData .= PHP_EOL;

    file_put_contents($logFile, $logData, FILE_APPEND);
}

function buildDynamicQuery($employee, $metadata, $indo_code, $userQuery, $synonyms = []) {
    $userQueryLower = strtolower($userQuery);
    $requestedFields = [];
    $builtQueries = []; // Array to store all generated SQL queries

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
        $query = "";

        // Special handling for leave quota
        if ($table === 'emp_leavequota_info' && in_array('lt_id', $cols)) {
            $indo_code_safe = addslashes($indo_code);
            $query = "SELECT lq.*, mlt.name AS leave_type_name, mlt.short_name 
                      FROM emp_leavequota_info lq
                      LEFT JOIN master_leavetype_info mlt ON lq.lt_id = mlt.lt_id
                      WHERE lq.indo_code='$indo_code_safe'";
        } else {
            $query = "SELECT $colsStr FROM $table WHERE indo_code='" . addslashes($indo_code) . "'";
        }

        // Save query for logging
        $builtQueries[] = $query;

        $result = $employee->query($indo_code, $query);

        // Ensure $result is always an array of rows
        if (is_array($result)) {
            $allResults[$table] = $result;
        } elseif (is_string($result) && !empty($result)) {
            $allResults[$table] = [[$colsStr => $result]];
        }
    }

    // Step 3: format response dynamically
    $botReply = [];

    foreach ($allResults as $table => $rows) {
        if (!is_array($rows)) continue;

        if ($table === 'emp_leavequota_info') {
            $botReply[] = "**Leave Balances:**";
            $list = [];
            $explanation = [];

            foreach ($rows as $index => $row) {
                if (!is_array($row)) continue;

                $leaveName = $row['leave_type_name'] ?? $row['short_name'] ?? $row['lt_id'];
                $leaveDays = $row['leaves'] ?? 0;

                $list[] = ($index + 1) . ". $leaveName: $leaveDays";

                if ($leaveDays > 0) {
                    $explanation[] = "Period " . ($index + 1) . ": Took $leaveDays days of $leaveName.";
                } else {
                    $explanation[] = "Period " . ($index + 1) . ": Took no $leaveName.";
                }
            }

            $botReply[] = implode("\n", $list);
            if (!empty($explanation)) {
                $botReply[] = "\n**Explanation:**\n" . implode("\n", $explanation);
            }

        } else {
            foreach ($rows as $row) {
                if (!is_array($row)) continue;

                foreach ($row as $key => $value) {
                    if ($value === null || $value === '') continue;

                    // Format dates nicely
                    if (strpos($key, 'dob') !== false || strpos($key, 'date') !== false) {
                        $value = date('d/m/Y', strtotime($value));
                    }

                    $botReply[] = ucfirst(str_replace('_', ' ', $key)) . ": $value";
                }
            }
        }
    }

    if (empty($botReply)) {
        $botReply[] = "No data found.";
    }

    // Log the queries
    logQuery($userQuery, $builtQueries);

    return [
        'response' => implode("\n\n", $botReply),
        'userQuery' => $userQuery,
        'builtQueries' => $builtQueries
    ];
}
