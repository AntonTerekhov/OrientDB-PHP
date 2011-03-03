<?php

require 'OrientDBSocket.php';
require 'OrientDBCommandAbstract.php';
require 'OrientDBCommandCommand.php';
require 'OrientDBCommandConfigGet.php';
require 'OrientDBCommandConfigList.php';
require 'OrientDBCommandConfigSet.php';
require 'OrientDBCommandConnect.php';
require 'OrientDBCommandCount.php';
require 'OrientDBCommandDataclusterAdd.php';
require 'OrientDBCommandDataclusterCount.php';
require 'OrientDBCommandDataclusterDatarange.php';
require 'OrientDBCommandDataclusterRemove.php';
require 'OrientDBCommandDBClose.php';
require 'OrientDBCommandDBCreate.php';
require 'OrientDBCommandDBExists.php';
require 'OrientDBCommandDBOpen.php';
require 'OrientDBCommandDictionaryKeys.php';
require 'OrientDBCommandDictionaryLookup.php';
require 'OrientDBCommandDictionaryPut.php';
require 'OrientDBCommandDictionaryRemove.php';
require 'OrientDBCommandDictionarySize.php';
require 'OrientDBCommandRecordCreate.php';
require 'OrientDBCommandRecordDelete.php';
require 'OrientDBCommandRecordLoad.php';
require 'OrientDBCommandRecordUpdate.php';
require 'OrientDBCommandShutdown.php';

require 'OrientDBRecord.php';

require 'helpers/hex_dump.php';


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

    protected $active = true;

    const RECORD_TYPE_BYTES = 'b';
    const RECORD_TYPE_COLUMN = 'c';
    const RECORD_TYPE_DOCUMENT = 'd';
    const RECORD_TYPE_FLAT = 'f';

    public static $recordTypes = array(self::RECORD_TYPE_BYTES, self::RECORD_TYPE_COLUMN, self::RECORD_TYPE_DOCUMENT, self::RECORD_TYPE_FLAT);

    const DB_TYPE_MEMORY = 'memory';
    const DB_TYPE_LOCAL = 'local';

    const COMMAND_MODE_SYNC = 's';
    const COMMAND_MODE_ASYNC = 'a';

    const DATACLUSTER_TYPE_LOGICAL = 'LOGICAL';
    const DATACLUSTER_TYPE_PHYSICAL = 'PHYSICAL';
    const DATACLUSTER_TYPE_MEMORY = 'MEMORY';

    public static $clusterTypes = array(self::DATACLUSTER_TYPE_LOGICAL, self::DATACLUSTER_TYPE_PHYSICAL, self::DATACLUSTER_TYPE_MEMORY);

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

                if ($command->type == OrientDBCommandAbstract::CONNECT) {
                    $this->connected = true;
                }
                if ($command->type == OrientDBCommandAbstract::DB_OPEN) {
                    $this->DBOpen = true;
                }
                if ($command->type == OrientDBCommandAbstract::DB_CLOSE) {
                    $this->DBOpen = false;
                    $this->active = false;
                    $this->socket = null;
                }
            return $data;
        } else {
            throw new OrientDBWrongCommandException('Command ' . $className . ' currenty not implemented');
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
                        OrientDBCommandAbstract::DATACLUSTER_ADD,
                        OrientDBCommandAbstract::DATACLUSTER_REMOVE,
                        OrientDBCommandAbstract::DATACLUSTER_COUNT,
                        OrientDBCommandAbstract::DATACLUSTER_DATARANGE,
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
        if (!$this->active) {
        	throw new OrientDBWrongCommandException('DBClose was executed. No interaction posibble.');
        }
        if (in_array($command->type, $require_connect) && !$this->isConnected()) {
            throw new OrientDBWrongCommandException('Not connected to server');
        }
        if (in_array($command->type, $require_DB) && !$this->isDBOpen()) {
            throw new OrientDBWrongCommandException('Database not open');
        }
    }

    public function setDebug($debug) {
    	$this->debug = $debug;
    }

    public function isDebug() {
    	return $this->debug;
    }

    public function setProtocolVersion($version) {
    	$this->protocolVersion = $version;
        if ($this->protocolVersion != $this->clientVersion) {
            throw new OrientDBException('Binary protocol is uncompatible with the Server connected: client=' . $this->clientVersion . ', server=' . $this->protocolVersion);
        }
    }
}

class OrientDBException extends Exception
{
}

class OrientDBConnectException extends OrientDBException
{
}

class OrientDBWrongCommandException extends OrientDBException
{
}

class OrientDBWrongParamsException extends OrientDBException
{
}
