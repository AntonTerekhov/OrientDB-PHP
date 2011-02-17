<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBDictionaryLookupTest extends PHPUnit_Framework_TestCase
{

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    protected $db;

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