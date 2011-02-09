<?php

class OrientDBCommandDictionarySize extends OrientDBCommandAbstract
{
    protected $key;

	protected $clusterId;

    protected $recordId;

    protected $recordType;

	public function __construct($parent)
	{
		parent::__construct($parent);
		$this->type = OrientDBCommandAbstract::DICTIONARY_SIZE;
	}

	public function prepare()
	{
		parent::prepare();
	}

	protected function parse()
	{
        $result = $this->readInt();
        return $result;
	}
}