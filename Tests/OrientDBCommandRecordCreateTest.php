<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBRecordCreateTest extends PHPUnit_Framework_TestCase
{

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    protected $db;

    protected $clusterId = 1;

    protected $recordContent = 'testrecord:0';

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testRecordCreateOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordCreate();
    }

    public function testRecordCreateOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordCreate();
    }

    public function testRecordCreateOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordCreate();
    }

    public function testRecordCreateOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordCreateWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordCreate($this->clusterId);
    }

    public function testRecordCreateWithWrongClusterID() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->recordCreate(1000000, 'INVALID');
    }

    public function testRecordCreateWithTypeBytes() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent, OrientDB::RECORD_TYPE_BYTES);
        $this->assertInternalType('integer', $recordPos);
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordCreateWithTypeColumn() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent, OrientDB::RECORD_TYPE_COLUMN);
        $this->assertInternalType('integer', $recordPos);
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordCreateWithTypeDocument() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $recordPos);
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordCreateWithTypeFlat() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent, OrientDB::RECORD_TYPE_FLAT);
        $this->assertInternalType('integer', $recordPos);
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordCreateWithWrongType() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->setExpectedException('OrientDBWrongParamsException');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent, '!');
    }
}