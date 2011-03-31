<?php

class OrientDBRecord
{

    public $classID;

    public $type;

    public $clusterID;

    public $recordPos;

    public $recordID;

    public $version;

    public $content;

    public $data;

    public function parse()
    {
        $this->recordID = $this->clusterID . ':' . $this->recordPos;
        // @TODO Parse content to data
    }
}