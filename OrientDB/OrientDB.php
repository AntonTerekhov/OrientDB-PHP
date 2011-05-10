<?php

class OrientDB
{

    private $host, $port;

    public $socket;

    private $debug = false;

    /**
     * Client protocol version
     * @var int
     */
    public $clientVersion = 5;

    /**
     * Server's protocol version.
     * @var int
     */
    public $protocolVersion = null;

    protected $connected = false;

    protected $DBOpen = false;

    protected $active = true;

    /**
     * SessionID returned from OrientDB, used to further identify queries to server
     * @var int
     */
    private $sessionIDServer;

    /**
     * SessionID returned from OrientDB, used to further identify queries to DB
     * @var int
     */
    private $sessionIDDB;

    const RECORD_TYPE_BYTES = 'b';

    const RECORD_TYPE_DOCUMENT = 'd';

    const RECORD_TYPE_FLAT = 'f';

    public static $recordTypes = array(
                    self::RECORD_TYPE_BYTES,
                    self::RECORD_TYPE_DOCUMENT,
                    self::RECORD_TYPE_FLAT);

    const DB_TYPE_MEMORY = 'memory';

    const DB_TYPE_LOCAL = 'local';

    const COMMAND_QUERY = 1;

    const COMMAND_SELECT_SYNC = 2;

    const COMMAND_SELECT_ASYNC = 3;

    const DATACLUSTER_TYPE_LOGICAL = 'LOGICAL';

    const DATACLUSTER_TYPE_PHYSICAL = 'PHYSICAL';

    const DATACLUSTER_TYPE_MEMORY = 'MEMORY';

    public static $clusterTypes = array(
                    self::DATACLUSTER_TYPE_LOGICAL,
                    self::DATACLUSTER_TYPE_PHYSICAL,
                    self::DATACLUSTER_TYPE_MEMORY);

    public $cachedRecords = array();

    private static $requireConnect = array(
                        OrientDBCommandAbstract::SHUTDOWN,
                        OrientDBCommandAbstract::DB_CREATE,
                        OrientDBCommandAbstract::DB_DELETE,
                        OrientDBCommandAbstract::DB_EXIST,
                        OrientDBCommandAbstract::CONFIG_GET,
                        OrientDBCommandAbstract::CONFIG_SET,
                        OrientDBCommandAbstract::CONFIG_LIST);

    private static $requireDBOpen = array(
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
                        OrientDBCommandAbstract::INDEX_LOOKUP,
                        OrientDBCommandAbstract::INDEX_PUT,
                        OrientDBCommandAbstract::INDEX_REMOVE,
                        OrientDBCommandAbstract::INDEX_SIZE,
                        OrientDBCommandAbstract::INDEX_KEYS,
                        OrientDBCommandAbstract::TX_COMMIT);

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
        $className = 'OrientDBCommand' . ucfirst($name);
        if (class_exists($className)) {
            $command = new $className($this);
            $this->canExecute($command);
            call_user_func_array(array(
                            $command,
                            'setAttribs'), $arguments);

            $command->prepare();
            $data = $command->execute();

            if ($command->opType == OrientDBCommandAbstract::CONNECT) {
                $this->connected = true;
            }
            if ($command->opType == OrientDBCommandAbstract::DB_OPEN) {
                $this->DBOpen = true;
            }
            if ($command->opType == OrientDBCommandAbstract::DB_CLOSE) {
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

        if (!$this->active) {
            throw new OrientDBWrongCommandException('DBClose was executed. No interaction posibble.');
        }
        if (in_array($command->opType, $this->getCommandsRequiresConnect()) && !$this->isConnected()) {
            throw new OrientDBWrongCommandException('Not connected to server');
        }
        if (in_array($command->opType, $this->getCommandsRequiresDBOpen()) && !$this->isDBOpen()) {
            throw new OrientDBWrongCommandException('Database not open');
        }
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    public function isDebug()
    {
        return $this->debug;
    }

    public function setProtocolVersion($version)
    {
        $this->protocolVersion = $version;
        if ($this->protocolVersion != $this->clientVersion) {
            throw new OrientDBException('Binary protocol is uncompatible with the Server connected: client=' . $this->clientVersion . ', server=' . $this->protocolVersion);
        }
    }

    /**
     * Returns list of commands require to previously run connect()
     * @return array
     */
    public function getCommandsRequiresConnect()
    {
        return self::$requireConnect;
    }

    /**
     * Returns list of commands require to previously run DBOpen()
     * @return array
     */
    public function getCommandsRequiresDBOpen()
    {
        return self::$requireDBOpen;
    }

    /**
     * Set sessionID for use with server queries
     * @param int $sessionID
     */
    public function setSessionIDServer($sessionID)
    {
        $this->sessionIDServer = $sessionID;
    }

    /**
     * Set sessionID for use with DB queries
     * @param int $sessionID
     */
    public function setSessionIDDB($sessionID)
    {
        $this->sessionIDDB = $sessionID;
    }

    /**
     * Return sessionID for server queries
     * @return int
     */
    public function getSessionIDServer()
    {
        return $this->sessionIDServer;
    }

    /**
     * Return sessinID for DB queries
     * @return int
     */
    public function getSessionIDDB()
    {
        return $this->sessionIDDB;
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


if (!function_exists('OrientDB_autoload')) {
    function OrientDB_autoload($className)
    {
        $prefix = 'OrientDB';
        if (strpos($className, $prefix) === 0) {
            $classTokens = substr($className, strlen($prefix));
            preg_match_all('/[A-Z]+[^A-Z]+/', $classTokens, $classTokens);
            $classToken = reset($classTokens[0]);

            switch ($classToken) {
                case 'Command':
                    $fileName = 'Commands/' . $className . '.php';
                break;
                case 'Type':
                    $fileName = 'OrientDBDataTypes.php';
                break;
                default:
                    $fileName = $className . '.php';
                break;
            }
            $fullName = dirname(__FILE__) . '/' . $fileName;
            if (file_exists($fullName)) {
                require_once $fullName;
            }
        }
    }

    spl_autoload_register('OrientDB_autoload');
}
