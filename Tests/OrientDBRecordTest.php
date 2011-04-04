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

    public function testParseRecordContentSimpleString()
    {
    	$record = new OrientDBRecord();
    	$key = 'name';
    	$value = 'Василий';
    	$record->content = $key . ':"' . $value . '"';
    	$record->parse();

    	$this->assertEquals($value, $record->data->name);
    }

    public function testParseRecordContentTwoStrings()
    {
        $record = new OrientDBRecord();
        $keys = array('FirstName', 'LastName');
        $values = array('Василий','Иванов');

        $temp = array();
        for ($i = 0; $i < count($keys); $i++) {
           $temp[] =  $keys[$i] . ':"' . $values[$i] . '"';
        }
        $record->content = implode(',', $temp);
        $record->parse();

        for ($i = 0; $i < count($keys); $i++) {
            $this->assertEquals($values[$i], $record->data->$keys[$i]);
        }
    }
}