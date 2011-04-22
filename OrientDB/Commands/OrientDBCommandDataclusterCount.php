<?php

class OrientDBCommandDataclusterCount extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::DATACLUSTER_COUNT;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 1) {
            throw new OrientDBWrongParamsException('This command requires cluster IDs in array');
        }
        if (!is_array($this->attribs[0])) {
            throw new OrientDBWrongParamsException('Array expected');
        }
        // Add count
        $this->addShort(count($this->attribs[0]));
        // Add clusterIDs
        for ($i = 0; $i < count($this->attribs[0]); $i++) {
            $this->addShort($this->attribs[0][$i]);
        }
    }

    protected function parse()
    {
        $this->debugCommand('count');
        $count = $this->readLong();
        return $count;
    }
}