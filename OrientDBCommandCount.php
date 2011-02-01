<?php

class OrientDBCommandCount extends OrientDBCommandAbstract
{

	public function __construct($socket, $protocolVersion)
	{
		parent::__construct($socket, $protocolVersion);
		$this->type = OrientDBCommandAbstract::COUNT;
	}

	public function prepare()
	{
		parent::prepare();
        $this->addString($this->attribs[0]);
	}

	protected function parse()
	{
        $count = $this->readLong();
        return $count;
	}

}