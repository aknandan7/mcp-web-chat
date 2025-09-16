<?php
require_once __DIR__ . '/../clients/MCPClient.php';

class Employee {
    private $mcp;

    public function __construct($config) {
        $this->mcp = new MCPClient($config['mcp_server_host'], $config['mcp_server_port']);
    }

    public function query($indo_code, $naturalQuery) {
        // Send indo_code instead of emp_id for secure filtering
        $query = [
            'indo_code' => $indo_code,
            'nl_query'   => $naturalQuery
        ];
        return $this->mcp->sendQuery($query);
    }
}
