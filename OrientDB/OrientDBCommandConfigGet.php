<?php

class OrientDBCommandConfigGet extends OrientDBCommandAbstract
{

	public function __construct($parent)
	{
		parent::__construct($parent);
		$this->type = OrientDBCommandAbstract::CONFIG_GET;
	}

    public function prepare()
    {
        parent::prepare();
        // Add option name
        $this->addString($this->attribs[0]);
    }

	protected function parse()
	{
        $value = $this->readString();

        return $value;
	}

}