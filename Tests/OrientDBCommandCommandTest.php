<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBCommandTest extends OrientDBBaseTesting
{

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testCommandOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->command('');
    }

    public function testCommandOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->command('');
    }

    public function testCommandOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->command('');
    }

    public function testCommandOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select * from city limit 7');
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
    }

    public function testCommandWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->command();
    }

    public function testCommandWithWrongMode() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->command('', 'INVALID');
    }

    public function testCommandWithModeAsync() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select from city traverse( any() )', OrientDB::COMMAND_MODE_ASYNC);
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
    }

    public function testCommandWithModeAsyncAndFetchPlan() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select from city traverse( any() )', OrientDB::COMMAND_MODE_ASYNC, '*:-1');
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
    }

    public function testCommandWithModeSync() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select * from [13:1]', OrientDB::COMMAND_MODE_SYNC);
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
    }

    public function testCommandWithNoRecordsAsync() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select from 11:4 where any() traverse(0,10) (address.city = "Rome")', OrientDB::COMMAND_MODE_ASYNC);
        $this->assertFalse($records);
    }

    public function testCommandWithNoRecordsSync() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select from 11:4 where any() traverse(0,10) (address.city = "Rome")', OrientDB::COMMAND_MODE_SYNC);
        $this->assertFalse($records);
    }

//    public function testCommandWithSingleRecordSync() {
//        $this->db->DBOpen('demo', 'writer', 'writer');
//        $this->db->setDebug(true);
//
//        $records = $this->db->command('insert into cluster:testcluster (name) values ("Jay")', OrientDB::COMMAND_MODE_ASYNC);
//
//        $this->assertFalse($records);
//    }


    // @TODO tests with cached records, tests with sync mode
}