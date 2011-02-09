<?php

class OrientDBCommandRecordCreate extends OrientDBCommandAbstract
{
	protected $clusterId;

	protected $recordType;

	public function __construct($parent)
	{
		parent::__construct($parent);
		$this->type = OrientDBCommandAbstract::RECORD_CREATE;
	}

	public function prepare()
	{
		parent::prepare();
		$this->clusterId = (int) $this->attribs[0];
		// Add ClusterId
        $this->addShort($this->clusterId);
        // Add RecordContent
        $this->addBytes($this->attribs[1]);
        if (count($this->attribs) > 2) {
            $this->recordType = $this->attribs[2];
        } else {
        	$this->recordType = OrientDB::RECORD_TYPE_DOCUMENT;
        }
        // recordType
        $this->addByte($this->recordType);
	}

	protected function parse()
	{
        $position = $this->readLong();
        if ($position > 0) {
        	return $position;
        }
        return false;
	}
}