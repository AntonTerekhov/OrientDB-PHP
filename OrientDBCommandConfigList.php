<?php

class OrientDBCommandConfigList extends OrientDBCommandAbstract
{

	public function __construct($socket, $protocolVersion)
	{
		parent::__construct($socket, $protocolVersion);
		$this->type = OrientDBCommandAbstract::CONFIG_LIST;
	}

	protected function parse()
	{


        $numOptions = $this->readShort();

        $options = array();
        for ($i = 0; $i < $numOptions; $i++)
        {
            $options[$this->readString()] = $this->readString();
        }

        return $options;
	}

}