<?php

class OrientDBCommandRecordCreate extends OrientDBCommandAbstract
{

    protected $clusterID;

    protected $recordType;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::RECORD_CREATE;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) > 3 || count($this->attribs) < 2) {
            throw new OrientDBWrongParamsException('This command requires cluster ID, record content and, optionally, record Type');
        }
        // Process clusterID
        $this->clusterID = (int) $this->attribs[0];
        // Add ClusterID
        $this->addShort($this->clusterID);
        // Add RecordContent
        $this->addBytes($this->attribs[1]);
        // recordType
        $this->recordType = OrientDB::RECORD_TYPE_DOCUMENT;
        if (count($this->attribs) == 3) {
            if (in_array($this->attribs[2], OrientDB::$recordTypes)) {
                $this->recordType = $this->attribs[2];
            } else {
                throw new OrientDBWrongParamsException('Incorrect record Type: ' . $this->attribs[2] . '. Awaliable types is: ' . implode(', ', OrientDB::$recordTypes));
            }
        }
        $this->addByte($this->recordType);
    }

    protected function parse()
    {
        $position = $this->readLong();
        return $position;
    }
}