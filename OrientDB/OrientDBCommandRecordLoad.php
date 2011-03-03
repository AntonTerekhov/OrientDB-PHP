<?php

class OrientDBCommandRecordLoad extends OrientDBCommandAbstract
{

    protected $clusterID;

    protected $recordPos;

    protected $fetchPlan;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::RECORD_LOAD;
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

        if ($this->clusterID === 0 || $this->recordPos === 0) {
            throw new OrientDBWrongParamsException('Wrong format for record ID');
        }
        // Add ClusterId
        $this->addShort($this->clusterID);
        // Add RecordId
        $this->addLong($this->recordPos);
        // Fetchplan
        $this->fetchPlan = '';
        if (count($this->attribs) == 2) {
            $this->fetchPlan = $this->attribs[1];
        }
        $this->addString($this->fetchPlan);
    }

    protected function parse()
    {
        $status = $this->readByte();
        if ($status != chr(0)) {
            $this->debugCommand('record_content');
            $record_content = $this->readBytes();
            $this->debugCommand('record_version');
            $record_version = $this->readInt();
            $this->debugCommand('record_type');
            $record_type = $this->readByte();
            $this->debugCommand('status');
            $status = $this->readByte();
            while ($status != chr(0)) {
                $this->debugCommand('record_content');
                $record = $this->readRecord();
                $this->debugCommand('status');
                $status = $this->readByte();
                // @TODO Deal with caching entries
            }
            // Form a record
            $record = new OrientDBRecord();
            // @TODO fix classId
            $record->classId = null;
            $record->type = $record_type;
            $record->clusterId = $this->clusterID;
            $record->recordPos = $this->recordPos;
            $record->version = $record_version;
            $record->content = $record_content;
            $record->parse();

            return $record;
        }
        return false;
    }
}