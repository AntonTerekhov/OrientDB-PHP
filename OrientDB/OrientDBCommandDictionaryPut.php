<?php

class OrientDBCommandDictionaryPut extends OrientDBCommandAbstract
{
    protected $key;

	protected $clusterId;

    protected $recordId;

    protected $recordType;

	public function __construct($parent)
	{
		parent::__construct($parent);
		$this->type = OrientDBCommandAbstract::DICTIONARY_PUT;
	}

	public function prepare()
	{
		parent::prepare();
		$this->key = $this->attribs[0];
		$this->recordType = $this->attribs[1];
		$arr = explode(':', $this->attribs[2]);
        if (count($arr) != 2) {
            throw new OrientDBException('Wrong format for record ID');
        }
		$this->clusterId = (int) $arr[0];
		$this->recordId = (int) $arr[1];
		// Add key
        $this->addString($this->key);
        // Add record-type
        $this->addByte($this->recordType);
        // Add clustedId
        $this->addShort($this->clusterId);
        // Add RecordId
        $this->addLong($this->recordId);
	}

	protected function parse()
	{
//		$this->readRaw(100);
        $record = $this->readRecord();
        return $record;
	}
}