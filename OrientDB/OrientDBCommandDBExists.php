<?php

class OrientDBCommandDBExists extends OrientDBCommandAbstract
{

	public function __construct($parent)
	{
		parent::__construct($parent);
		$this->type = OrientDBCommandAbstract::DB_EXIST;
	}

	public function prepare()
	{
		parent::prepare();
	}

	protected function parse()
	{
        $result = $this->readByte();
        if ($result == chr(1)) {
        	return true;
        }
        return false;
	}

}