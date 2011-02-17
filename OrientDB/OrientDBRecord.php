<?php

class OrientDBRecord
{

    public $classId;

    public $type;

    public $clusterId;

    public $recordPos;

    public $recordID;

    public $version;

    public $content;

    public $data;

    public function parse()
    {
    	$this->recordID = $this->clusterId . ':' . $this->recordPos;
        // @TODO
    }
}