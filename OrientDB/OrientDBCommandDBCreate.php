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
        if (count($this->attribs) != 2) {
            throw new OrientDBWrongParamsException('This command requires DB name and type');
        }
        // Add DB name
        $this->addString($this->attribs[0]);
        // Add DB type
        $db_types = array(OrientDB::DB_TYPE_MEMORY, OrientDB::DB_TYPE_LOCAL);
        if (!in_array($this->attribs[1], $db_types)) {
            throw new OrientDBWrongParamsException('Not supported DB type. Supported types is: ' . implode(', ', $db_types));
        }
        $this->addString($this->attribs[1]);
    }

    protected function parse()
    {
        return true;
    }

}