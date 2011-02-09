<?php

class OrientDBCommandDBCreate extends OrientDBCommandAbstract
{

	public function __construct($parent)
	{
		parent::__construct($parent);
		$this->type = OrientDBCommandAbstract::DB_CREATE;
	}

	public function prepare()
	{
		parent::prepare();
		// Add DB name
        $this->addString($this->attribs[0]);
        // Add DB type
        if (!in_array($this->attribs[1], array('memory', 'local'))) {
        	throw new OrientDBException('Not supported DB type');
        }
        $this->addString($this->attribs[1]);
	}

	protected function parse()
	{
        return true;
	}

}