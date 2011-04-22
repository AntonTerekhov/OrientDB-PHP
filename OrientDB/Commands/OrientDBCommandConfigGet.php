<?php

class OrientDBCommandConfigGet extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::CONFIG_GET;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 1) {
            throw new OrientDBWrongParamsException('This command requires config name');
        }
        // Add option name
        $this->addString($this->attribs[0]);
    }

    protected function parse()
    {
        $this->debugCommand('config_value');
        $value = $this->readString();

        return $value;
    }

}