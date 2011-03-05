<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBDictionarySizeTest extends OrientDBBaseTesting
{

    protected $key = 'testkey';

    protected $recordID = '1:1';

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testDictionarySizeOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionarySize();
    }

    public function testDictionarySizeOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionarySize();
    }

    public function testDictionarySizeOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionarySize();
    }

    public function testDictionarySizeOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result = $this->db->dictionarySize();
        $this->assertInternalType('integer', $result);
    }

    public function testDictionarySizeWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $result1 = $this->db->dictionarySize();
        $record = $this->db->dictionaryPut($this->key, $this->recordID);
        $result2 = $this->db->dictionarySize();
        $record = $this->db->dictionaryRemove($this->key);
        $result3 = $this->db->dictionarySize();
        $this->assertEquals($result1 + 1, $result2);
        $this->assertEquals($result1, $result3);
    }

}