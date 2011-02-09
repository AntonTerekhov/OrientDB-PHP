<?php

class OrientDBCommandDictionaryLookup extends OrientDBCommandAbstract
{
    protected $key;

	public function __construct($parent)
	{
		parent::__construct($parent);
		$this->type = OrientDBCommandAbstract::DICTIONARY_PUT;
	}

	public function prepare()
	{
		parent::prepare();
		$this->key = $this->attribs[0];
		// Add key
        $this->addString($this->key);
	}

	protected function parse()
	{
//		  $this->readRaw(100);
        $record = $this->readRecord();
//        return $record;
// @TODO: fix dictionary lookup command
	}
}