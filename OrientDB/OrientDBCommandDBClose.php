<?php

class OrientDBCommandDBClose extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::DB_CLOSE;
    }

    protected function parse()
    {
    }
}