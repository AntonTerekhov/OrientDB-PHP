<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBDictionaryPutTest extends PHPUnit_Framework_TestCase
{

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    protected $db;

    protected $key = 'testkey';

    protected $recordID = '1:1';

    protected $recordIDNext = '1:2';

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testDictionaryPutOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryPut();
    }

    public function testDictionaryPutOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryPut();
    }

    public function testDictionaryPutOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryPut();
    }

    public function testDictionaryPutOnOpenDBWriter() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->dictionaryPut($this->key, $this->recordID);
    }

    public function testDictionaryPutOnOpenDBAdmin() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->dictionaryRemove($this->key);
        $record = $this->db->dictionaryPut($this->key, $this->recordID);
        // No records was on that key before
        $this->assertFalse($record);
        $this->db->dictionaryRemove($this->key);
    }

    public function testDictionaryPutWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->dictionaryPut($this->key);
    }

    public function testDictionaryPutWithWrongRecordIDOne() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->dictionaryPut($this->key, 'INVALID');
    }

    public function testDictionaryPutWithWrongRecordIDTwo() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->dictionaryPut($this->key, 'INVALID:');
    }

    public function testDictionaryPutWithWrongRecordIDThree() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->dictionaryPut($this->key, ':INVALID');
    }

    public function testDictionaryPutWithTypeBytes() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->dictionaryRemove($this->key);
        $record = $this->db->dictionaryPut($this->key, $this->recordID, OrientDB::RECORD_TYPE_BYTES);
        // No records was on that key before
        $this->assertFalse($record);
        $this->db->dictionaryRemove($this->key);
    }

    public function testDictionaryPutWithTypeColumn() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->dictionaryRemove($this->key);
        $record = $this->db->dictionaryPut($this->key, $this->recordID, OrientDB::RECORD_TYPE_COLUMN);
        // No records was on that key before
        $this->assertFalse($record);
        $this->db->dictionaryRemove($this->key);
    }

    public function testDictionaryPutWithTypeDocument() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->dictionaryRemove($this->key);
        $record = $this->db->dictionaryPut($this->key, $this->recordID, OrientDB::RECORD_TYPE_DOCUMENT);
        // No records was on that key before
        $this->assertFalse($record);
        $this->db->dictionaryRemove($this->key);
    }

    public function testDictionaryPutWithTypeFlat() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->dictionaryRemove($this->key);
        $record = $this->db->dictionaryPut($this->key, $this->recordID, OrientDB::RECORD_TYPE_FLAT);
        // No records was on that key before
        $this->assertFalse($record);
        $this->db->dictionaryRemove($this->key);
    }

    public function testDictionaryPutWithWrongType() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->dictionaryPut($this->key, $this->recordID, '!');
    }

    public function testDictionaryPutReturnPrevious() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->dictionaryRemove($this->key);
        $record = $this->db->dictionaryPut($this->key, $this->recordID);
        // No records was on that key before
        $this->assertFalse($record);
        $record = $this->db->dictionaryPut($this->key, $this->recordIDNext);
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->assertAttributeEquals($this->recordID, 'recordID', $record);
        $this->db->dictionaryRemove($this->key);
    }

    public function testDictionaryPutWrongRecordID() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->dictionaryRemove($this->key);
        $record = $this->db->dictionaryPut($this->key, '1000000:1000000');
        $this->setExpectedException('OrientDBException');
        $this->db->dictionaryRemove($this->key);
    }
}