<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBRecordUpdateTest extends PHPUnit_Framework_TestCase
{

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    protected $db;

    protected $clusterId = 1;

    protected $recordContent = 'testrecord:0';

    protected $recordContentUpd = 'testrecord:1';

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testRecordUpdateOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordUpdate();
    }

    public function testRecordUpdateOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordUpdate();
    }

    public function testRecordUpdateOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordUpdate();
    }

    public function testRecordUpdateOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $record1 = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $version = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContentUpd);
        $this->assertEquals($record1->version + 1, $version);
        $record2 = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version, 'version', $record2);
        $this->assertAttributeEquals($this->recordContentUpd, 'content', $record2);
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordUpdateWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordUpdate($this->clusterId);
    }

    public function testRecordUpdateWithWrongClusterIDOne() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->recordUpdate('INVALID', $this->recordContent);
    }

    public function testRecordUpdateWithWrongClusterIDTwo() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->recordUpdate(':INVALID', $this->recordContent);
    }

    public function testRecordUpdateWithWrongClusterIDThree() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->recordUpdate('INVALID:', $this->recordContent);
    }

    public function testRecordUpdateWithSameType() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent, OrientDB::RECORD_TYPE_BYTES);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContentUpd, 0, OrientDB::RECORD_TYPE_BYTES);
        $this->assertInternalType('integer', $version);
        $record2 = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version, 'version', $record2);
        $this->assertAttributeEquals($this->recordContentUpd, 'content', $record2);
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordUpdateWithTypeBytes() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContentUpd, 0, OrientDB::RECORD_TYPE_BYTES);
        $this->assertInternalType('integer', $version);
        $record2 = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version, 'version', $record2);
        $this->assertAttributeEquals($this->recordContentUpd, 'content', $record2);
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }


    public function testRecordUpdateWithTypeColumn() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContentUpd, 0, OrientDB::RECORD_TYPE_COLUMN);
        $this->assertInternalType('integer', $version);
        $record2 = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version, 'version', $record2);
        $this->assertAttributeEquals($this->recordContentUpd, 'content', $record2);
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordUpdateWithTypeDocument() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContentUpd, 0, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $version);
        $record2 = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version, 'version', $record2);
        $this->assertAttributeEquals($this->recordContentUpd, 'content', $record2);
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordUpdateWithTypeFlat() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContentUpd, 0, OrientDB::RECORD_TYPE_FLAT);
        $this->assertInternalType('integer', $version);
        $record2 = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version, 'version', $record2);
        $this->assertAttributeEquals($this->recordContentUpd, 'content', $record2);
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordUpdateWithWrongType() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $recordPos);
        $this->setExpectedException('OrientDBWrongParamsException');
        $version = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContentUpd, 0, '!');
        $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordUpdateWithPessimisticVersion() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContentUpd);
        $record = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version, 'version', $record);
        $this->assertEquals(1, $version);
        $this->assertAttributeEquals($this->recordContentUpd, 'content', $record);
        $version2 = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContent, -1);
        $record2 = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version2, 'version', $record2);
        $this->assertEquals(2, $version2);
        $this->assertAttributeEquals($this->recordContent, 'content', $record2);
        $result = $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordDeleteWithCorrectVersion() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContentUpd);
        $record = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version, 'version', $record);
        $this->assertEquals(1, $version);
        $this->assertAttributeEquals($this->recordContentUpd, 'content', $record);
        $version2 = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContent, $version);
        $record2 = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version2, 'version', $record2);
        $this->assertEquals(2, $version2);
        $this->assertAttributeEquals($this->recordContent, 'content', $record2);
        $result = $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }

    public function testRecordDeleteWithIncorrectVersion() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterId, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContentUpd);
        $record = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version, 'version', $record);
        $this->assertEquals(1, $version);
        $this->assertAttributeEquals($this->recordContentUpd, 'content', $record);
        $version2 = $this->db->recordUpdate($this->clusterId . ':' . $recordPos, $this->recordContent, $version + 1);
        $record2 = $this->db->recordLoad($this->clusterId . ':' . $recordPos, '');
        $this->assertAttributeEquals($version2, 'version', $record2);
        $this->assertEquals(2, $version2);
        $this->assertAttributeEquals($this->recordContent, 'content', $record2);
        $result = $this->db->recordDelete($this->clusterId  . ':' . $recordPos);
    }
}