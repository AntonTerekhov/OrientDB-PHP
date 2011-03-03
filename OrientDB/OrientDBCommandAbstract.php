<?php

abstract class OrientDBCommandAbstract
{

    const SHUTDOWN = 1;

    const CONNECT = 2;

    const DB_OPEN = 5;

    const DB_CREATE = 6;

    const DB_CLOSE = 7;

    const DB_EXIST = 8;

    const DB_DELETE = 9;

    const DATACLUSTER_ADD = 10;

    const DATACLUSTER_REMOVE = 11;

    const DATACLUSTER_COUNT = 12;

    const DATACLUSTER_DATARANGE = 13;

    //    const DATASEGMENT_ADD = 0x20;

    //    const DATASEGMENT_REMOVE = 0x21;

    const RECORD_LOAD = 30;

    const RECORD_CREATE = 31;

    const RECORD_UPDATE = 32;

    const RECORD_DELETE = 33;

    const COUNT = 40;

    const COMMAND = 41;

    const DICTIONARY_LOOKUP = 50;

    const DICTIONARY_PUT = 51;

    const DICTIONARY_REMOVE = 52;

    const DICTIONARY_SIZE = 53;

    const DICTIONARY_KEYS = 54;

    //    const TX_COMMIT = 0x60;

    const CONFIG_GET = 70;

    const CONFIG_SET = 71;

    const CONFIG_LIST = 72;

    const STATUS_SUCCESS = 0x00;

    const STATUS_ERROR = 0x01;

    private $socket;

    /**
     * Command type
     * @var unknown_type
     */
    public $type;

    /**
     * Attributes
     * @var unknown_type
     */
    protected $attribs;

    /**
     * TransactionId to identify queries
     * @var unknown_type
     */
    protected static $transactionId = 0;

    /**
     * Current transaction id
     * @var unknown_type
     */
    protected $currentTransactionId;

    /**
     * Request Status, Success or Error
     * @var unknown_type
     */
    protected $requestStatus;

    /**
     * Request bytes tranferred to server
     * @var unknown_type
     */
    protected $requestBytes;

    protected $debug;

    private $parent;

    public function __construct($parent)
    {
        $this->socket = $parent->socket;
        $this->debug = $parent->isDebug();
        $this->parent = $parent;
    }

    public function prepare()
    {
        $this->requestBytes .= chr($this->type);
        $this->currentTransactionId = ++self::$transactionId;
        $this->addInt($this->currentTransactionId);
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

        $requestTransactionId = $this->readInt();
        if ($requestTransactionId !== $this->currentTransactionId) {
            throw new OrientDBException('Transaction ID mismatch');
        }

        if ($this->requestStatus === chr(OrientDBCommandAbstract::STATUS_SUCCESS)) {
            return $this->parse();
        } elseif ($this->requestStatus === chr(OrientDBCommandAbstract::STATUS_ERROR)) {
            $exception = null;
            while ($this->readByte() === chr(OrientDBCommandAbstract::STATUS_ERROR)) {
                $javaException = $this->readString();
                $exception = new OrientDBException($this->readString(), 0, is_null($exception) ? null : $exception);
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
        $this->debugCommand('record_classId');
        $record->classId = $this->readShort();
        // @TODO: fix it in more pleasant way
        // as seen at enterprise/src/main/java/com/orientechnologies/orient/enterprise/channel/binary/OChannelBinaryProtocol.java
        //  RECORD_NULL = -2
        if ($record->classId == 65534) {
            return false;
        }
        $this->debugCommand('record_type');
        $record->type = $this->readByte();
        $this->debugCommand('record_clusterId');
        $record->clusterId = $this->readShort();
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
