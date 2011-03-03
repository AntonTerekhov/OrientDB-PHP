<?php

class OrientDBCommandDictionaryPut extends OrientDBCommandAbstract
{

    protected $key;

    protected $clusterID;

    protected $recordPos;

    protected $recordType;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::DICTIONARY_PUT;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) > 3 || count($this->attribs) < 2) {
            throw new OrientDBWrongParamsException('This command requires key name, record ID and, optionally, record Type');
        }
        $this->key = $this->attribs[0];
        // Process recordID
        $arr = explode(':', $this->attribs[1]);
        if (count($arr) != 2) {
            throw new OrientDBWrongParamsException('Wrong format for record ID');
        }
        $this->clusterID = (int) $arr[0];
        $this->recordPos = (int) $arr[1];
        if ($this->clusterID === 0 || $this->recordPos === 0) {
            throw new OrientDBWrongParamsException('Wrong format for record ID');
        }
        // Process recordType
        $this->recordType = OrientDB::RECORD_TYPE_DOCUMENT;
        if (count($this->attribs) == 3) {
            if (in_array($this->attribs[2], OrientDB::$recordTypes)) {
                $this->recordType = $this->attribs[2];
            } else {
                throw new OrientDBWrongParamsException('Incorrect record Type: ' . $this->attribs[2] . '. Awaliable types is: ' . implode(', ', OrientDB::$recordTypes));
            }
        }

        // Add key
        $this->addString($this->key);
        // Add record-type
        $this->addByte($this->recordType);
        // Add clustedID
        $this->addShort($this->clusterID);
        // Add RecordPos
        $this->addLong($this->recordPos);
    }

    protected function parse()
    {
        $record = $this->readRecord();
        return $record;
    }
}