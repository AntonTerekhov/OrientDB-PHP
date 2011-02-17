<?php

class OrientDBCommandDictionaryRemove extends OrientDBCommandAbstract
{
    protected $key;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::DICTIONARY_REMOVE;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 1) {
            throw new OrientDBWrongParamsException('This command requires key name');
        }
        $this->key = $this->attribs[0];
        // Add key
        $this->addString($this->key);
    }

    protected function parse()
    {
        $record = $this->readRecord();
        return $record;
    }
}