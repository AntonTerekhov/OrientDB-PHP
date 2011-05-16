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
     * @throws Exception
     */
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

    /**
     * Destructor
     */
    public function __destruct()
    {
        fclose($this->socket);
    }

    /**
     * Read bytes ftom socket
     * @param int $length Bytes to read
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
}