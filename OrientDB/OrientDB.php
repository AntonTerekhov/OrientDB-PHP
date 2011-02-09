<?php

require 'OrientDBSocket.php';
require 'OrientDBCommandAbstract.php';
require 'OrientDBCommandConfigList.php';
require 'OrientDBCommandConnect.php';
require 'OrientDBCommandCount.php';
require 'OrientDBCommandOpenDB.php';
require 'OrientDBCommandRecordCreate.php';
require 'OrientDBCommandRecordDelete.php';
require 'OrientDBCommandRecordLoad.php';
require 'OrientDBCommandRecordUpdate.php';

require 'OrientDBRecord.php';


class OrientDB
{

    private $host, $port;

    public $socket;

    private $debug = false;

    /**
     * Client protocol version
     * @var int
     */
    public $clientVersion = 2;

    /**
     * Server's protocol version.
     * @var int
     */
    public $protocolVersion = null;

    protected $connected = false;

    protected $DBOpen = false;

    const RECORD_TYPE_BYTES = 'b';
    const RECORD_TYPE_COLUMN = 'c';
    const RECORD_TYPE_DOCUMENT = 'd';
    const RECORD_TYPE_FLAT = 'f';


    public function __construct($host, $port, $timeout = 30)
    {
        $this->host = $host;
        $this->port = $port;
        $this->socket = new OrientDBSocket($host, $port, $timeout);
    }

    public function __destruct()
    {
        unset($this->socket);
    }

    public function isConnected()
    {
        return $this->connected;
    }

    public function isDBOpen()
    {
        return $this->DBOpen;
    }

    public function __call($name, $arguments)
    {
        $className = 'OrientDBCommand' . $name;
        if (class_exists($className)) {
            $command = new $className($this);
            $this->canExecute($command);
            call_user_func_array(array(
                            $command,
                            'setAttribs'), $arguments);

            $command->prepare();
            $data = $command->execute();
            // @TODO Read protocol version if first called methor get error from server
            if (is_null($this->protocolVersion)) {
                $this->protocolVersion = $command->getProtocolVersion();
                if ($this->protocolVersion != $this->clientVersion) {
                    throw new OrientDBException('Binary protocol is uncompatible with the Server connected: client=' . $this->clientVersion . ', server=' . $this->protocolVersion);
                }
                if ($command->type == OrientDBCommandAbstract::CONNECT || $command->type == OrientDBCommandAbstract::DB_OPEN) {
                    $this->connected = true;
                }
                if ($command->type == OrientDBCommandAbstract::DB_OPEN) {
                    $this->DBOpen = true;
                }
                if ($command->type == OrientDBCommandAbstract::DB_CLOSE) {
                    $this->DBOpen = false;
                }
            }
            return $data;
        } else {
            throw new OrientDBException('Command ' . $className . ' currenty not implemented');
        }

    }

    protected function canExecute($command)
    {
        $require_connect = array(
                        OrientDBCommandAbstract::SHUTDOWN,
                        OrientDBCommandAbstract::DB_CREATE,
                        OrientDBCommandAbstract::DB_EXIST,
                        OrientDBCommandAbstract::DB_DELETE,
                        OrientDBCommandAbstract::CONFIG_GET,
                        OrientDBCommandAbstract::CONFIG_SET,
                        OrientDBCommandAbstract::CONFIG_LIST);
        $require_DB = array(
                        OrientDBCommandAbstract::DB_CLOSE,
                        //OrientDBCommandAbstract::DATACLUSTER_ADD,
                        //OrientDBCommandAbstract::DATACLUSTER_REMOVE,
                        //OrientDBCommandAbstract::DATACLUSTER_COUNT,
                        //OrientDBCommandAbstract::DATACLUSTER_DATARANGE,
                        //OrientDBCommandAbstract::DATASEGMENT_ADD,
                        //OrientDBCommandAbstract::DATASEGMENT_REMOVE,
                        OrientDBCommandAbstract::RECORD_LOAD,
                        OrientDBCommandAbstract::RECORD_CREATE,
                        OrientDBCommandAbstract::RECORD_UPDATE,
                        OrientDBCommandAbstract::RECORD_DELETE,
                        OrientDBCommandAbstract::COUNT,
                        OrientDBCommandAbstract::COMMAND,
                        OrientDBCommandAbstract::DICTIONARY_LOOKUP,
                        OrientDBCommandAbstract::DICTIONARY_PUT,
                        OrientDBCommandAbstract::DICTIONARY_REMOVE,
                        OrientDBCommandAbstract::DICTIONARY_SIZE,
                        OrientDBCommandAbstract::DICTIONARY_KEYS,
                        //OrientDBCommandAbstract::TX_COMMIT
                        );
        if (in_array($command->type, $require_connect) && !$this->isConnected()) {
            throw new OrientDBException('Not connected to server');
        }
        if (in_array($command->type, $require_DB) && !$this->isDBOpen()) {
            throw new OrientDBException('Database not open');
        }
    }

    public function setDebug($debug) {
    	$this->debug = $debug;
    }

    public function isDebug() {
    	return $this->debug;
    }
}

class OrientDBException extends Exception
{
}

class OrientDBConnectException extends OrientDBException
{
}