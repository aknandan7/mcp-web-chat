<?php
class MCPClient {
    private $host;
    private $port;
    private $socket;

    public function __construct($host, $port) {
        $this->host = $host;
        $this->port = $port;
    }

    public function connect() {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, 5);
        if (!$this->socket) {
            throw new Exception("Could not connect to MCP Server: $errstr ($errno)");
        }
    }

    public function sendQuery($query) {
        if (!$this->socket) $this->connect();

        $json = json_encode($query);
        fwrite($this->socket, $json . "\n");

        $response = fgets($this->socket);
        return json_decode($response, true);
    }

    public function close() {
        if ($this->socket) fclose($this->socket);
    }
}
