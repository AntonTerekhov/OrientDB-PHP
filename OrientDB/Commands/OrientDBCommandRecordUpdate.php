<?php

class OrientDBCommandRecordUpdate extends OrientDBCommandAbstract
{

    protected $clusterID;

    protected $recordPos;

    protected $recordType;

    protected $version;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::RECORD_UPDATE;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) > 4 || count($this->attribs) < 2) {
            throw new OrientDBWrongParamsException('This command requires record ID, record content and, optionally, record version and, optionally, record type');
        }
        $arr = explode(':', $this->attribs[0]);
        if (count($arr) != 2) {
            throw new OrientDBWrongParamsException('Wrong format for record ID');
        }
        $this->clusterID = (int) $arr[0];
        $this->recordPos = (int) $arr[1];

        if ($this->clusterID === 0 || (string) $this->recordPos !== $arr[1]) {
            throw new OrientDBWrongParamsException('Wrong format for record ID');
        }
        // Add ClusterID
        $this->addShort($this->clusterID);
        // Add Record pos
        $this->addLong($this->recordPos);
        // Add record content
        $this->addBytes($this->attribs[1]);
        // Add version
        if (count($this->attribs) >= 3) {
            $this->version = (int) $this->attribs[2];
        } else {
            // Pessimistic way
            $this->version = -1;
        }
        $this->addInt($this->version);
        if (count($this->attribs) == 4) {
            if (in_array($this->attribs[3], OrientDB::$recordTypes)) {
                $this->recordType = $this->attribs[3];
            } else {
                throw new OrientDBWrongParamsException('Incorrect record Type: ' . $this->attribs[2] . '. Awaliable types is: ' . implode(', ', OrientDB::$recordTypes));
            }
        } else {
            $this->recordType = OrientDB::RECORD_TYPE_DOCUMENT;
        }
        // Add recordType
        $this->addByte($this->recordType);
    }

    protected function parse()
    {
        $this->debugCommand('record_version');
        $version = $this->readInt();
        return $version;
    }
}