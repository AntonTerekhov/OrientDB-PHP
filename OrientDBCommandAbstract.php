<?php

abstract class OrientDBCommandAbstract
{

    const SHUTDOWN = 01;

    const CONNECT = 02;

    const DB_OPEN = 05;

    const DB_CREATE = 06;

    const DB_CLOSE = 07;

    const DB_EXIST = 08;

    const DB_DELETE = 09;

    //    const DATACLUSTER_ADD = 0x10;

    //    const DATACLUSTER_REMOVE = 0x11;

    //    const DATACLUSTER_COUNT = 0x12;

    //    const DATACLUSTER_DATARANGE = 0x13;

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

    /**
     * Server's protocol version. Not used currently
     * @var unknown_type
     */
    public $protocolVersion;

    protected $debug;

    public function __construct($parent)
    {
        $this->socket = $parent->socket;
        $this->protocolVersion = $parent->protocolVersion;
        $this->debug = $parent->isDebug();
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

        if (is_null($this->protocolVersion)) {
            $this->protocolVersion = $this->readShort();
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

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
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
        return reset(unpack('n', $this->readRaw(2)));
    }

    protected function readInt()
    {
        return reset(unpack('N', $this->readRaw(4)));
    }

    protected function readLong()
    {
        // @TODO wtf? Java sends long as 64-bit
        if (reset(unpack('N', $this->readRaw(4))) > 0) {
            throw new OrientDBException('64-bit long detected!');
        }
        return reset(unpack('N', $this->readRaw(4)));
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
    	$this->debugCommand('record_type');
    	$record->type = $this->readByte();
    	$this->debugCommand('record_clusterId');
    	$record->clusterId = $this->readShort();
    	$this->debugCommand('record_position');
    	$record->recordId = $this->readLong();
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
