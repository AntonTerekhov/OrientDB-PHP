<?php

class OrientDBCommandDictionaryKeys extends OrientDBCommandAbstract
{
	public function __construct($parent)
	{
		parent::__construct($parent);
		$this->type = OrientDBCommandAbstract::DICTIONARY_KEYS;
	}

	public function prepare()
	{
		parent::prepare();
	}

	protected function parse()
	{
        $keysCount = $this->readInt();
        $keys = array();
        for ($i = 0; $i < $keysCount; $i++) {
        	$keys[] = $this->readString();
        }
        return $keys;
	}
}