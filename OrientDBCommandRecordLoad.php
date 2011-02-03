<?php

class OrientDBCommandRecordLoad extends OrientDBCommandAbstract
{
	protected $clusterId;

	protected $recordId;

	public function __construct($parent)
	{
		parent::__construct($parent);
		$this->type = OrientDBCommandAbstract::RECORD_LOAD;
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
        // Add RecordId
        $this->addLong($this->recordId);
        // Fetchplan
        $this->addString($this->attribs[1]);

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
	        $record->clusterId = $this->clusterId;
	        $record->recordId = $this->recordId;
	        $record->version = $record_version;
	        $record->content = $record_content;
	        $record->parse();

	        return $record;
        }
        return false;

	}

}