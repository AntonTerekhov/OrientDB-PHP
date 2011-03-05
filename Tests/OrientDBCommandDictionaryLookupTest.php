<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBDictionaryLookupTest extends OrientDBBaseTesting
{

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testDictionaryLookupOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryLookup();
    }

    public function testDictionaryLookupOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryLookup();
    }

    public function testDictionaryLookupOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryLookup();
    }

    public function testDictionaryLookupOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->dictionaryLookup('key-1');
        $this->assertInstanceOf('OrientDBRecord', $record);
    }

    public function testDictionaryLookupWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->dictionaryLookup();
    }

    public function testDictionaryLookupWithIncorrectKey() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->dictionaryLookup('NONEXIST');
        $this->assertFalse($record);
    }
}