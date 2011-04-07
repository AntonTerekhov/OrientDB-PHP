<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBRecordLoadTest extends OrientDBBaseTesting
{

    protected $clusterID = 1;

    protected $recordContent = 'testrecord:0';

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testRecordLoadOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordLoad();
    }

    public function testRecordLoadOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordLoad();
    }

    public function testRecordLoadOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordLoad();
    }

    public function testRecordLoadOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $record = $this->db->recordLoad($this->clusterID . ':' . $recordPos);
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->AssertSame($this->recordContent, $record->content);
        $this->AssertSame($this->clusterID . ':' . $recordPos, $record->recordID);
        $result = $this->db->recordDelete($this->clusterID . ':' . $recordPos);
        $this->assertTrue($result);
    }

    public function testRecordLoadWithWrongOptionCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordLoad();
    }

    public function testRecordLoadWithWrongRecordIDOne()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordLoad('INVALID', '');
    }

    public function testRecordLoadWithWrongRecordIDTwo()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordLoad('INVALID:', '');
    }

    public function testRecordLoadWithWrongRecordIDThree()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordLoad(':INVALID', '');
    }

    public function testRecordLoadWithWrongRecordIDFour()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordLoad('1:INVALID', '');
    }

    public function testRecordLoadWithRecordPosZero()
    {
        $recordPos = 0;
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->assertInstanceOf('OrientDBRecord', $record);
    }

    public function testRecordLoadWithDeletedRecordId()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $result = $this->db->recordDelete($this->clusterID . ':' . $recordPos);
        $this->assertTrue($result);
        $record = $this->db->recordLoad($this->clusterID . ':' . $recordPos);
        $this->assertFalse($record);
    }

    public function testRecordLoadWithOutOfBoundsRecordId()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->recordLoad($this->clusterID . ':' . 1000000, '');
    }

    public function testRecordLoadWithFetchPlan()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        // Load record City:1
        $record = $this->db->recordLoad(12 . ':' . 1, '*:-1');
        $this->assertInstanceOf('OrientDBRecord', $record);
    }

    public function testRecordLoadWithFetchPlanOneItem()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        // Load record City:1
        $this->assertEmpty($this->db->cachedRecords);
        $record = $this->db->recordLoad(12 . ':' . 1, '*:1');
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->AssertSame(1, count($this->db->cachedRecords));
        $record = $this->db->recordLoad(12 . ':' . 1, '*:0');
        $this->assertEmpty($this->db->cachedRecords);
    }

    public function testRecordLoadWithFetchPlanManyItems()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        // Load record City:1
        $this->assertEmpty($this->db->cachedRecords);
        $record = $this->db->recordLoad(12 . ':' . 1, '*:2');
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->AssertSame(2, count($this->db->cachedRecords));
        $record = $this->db->recordLoad(12 . ':' . 1, '*:0');
        $this->assertEmpty($this->db->cachedRecords);
    }
}