<?php

class OrientDBCommandDataclusterRemove extends OrientDBCommandAbstract
{
    protected $clusterID;

    public function __construct($parent) {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::DATACLUSTER_REMOVE;
    }

    public function prepare() {
        parent::prepare();
        if (count($this->attribs) != 1) {
            throw new OrientDBWrongParamsException('This command requires cluster ID');
        }
        if (is_int($this->attribs[0])) {
            $this->clusterID =$this->attribs[0];
        } else {
            throw new OrientDBWrongParamsException('Integer expected');
        }
        // Add clusterID
        $this->addShort($this->clusterID);
    }

    protected function parse() {
        $result = $this->readByte();
        if ($result == chr(1)) {
            return true;
        }
        return false;
    }
}