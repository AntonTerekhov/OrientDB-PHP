<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBIndexPutTest extends OrientDBBaseTesting
{

    protected $key = 'testkey';

    protected $recordID = '1:1';

    protected $recordIDNext = '1:2';

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testIndexPutOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexPut();
    }

    public function testIndexPutOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexPut();
    }

    public function testIndexPutOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexPut();
    }

    public function testIndexPutOnOpenDBWriter() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->db->indexRemove($this->key);
        $record = $this->db->indexPut($this->key, $this->recordID);
        // No records was on that key before
        $this->assertFalse($record);
        $this->db->indexRemove($this->key);
    }

    public function testIndexPutOnOpenDBAdmin() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->indexRemove($this->key);
        $record = $this->db->indexPut($this->key, $this->recordID);
        // No records was on that key before
        $this->assertFalse($record);
        $this->db->indexRemove($this->key);
    }

    public function testIndexPutWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->indexPut($this->key);
    }

    public function testIndexPutWithWrongRecordIDOne() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->indexPut($this->key, 'INVALID');
    }

    public function testIndexPutWithWrongRecordIDTwo() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->indexPut($this->key, 'INVALID:');
    }

    public function testIndexPutWithWrongRecordIDThree() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->indexPut($this->key, ':INVALID');
    }

    public function testIndexPutWithTypeBytes() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->indexRemove($this->key);
        $record = $this->db->indexPut($this->key, $this->recordID, OrientDB::RECORD_TYPE_BYTES);
        // No records was on that key before
        $this->assertFalse($record);
        $this->db->indexRemove($this->key);
    }

    public function testIndexPutWithTypeDocument() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->indexRemove($this->key);
        $record = $this->db->indexPut($this->key, $this->recordID, OrientDB::RECORD_TYPE_DOCUMENT);
        // No records was on that key before
        $this->assertFalse($record);
        $this->db->indexRemove($this->key);
    }

    public function testIndexPutWithTypeFlat() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->indexRemove($this->key);
        $record = $this->db->indexPut($this->key, $this->recordID, OrientDB::RECORD_TYPE_FLAT);
        // No records was on that key before
        $this->assertFalse($record);
        $this->db->indexRemove($this->key);
    }

    public function testIndexPutWithWrongType() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->indexPut($this->key, $this->recordID, '!');
    }

    public function testIndexPutReturnPrevious() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->indexRemove($this->key);
        $record = $this->db->indexPut($this->key, $this->recordID);
        // No records was on that key before
        $this->assertFalse($record);
        $record = $this->db->indexPut($this->key, $this->recordIDNext);
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->assertAttributeEquals($this->recordID, 'recordID', $record);
        $this->db->indexRemove($this->key);
    }

    public function testIndexPutWrongRecordID() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->indexRemove($this->key);
        $record = $this->db->indexPut($this->key, '1000000:1000000');
        $this->setExpectedException('OrientDBException');
        $this->db->indexRemove($this->key);
    }
}