<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBRecordDeleteTest extends PHPUnit_Framework_TestCase
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

    public function testRecordDeleteOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordDelete();
    }

    public function testRecordDeleteOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordDelete();
    }

    public function testRecordDeleteOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordDelete();
    }

    public function testRecordDeleteOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $result = $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
        $this->assertTrue($result);
    }

    public function testRecordDeleteWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordDelete();
    }

    public function testRecordDeleteWithWrongRecordIDOne() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordDelete('INVALID');
    }

    public function testRecordDeleteWithWrongRecordIDTwo() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordDelete('INVALID:');
    }

    public function testRecordDeleteWithWrongRecordIDThree() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordDelete(':INVALID');
    }

    public function testRecordDeleteWithPessimisticVersion() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $result = $this->db->recordDelete($this->clusterId  . ':' . $recordPos, -1);
        $this->assertTrue($result);
    }

    public function testRecordDeleteWithCorrectVersion() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $result = $this->db->recordDelete($this->clusterId  . ':' . $recordPos, 0);
        $this->assertTrue($result);
    }

    public function testRecordDeleteWithIncorrectVersion() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $updateVersion = $this->db->recordUpdate($this->clusterId  . ':' . $recordPos, $this->recordContent);
        $this->setExpectedException('OrientDBException');
        $result = $this->db->recordDelete($this->clusterId  . ':' . $recordPos, 0);
        $this->assertFalse($result);
        $result = $this->db->recordDelete($this->clusterId  . ':' . $recordPos, $updateVersion);
        $this->assertTrue($result);
    }
}