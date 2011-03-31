<?php

class OrientDBSocket
{

    private $socket;

    private $bufferLen;

    public $debug = false;

    public function __construct($host, $port, $timeout = 30, $bufferLen = 16384)
    {
        $socket = $this->socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if ($socket === false) {
            throw new Exception("Socket error #{$errno}: {$errstr}");
        }

        stream_set_blocking($socket, 1);
        stream_set_timeout($socket, 1);

        $this->bufferLen = $bufferLen;
    }

    public function __destruct()
    {
        fclose($this->socket);
    }

    public function read($length = null)
    {
        $data = fread($this->socket, $length === null ? $this->bufferLen : $length);
        if ($this->debug) {
            hex_dump($data);
        }
        return $data;
    }

    public function send($data)
    {
        fwrite($this->socket, $data);
        if ($this->debug) {
            hex_dump($data);
        }
    }
}
