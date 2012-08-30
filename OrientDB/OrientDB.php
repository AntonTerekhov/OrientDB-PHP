<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * Main class in OrientDB-PHP library.
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Main
 *
 * @method mixed command() command(int $commandMode, string $query, string $fetchplan = null) Execute SQL-like command
 * @method void commit() commit() Not yet implemented
 * @method array configList() configList() Return list of server config options
 * @method string configGet() configGet(string $optionName) Get value of an option
 * @method boolean configSet() configSet(string $optionName, string $optionValue) Set value for config option
 * @method boolean connect() connect(string $userName, string $password) Connect to OrientDB server
 * @method int count() count(string $clusterName)  Count elements in cluster
 * @method int dataclusterAdd() dataclusterAdd(string $clusterName, string $clusterType) Add a datacluster to DB
 * @method boolean dataclusterRemove() dataclusterRemove(int $clusterID) Remove datacluster from DB
 * @method int dataclusterCount() dataclusterCount(array $clusterIDs) Count elements in clusters
 * @method array dataclusterDatarange() dataclusterDatarange(int $clusterID) Return datarange for datacluster
 * @method int datasegmentAdd() datasegmentAdd(string $name, string $location) Not yet implemented. Create new datasegment
 * @method boolean datasegmentDelete() datasegmentDelete(string $name) Not yet implemented. Drop datasegment by name.
 * @method array DBOpen() DBOpen(string $dbName, string $userName, string $password) Open OrientDB database
 * @method void DBClose() DBClose() Closes currently opened DB
 * @method boolean DBCreate() DBCreate(string $dbName, string $dbType) Create new database
 * @method boolean DBDelete() DBDelete(string $dbName) Delete DB
 * @method boolean DBExists() DBExists(string $dbName) Check if DB exists
 * @method array DBList() DBList() Return DB list
 * @method mixed query(string $query) Execute general style query, for SELECT query see select() method
 * @method int recordCreate() recordCreate(int $clusterID, string $recordContent, string $recordType = OrientDB::RECORD_TYPE_DOCUMENT) Create a new record
 * @method boolean recordDelete() recordDelete(string $recordID, int $recordVersion = -1) Delete a record
 * @method OrientDBRecord recordLoad() recordLoad(string $recordID, string $fetchPlan = null) Load a record
 * @method int recordUpdate() recordUpdate(string $recordID, string $recordContent, int $recordVersion = -1, string $recordType = OrientDB::RECORD_TYPE_DOCUMENT) Update a record
 * @method mixed select() select(string $query) Execute sync-style select query
 * @method mixed selectAsync() selectAsync(string $query, string $fetchplan = null) Execute async-style select query with optional fetchplan
 * @method void shutdown() shutdown(string $userName, string $password) Shutdown OrientDB server remotely
 */
class OrientDB
{

    /**
     * Hostname of OrientDB server
     * @var string
     */
    private $host;

    /**
     * Port of OrientDB server
     * @var int
     */
    private $port;

    /**
     * Socked, connected to OrientDB server
     * @var OrientDBSocket
     */
    public $socket;

    /**
     * Debug information output control
     * @var bool
     */
    private $debug = false;

    /**
     * Client protocol version
     * @var int
     */
    public $clientVersion = 12;

    /**
     * Server's protocol version.
     * @var int
     */
    public $protocolVersion = null;

    /**
     * If we connected() to server
     * @var bool
     */
    protected $connected = false;

    /**
     * If we DBOpen() some DB
     * @var bool
     */
    protected $DBOpen = false;

    /**
     * If no DBClose() called
     * @var bool
     */
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


    const DRIVER_NAME = 'OrientDB-PHP';

    const DRIVER_VERSION = 'beta-0.4.7';

    /**
     * Record type Bytes
     * @var string
     */
    const RECORD_TYPE_BYTES = 'b';

    /**
     * Record type Document
     * @var string
     */
    const RECORD_TYPE_DOCUMENT = 'd';

    /**
     * Record type Flat
     * @var string
     */
    const RECORD_TYPE_FLAT = 'f';

    /**
     * List of available record types
     * @var array
     */
    public static $recordTypes = array(
        self::RECORD_TYPE_BYTES,
        self::RECORD_TYPE_DOCUMENT,
        self::RECORD_TYPE_FLAT);

    /**
     * Database type memory
     * @var string
     */
    const DB_TYPE_MEMORY = 'memory';

    /**
     * Database type local (disk)
     * @var string
     */
    const DB_TYPE_LOCAL = 'local';

    /**
     * Query type general
     * @var int
     */
    const COMMAND_QUERY = 1;

    /**
     * Query type synchronous select
     * @var int
     */
    const COMMAND_SELECT_SYNC = 2;

    /**
     * Query type asynchronous select
     * @var int
     */
    const COMMAND_SELECT_ASYNC = 3;

    /**
     * Datacluster type physical (disk)
     * @var string
     */
    const DATACLUSTER_TYPE_PHYSICAL = 'PHYSICAL';

    /**
     * Datacluster type memory
     * @var string
     */
    const DATACLUSTER_TYPE_MEMORY = 'MEMORY';

    /**
     * List of available datacluster types
     * @var array
     */
    public static $clusterTypes = array(
        self::DATACLUSTER_TYPE_PHYSICAL,
        self::DATACLUSTER_TYPE_MEMORY);

    /**
     * Associative list of cached records, if any
     * @var array
     */
    public $cachedRecords = array();

    /**
     * List of commands, requires to be connect()ed first
     * @var array
     */
    private static $requireConnect = array(
        OrientDBCommandAbstract::SHUTDOWN,
        OrientDBCommandAbstract::DB_CREATE,
        OrientDBCommandAbstract::DB_DELETE,
        OrientDBCommandAbstract::DB_EXIST,
        OrientDBCommandAbstract::CONFIG_GET,
        OrientDBCommandAbstract::CONFIG_SET,
        OrientDBCommandAbstract::CONFIG_LIST,
        OrientDBCommandAbstract::DB_LIST);

    /**
     * List of commands, requires to be DBOpen()ed first
     * @var array
     */
    private static $requireDBOpen = array(
        OrientDBCommandAbstract::DB_CLOSE,
        OrientDBCommandAbstract::DATACLUSTER_ADD,
        OrientDBCommandAbstract::DATACLUSTER_REMOVE,
        OrientDBCommandAbstract::DATACLUSTER_COUNT,
        OrientDBCommandAbstract::DATACLUSTER_DATARANGE,
        OrientDBCommandAbstract::DATASEGMENT_ADD,
        OrientDBCommandAbstract::DATASEGMENT_REMOVE,
        OrientDBCommandAbstract::RECORD_LOAD,
        OrientDBCommandAbstract::RECORD_CREATE,
        OrientDBCommandAbstract::RECORD_UPDATE,
        OrientDBCommandAbstract::RECORD_DELETE,
        OrientDBCommandAbstract::COUNT,
        OrientDBCommandAbstract::COMMAND,
        OrientDBCommandAbstract::TX_COMMIT);

    /**
     * Construct new instance of OrientDB-PHP
     * @param string $host
     * @param int $port
     * @param int $timeout
     */
    public function __construct($host, $port = 2424, $timeout = 30)
    {
        $this->host = $host;
        $this->port = $port;
        $this->socket = new OrientDBSocket($host, $port, $timeout);
    }

    /**
     * Destructor, implicitly unset $this->socket
     */
    public function __destruct()
    {
        if ($this->isDBOpen() &&
                $this->socket->isValid()) {
            $this->DBClose();
        }
    }

    /**
     * If connect() called on class instance
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * If DBOpen() called on class instance
     * @return bool
     */
    public function isDBOpen()
    {
        return $this->DBOpen;
    }

    /**
     * Main magic method
     * @param string $name
     * @param array $arguments
     * @throws OrientDBWrongCommandException
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $className = 'OrientDBCommand' . ucfirst($name);
        if (class_exists($className)) {
            /**
             * @var OrientDBCommandAbstract
             */
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
            throw new OrientDBWrongCommandException('Command ' . $className . ' currently not implemented');
        }
    }

    /**
     * Check if command's requirements are met
     * @param OrientDBCommandAbstract $command Command instance
     * @throws OrientDBWrongCommandException
     * @see OrientDBCommandAbstract
     */
    protected function canExecute($command)
    {

        if (!$this->active) {
            throw new OrientDBWrongCommandException('DBClose was executed. No interaction is possible.');
        }
        if (in_array($command->opType, $this->getCommandsRequiresConnect()) && !$this->isConnected()) {
            throw new OrientDBWrongCommandException('Not connected to server');
        }
        if (in_array($command->opType, $this->getCommandsRequiresDBOpen()) && !$this->isDBOpen()) {
            throw new OrientDBWrongCommandException('Database not open');
        }
    }

    /**
     * Control debug output status
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * Return debug output status
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Check if current client protocol version matches server version
     * @param int $version Server version
     * @throws OrientDBException
     */
    public function setProtocolVersion($version)
    {
        $this->protocolVersion = $version;
        if ($this->protocolVersion != $this->clientVersion) {
            throw new OrientDBException('Binary protocol is uncompatible with the Server connected: clientVersion=' . $this->clientVersion . ', serverVersion=' . $this->protocolVersion);
        }
    }

    /**
     * Return this driver supported protocol version
     * @return int
     */
    public function getProtocolVersionClient()
    {
        return $this->clientVersion;
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
     * Return sessionID for DB queries
     * @return int
     */
    public function getSessionIDDB()
    {
        return $this->sessionIDDB;
    }
}

/**
 * Base exception for OrientDB-PHP
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Main
 */
class OrientDBException extends Exception
{
}

/**
 * Exception for connection problems
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Main
 */
class OrientDBConnectException extends OrientDBException
{
}

/**
 * Exception for wrong command sequence
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Main
 */
class OrientDBWrongCommandException extends OrientDBException
{
}

/**
 * Exception for wrong command's param
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Main
 */
class OrientDBWrongParamsException extends OrientDBException
{
}

/**
 * Exception for de-serialization errors
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Main
 */
class OrientDBDeSerializeException extends OrientDBException
{
}

if (!function_exists('OrientDB_autoload')) {

    /**
     *
     * Default autoload function for OrientDB-PHP
     * @package OrientDB-PHP
     * @param string $className
     */
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