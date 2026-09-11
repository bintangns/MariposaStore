<?php

namespace App\Services;

class RconClient
{
    private $socket;
    private string $host;
    private int $port;
    private string $password;
    private int $requestId = 1;

    const SERVERDATA_AUTH           = 3;
    const SERVERDATA_EXECCOMMAND    = 2;
    const SERVERDATA_RESPONSE_VALUE = 0;

    public function __construct(string $host, int $port, string $password)
    {
        $this->host     = $host;
        $this->port     = $port;
        $this->password = $password;
    }

    public function connect(): void
    {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, 5);

        if (!$this->socket) {
            throw new \Exception("RCON connection failed: {$errstr} ({$errno})");
        }

        stream_set_timeout($this->socket, 5);

        // Authenticate
        $this->send(self::SERVERDATA_AUTH, $this->password);
        $response = $this->receive();

        if ($response['id'] === -1) {
            throw new \Exception("RCON authentication failed — wrong password");
        }
    }

    public function sendCommand(string $command): string
    {
        $this->send(self::SERVERDATA_EXECCOMMAND, $command);
        $response = $this->receive();
        return $response['body'] ?? '';
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }

    private function send(int $type, string $body): void
    {
        $id      = $this->requestId++;
        $payload = pack('VV', $id, $type) . $body . "\x00\x00";
        $packet  = pack('V', strlen($payload)) . $payload;
        fwrite($this->socket, $packet);
    }

    private function receive(): array
    {
        $sizeData = fread($this->socket, 4);
        if (strlen($sizeData) < 4) {
            return ['id' => -1, 'type' => -1, 'body' => ''];
        }

        $size = unpack('V', $sizeData)[1];
        $data = fread($this->socket, $size);

        $id   = unpack('V', substr($data, 0, 4))[1];
        $type = unpack('V', substr($data, 4, 4))[1];
        $body = substr($data, 8, -2); // trim null bytes

        return ['id' => $id, 'type' => $type, 'body' => $body];
    }
}
