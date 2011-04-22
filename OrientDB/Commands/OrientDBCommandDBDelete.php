<?php

class OrientDBCommandDBDelete extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::DB_DELETE;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 1) {
            throw new OrientDBWrongParamsException('This command requires DB name');
        }
        // Add DB name
        $this->addString($this->attribs[0]);
    }

    protected function parse()
    {
        // Orient DB returns nothing here
        return true;
    }
}