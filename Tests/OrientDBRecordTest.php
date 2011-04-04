<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBRecordTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        //$this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        //$this->db = null;
    }

    public function testParseRecordID()
    {
    	$clusterID = 1;
        $recordPos = 2;

        $record = new OrientDBRecord();
        $record->clusterID = $clusterID;
        $record->recordPos = $recordPos;
        $this->assertNull($record->recordID);
        $record->parse();
        $this->assertEquals($clusterID . ':' . $recordPos, $record->recordID);
    }

    public function testParseRecordContent()
    {
    	$record = new OrientDBRecord();
    	$key = 'name';
    	$value = 'Василий';
    	$record->content = $key . ':"' . $value . '"';
    	$record->parse();

    	$this->assertEquals($value, $record->data->name);
    }

}

/*
 *
 *
 *
 * $record = new OrientDBRecord();

        $record->classID == null;


        $record->clusterID = 1;

        $record->recordPos = 1;

        $record->content = '';

        $record->parse();
 */