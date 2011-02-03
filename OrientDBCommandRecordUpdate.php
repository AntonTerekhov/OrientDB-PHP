<?php

class OrientDBCommandRecordUpdate extends OrientDBCommandAbstract
{

    protected $clusterId;

    protected $recordId;

    protected $recordType;

    protected $version;

    public function __construct($socket, $protocolVersion)
    {
        parent::__construct($socket, $protocolVersion);
        $this->type = OrientDBCommandAbstract::RECORD_UPDATE;
    }

    public function prepare()
    {
        parent::prepare();
        $record_ids = explode(':', $this->attribs[0]);
        if (count($record_ids) != 2) {
            throw new OrientDBException('Wrong format for record ID');
        }

        $this->clusterId = (int) $record_ids[0];
        $this->recordId = (int) $record_ids[1];
        // Add ClusterId
        $this->addShort($this->clusterId);
        // Add RecordContent
        $this->addLong($this->recordId);
        // Add record content
        $this->addBytes($this->attribs[1]);
        // Add version
        if (count($this->attribs) > 2) {
            $this->version = $this->attribs[2];
        } else {
            $this->version = -1;
        }
        $this->addInt($this->version);
        if (count($this->attribs) > 3) {
            $this->recordType = $this->attribs[3];
        } else {
            $this->recordType = OrientDB::RECORD_TYPE_DOCUMENT;
        }
        // Add recordType
        $this->addByte($this->recordType);
    }

    protected function parse()
    {
        $version = $this->readInt();
        return $version;
    }
}