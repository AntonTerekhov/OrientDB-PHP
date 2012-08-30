<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * Socket class in OrientDB-PHP library.
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Main
 */
class OrientDBSocket
{
    /**
     * Socket object
     * @var resource
     */
    private $socket;

    /**
     * Default buffer length
     * @var int
     */
    private $bufferLen;

    /**
     * Control debug output
     * @var bool
     */
    public $debug = false;

    /**
     * Create new instance
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @param int $bufferLen
     * @throws OrientDBConnectException
     */
    public function __construct($host, $port, $timeout = 30, $bufferLen = 16384)
    {
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if ($this->socket === false) {
            throw new OrientDBConnectException("Socket error #{$errno}: {$errstr}");
        }

        stream_set_blocking($this->socket, 1);
        stream_set_timeout($this->socket, 1);

        $this->bufferLen = $bufferLen;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        fclose($this->socket);
    }

    /**
     * Read bytes from socket
     * @param int $length Bytes to read
     * @return string
     */
    public function read($length = null)
    {
        $data = fread($this->socket, $length === null ? $this->bufferLen : $length);
        if ($this->debug) {
            OrientDBHelpers::hexDump($data);
        }
        return $data;
    }

    /**
     * Send data to socket
     * @param string $data
     */
    public function send($data)
    {
        fwrite($this->socket, $data);
        if ($this->debug) {
            OrientDBHelpers::hexDump($data);
        }
    }

    /**
     * Check if socket still a valid resource
     * @return bool
     */
    public function isValid()
    {
        return is_resource($this->socket);
    }
}