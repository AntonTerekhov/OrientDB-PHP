<?php

class OrientDBCommandDBExists extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::DB_EXIST;
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
        $this->debugCommand('exist_result');
        $result = $this->readByte();
        if ($result == chr(1)) {
            return true;
        }
        return false;
    }
}