<?php

class OrientDBCommandShutdown extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::SHUTDOWN;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 2) {
            throw new OrientDBWrongParamsException('This command requires name and password');
        }
        // Add name
        $this->addString($this->attribs[0]);
        // Add name
        $this->addString($this->attribs[1]);
    }

    protected function parse()
    {
    }
}