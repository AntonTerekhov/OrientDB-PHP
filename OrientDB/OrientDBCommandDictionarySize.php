<?php

class OrientDBCommandDictionarySize extends OrientDBCommandAbstract
{
    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::DICTIONARY_SIZE;
    }

    public function prepare()
    {
        parent::prepare();
    }

    protected function parse()
    {
        $result = $this->readInt();
        return $result;
    }
}