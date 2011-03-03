<?php

class OrientDBCommandRecordDelete extends OrientDBCommandAbstract
{

    protected $clusterID;

    protected $recordPos;

    protected $recordType;

    protected $version;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::RECORD_DELETE;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) > 2 || count($this->attribs) < 1) {
            throw new OrientDBWrongParamsException('This command requires record ID and, optionally, record version');
        }
        $arr = explode(':', $this->attribs[0]);
        if (count($arr) != 2) {
            throw new OrientDBWrongParamsException('Wrong format for record ID');
        }
        $this->clusterID = (int) $arr[0];
        $this->recordPos = (int) $arr[1];

        if ($this->clusterID === 0 || $this->recordPos === 0) {
            throw new OrientDBWrongParamsException('Wrong format for record ID');
        }
        if (count($this->attribs) == 2) {
            $this->version = (int) $this->attribs[1];
        } else {
            // Pessimistic way
            $this->version = -1;
        }
        // Add ClusterID
        $this->addShort($this->clusterID);
        // Add RecordID
        $this->addLong($this->recordPos);
        // Add version
        $this->addInt($this->version);
    }

    protected function parse()
    {
        $result = $this->readByte();
        if ($result == chr(1)) {
            return true;
        } else {
            return false;
        }
    }
}