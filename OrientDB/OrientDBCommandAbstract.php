<?php

abstract class OrientDBCommandAbstract
{

    const SHUTDOWN = 1;

    const CONNECT = 2;

    const DB_OPEN = 3;

    const DB_CREATE = 4;

    const DB_CLOSE = 5;

    const DB_EXIST = 6;

    const DB_DELETE = 7;

    // const DB_SIZE = 8;

    // const DB_COUNTRECORDS = 9;

    const DATACLUSTER_ADD = 10;

    const DATACLUSTER_REMOVE = 11;

    const DATACLUSTER_COUNT = 12;

    const DATACLUSTER_DATARANGE = 13;

    // const DATASEGMENT_ADD = 20;

    // const DATASEGMENT_REMOVE = 21;

    const RECORD_LOAD = 30;

    const RECORD_CREATE = 31;

    const RECORD_UPDATE = 32;

    const RECORD_DELETE = 33;

    const COUNT = 40;

    const COMMAND = 41;

    const INDEX_LOOKUP = 50;

    const INDEX_PUT = 51;

    const INDEX_REMOVE = 52;

    const INDEX_SIZE = 53;

    const INDEX_KEYS = 54;

    // const INDEX_QUERY = 55;

    const TX_COMMIT = 60;

    const CONFIG_GET = 70;

    const CONFIG_SET = 71;

    const CONFIG_LIST = 72;

    const STATUS_SUCCESS = 0x00;

    const STATUS_ERROR = 0x01;

    private $socket;

    /**
     * Command type
     * @var integer
     */
    public $type;

    /**
     * Attributes
     * @var array
     */
    protected $attribs;

    /**
     * TransactionID to identify queries
     * @var integer
     */
    protected static $transactionID = 0;

    /**
     * Current transaction id
     * @var integer
     */
    protected $currentTransactionID;

    /**
     * Request Status, Success or Error
     * @var unknown_type
     */
    protected $requestStatus;

    /**
     * Request bytes tranferred to server
     * @var string
     */
    protected $requestBytes;

    /**
     * Print debug messages
     * @var boolean
     */
    protected $debug;

    /**
     * Link to OrientDB instanse
     * @var OrientDB
     */
    protected $parent;

    public function __construct($parent)
    {
        $this->socket = $parent->socket;
        $this->debug = $parent->isDebug();
        $this->parent = $parent;
    }

    public function prepare()
    {
        $this->requestBytes .= chr($this->type);
        $this->currentTransactionID = ++self::$transactionID;
        $this->addInt($this->currentTransactionID);
    }

    public function execute()
    {
        $this->socket->debug = $this->debug;
        $this->socket->send($this->requestBytes);

        if (is_null($this->parent->protocolVersion)) {
            $this->parent->setProtocolVersion($this->readShort());
        }
        if ($this->type == self::DB_CLOSE) {
            // No incoming bytes
            return;
        }
        $this->requestStatus = $this->readByte();

        $requestTransactionID = $this->readInt();
        if ($requestTransactionID !== $this->currentTransactionID) {
            throw new OrientDBException('Transaction ID mismatch');
        }

        if ($this->requestStatus === chr(OrientDBCommandAbstract::STATUS_SUCCESS)) {
            return $this->parse();
        } elseif ($this->requestStatus === chr(OrientDBCommandAbstract::STATUS_ERROR)) {
            $exception = null;
            while ($this->readByte() === chr(OrientDBCommandAbstract::STATUS_ERROR)) {
                $this->debugCommand('exception_javaclass');
                $javaException = $this->readString();
                $this->debugCommand('exception_message');
                $exception = new OrientDBException($javaException . ': ' . $this->readString(), 0, is_null($exception) ? null : $exception);
            }
            throw $exception;
        } else {
            throw new OrientDBException('Unknown error');
        }
    }

    protected abstract function parse();

    public function setAttribs()
    {
        $this->attribs = func_get_args();
    }

    protected function readRaw($length)
    {
        $data = '';
        while (($length - strlen($data)) > 0) {
            $data .= $this->socket->read($length - strlen($data));
        }
        return $data;
    }

    protected function readByte()
    {
        return $this->readRaw(1);
    }

    protected function readShort()
    {
        $data = unpack('n', $this->readRaw(2));
        return reset($data);
    }

    protected function readInt()
    {
        $data = unpack('N', $this->readRaw(4));
        return reset($data);
    }

    protected function readLong()
    {
        $data = unpack('N', $this->readRaw(4));
        // @TODO wtf? Java sends long as 64-bit
        if (reset($data) > 0) {
            throw new OrientDBException('64-bit long detected!');
        }
        $data = unpack('N', $this->readRaw(4));
        return reset($data);
    }

    protected function readString()
    {
        $size = $this->readInt();

        return $this->readRaw($size);
    }

    protected function readBytes()
    {
        $size = $this->readInt();
        if ($size == -1) {
            return null;
        }
        return $this->readRaw($size);
    }

    protected function readRecord()
    {
        $record = new OrientDBRecord();
        $this->debugCommand('record_classID');
        $record->classID = $this->readShort();
        // as seen at enterprise/src/main/java/com/orientechnologies/orient/enterprise/channel/binary/OChannelBinaryProtocol.java
        // RECORD_NULL = -2
        if ($record->classID == -2) {
            // No record
            return false;
        }
        if ($record->classID == -1) {
            // No class
            $record->classID == null;
        }
        $this->debugCommand('record_type');
        $record->type = $this->readByte();
        $this->debugCommand('record_clusterID');
        $record->clusterID = $this->readShort();
        $this->debugCommand('record_position');
        $record->recordPos = $this->readLong();
        $this->debugCommand('record_version');
        $record->version = $this->readInt();
        $this->debugCommand('record_content');
        $record->content = $this->readBytes();
        $record->parse();
        return $record;
    }

    protected function addByte($byte)
    {
        $this->requestBytes .= $byte;
    }

    protected function addShort($short)
    {
        $this->requestBytes .= pack('n', $short);
    }

    protected function addInt($int)
    {
        $this->requestBytes .= pack('N', $int);
    }

    protected function addLong($long)
    {
        // @TODO support 64-bit
        $this->requestBytes .= str_repeat(chr(0), 4) . pack('N', $long);
    }

    protected function addString($string)
    {
        $this->addInt(strlen($string));
        $this->requestBytes .= $string;
    }

    protected function addBytes($string)
    {
        $this->addInt(strlen($string));
        $this->requestBytes .= $string;
    }

    protected function debugCommand($commandName)
    {
        if ($this->debug) {
            echo '>' . $commandName . PHP_EOL;
        }
    }
}
