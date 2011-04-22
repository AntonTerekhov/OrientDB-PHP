<?php

class OrientDBCommandRecordLoad extends OrientDBCommandAbstract
{

    protected $clusterID;

    protected $recordPos;

    protected $fetchPlan;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::RECORD_LOAD;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) > 2 || count($this->attribs) < 1) {
            throw new OrientDBWrongParamsException('This command requires record ID and, optionally, fetch plan');
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
        // Add RecordID
        $this->addLong($this->recordPos);
        // Fetchplan
        $this->fetchPlan = '*:0';
        if (count($this->attribs) == 2) {
            $this->fetchPlan = $this->attribs[1];
        }
        $this->addString($this->fetchPlan);
    }

    protected function parse()
    {
        $this->debugCommand('record_status_first');
        $status = $this->readByte();
        if ($status != chr(0)) {
            $this->debugCommand('record_content');
            $record_content = $this->readBytes();
            $this->debugCommand('record_version');
            $record_version = $this->readInt();
            $this->debugCommand('record_type');
            $record_type = $this->readByte();
            $this->debugCommand('record_status_cache');
            $status = $this->readByte();

            $cachedRecords = array();
            while ($status != chr(0)) {
                $this->debugCommand('record_content');
                $record = $this->readRecord();
                $cachedRecords[$record->recordID] = $record;
                $this->debugCommand('record_status_next');
                $status = $this->readByte();
            }
            // Invalidate cache
            $this->parent->cachedRecords = $cachedRecords;
            // Form a record
            $record = new OrientDBRecord();
            // @TODO fix classID
            $record->classID = null;
            $record->type = $record_type;
            $record->clusterID = $this->clusterID;
            $record->recordPos = $this->recordPos;
            $record->version = $record_version;
            $record->content = $record_content;
            $record->parse();

            return $record;
        }
        return false;
    }
}