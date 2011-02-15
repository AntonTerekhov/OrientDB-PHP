<?php

class OrientDBCommandConfigSet extends OrientDBCommandAbstract
{

	public function __construct($parent)
	{
		parent::__construct($parent);
		$this->type = OrientDBCommandAbstract::CONFIG_SET;
	}

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 2) {
            throw new OrientDBWrongParamsException('This command requires config name and value');
        }
        // Add option name
        $this->addString($this->attribs[0]);
        // Add option value
        $this->addString($this->attribs[1]);
    }

	protected function parse()
	{
        return true;
	}

}