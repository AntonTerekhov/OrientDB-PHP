<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBDictionaryKeysTest extends OrientDBBaseTesting
{

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testDictionaryKeysOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryKeys();
    }

    public function testDictionaryKeysOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryKeys();
    }

    public function testDictionaryKeysOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->dictionaryKeys();
    }

    public function testDictionaryKeysOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $list = $this->db->dictionaryKeys();
        $this->assertInternalType('array', $list);
    }
}