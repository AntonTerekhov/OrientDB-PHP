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
        $this->assertSame($clusterID . ':' . $recordPos, $record->recordID);
    }

    public function testParseRecordContentSimpleString()
    {
    	$record = new OrientDBRecord();
    	$key = 'name';
    	$value = 'Василий';
    	$record->content = $key . ':"' . $value . '"';
    	$record->parse();

        $this->assertSame($value, $record->data->name);
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
            $this->assertSame($values[$i], $record->data->$keys[$i]);
        }
    }

    public function testParseRecordContentComplex()
    {
        $record = new OrientDBRecord();
        $record->content = 'Profile@nick:"ThePresident",follows:[],followers:[#10:5,#10:6],name:"Barack",surname:"Obama",location:#3:2,invitedBy:,salary_cloned:,salary:120.3f';
        $record->parse();

        $this->assertSame("Profile", $record->className);
        $this->assertSame("ThePresident", $record->data->nick);
        $this->assertInternalType('array', $record->data->follows);
        $this->assertSame(0, count($record->data->follows));
        $this->assertInternalType('array', $record->data->followers);
        $this->assertSame(2, count($record->data->followers));
        $this->assertSame('#10:5', $record->data->followers[0]);
        $this->assertSame('#10:6', $record->data->followers[1]);
        $this->assertSame("Barack", $record->data->name);
        $this->assertSame("Obama", $record->data->surname);
        $this->assertSame("#3:2", $record->data->location);
        $this->assertNull($record->data->invitedBy);
        $this->assertNull($record->data->salary_cloned);
        $this->assertSame(120.3, $record->data->salary);
    }
}