<?php

class OrientDBCommandIndexKeys extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::INDEX_KEYS;
    }

    public function prepare()
    {
        parent::prepare();
    }

    protected function parse()
    {
        $this->debugCommand('keys_count');
        $keysCount = $this->readInt();
        $keys = array();
        for ($i = 0; $i < $keysCount; $i++) {
            $this->debugCommand('read_keys');
            $keys[] = $this->readString();
        }
        return $keys;
    }
}