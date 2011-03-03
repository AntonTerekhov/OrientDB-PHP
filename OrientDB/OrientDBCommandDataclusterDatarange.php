<?php

class OrientDBCommandDataclusterDatarange extends OrientDBCommandAbstract
{
    protected $clusterID;

    public function __construct($parent) {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::DATACLUSTER_DATARANGE;
    }

    public function prepare() {
        parent::prepare();
        if (count($this->attribs) != 1) {
            throw new OrientDBWrongParamsException('This command requires cluster ID');
        }
        $this->clusterID = $this->attribs[0];
        if (!is_int($this->clusterID)) {
        	throw new OrientDBWrongParamsException('Integer expected');
        }
        // Add clusterID
        $this->addShort($this->clusterID);
    }

    protected function parse() {
        $startPos = $this->readLong();
        $endPos = $this->readLong();
        return array('start' => $startPos, 'end' => $endPos);
    }
}