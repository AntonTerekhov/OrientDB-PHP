<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBDictionaryRemoveTest extends PHPUnit_Framework_TestCase
{

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    protected $db;

    protected $key = 'testkey';

    protected $recordID = '1:1';

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testDictionaryRemoveOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryRemove();
    }

    public function testDictionaryRemoveOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryRemove();
    }

    public function testDictionaryRemoveOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryRemove();
    }

    public function testDictionaryRemoveOnOpenDBWriter() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->dictionaryRemove($this->key, $this->recordID);
    }

    public function testDictionaryRemoveOnOpenDBAdmin() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->dictionaryPut($this->key, $this->recordID);
        $record = $this->db->dictionaryRemove($this->key);
        $this->assertAttributeEquals($this->recordID, 'recordID', $record);
        $result = $this->db->dictionaryLookup($this->key);
        $this->assertFalse($result);
    }

    public function testDictionaryRemoveWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->dictionaryRemove();
    }

    public function testDictionaryRemoveWithWrongKey() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $record = $this->db->dictionaryRemove('INVALID');
        $this->assertFalse($record);
    }
}