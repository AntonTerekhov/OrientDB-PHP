<?php

class OrientDBSocket
{

    private $socket;

    private $bufferLen;

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
//    	$this->debug($data);
        hex_dump($data);
    	return $data;
    }

    public function send($data)
    {
        fwrite($this->socket, $data);
//        OrientDBSocket::debug($data);
        hex_dump($data);
    }

    static function debug($string)
    {
        $output = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $ord = ord($string[$i]);
            if ($ord < 0x20 || $ord > 0x7f) {
                $output .= sprintf(' 0x%02s ', $ord);
            } else {
                $output .= $string[$i];
            }
        }
        echo '(' . (strlen($string) + 1) . ') ' . $output . PHP_EOL;
    }
}
