<?php

class OrientDBCommandIndexSize extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::INDEX_SIZE;
    }

    public function prepare()
    {
        parent::prepare();
    }

    protected function parse()
    {
        $this->debugCommand('size');
        $result = $this->readInt();
        return $result;
    }
}