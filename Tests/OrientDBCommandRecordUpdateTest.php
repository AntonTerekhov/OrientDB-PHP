<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBRecordUpdateTest extends OrientDBBaseTesting
{

    protected $clusterID = 1;

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
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $record1 = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $version = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContentUpd);
        $this->AssertSame($record1->version + 1, $version);
        $record2 = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version, $record2->version);
        $this->AssertSame($this->recordContentUpd, $record2->content);
        $this->db->recordDelete($this->clusterID  . ':' . $recordPos);
    }

    public function testRecordUpdateWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordUpdate($this->clusterID);
    }

    public function testRecordUpdateWithWrongRecordIDOne() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->recordUpdate('INVALID', $this->recordContent);
    }

    public function testRecordUpdateWithWrongRecordIDTwo() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->recordUpdate(':INVALID', $this->recordContent);
    }

    public function testRecordUpdateWithWrongRecordIDThree() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->recordUpdate('INVALID:', $this->recordContent);
    }

    public function testRecordUpdateWithWrongRecordIDFour() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->recordUpdate('1:INVALID', $this->recordContent);
    }

    public function testRecordUpdateWithRecordPosZero() {
        $recordPos = 0;
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $version = $this->db->recordUpdate($this->clusterID .':' . $recordPos, $record->content);
        $record2 = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version, $record2->version);
    }

    public function testRecordUpdateWithSameType() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent, OrientDB::RECORD_TYPE_BYTES);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContentUpd, 0, OrientDB::RECORD_TYPE_BYTES);
        $this->assertInternalType('integer', $version);
        $record2 = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version, $record2->version);
        $this->AssertSame($this->recordContentUpd, $record2->content);
        $this->db->recordDelete($this->clusterID  . ':' . $recordPos);
    }

    public function testRecordUpdateWithTypeBytes() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContentUpd, 0, OrientDB::RECORD_TYPE_BYTES);
        $this->assertInternalType('integer', $version);
        $record2 = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version, $record2->version);
        $this->AssertSame($this->recordContentUpd, $record2->content);
        $this->db->recordDelete($this->clusterID  . ':' . $recordPos);
    }

    public function testRecordUpdateWithTypeDocument() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContentUpd, 0, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $version);
        $record2 = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version, $record2->version);
        $this->AssertSame($this->recordContentUpd, $record2->content);
        $this->db->recordDelete($this->clusterID  . ':' . $recordPos);
    }

    public function testRecordUpdateWithTypeFlat() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContentUpd, 0, OrientDB::RECORD_TYPE_FLAT);
        $this->assertInternalType('integer', $version);
        $record2 = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version, $record2->version);
        $this->AssertSame($this->recordContentUpd, $record2->content);
        $this->db->recordDelete($this->clusterID  . ':' . $recordPos);
    }

    public function testRecordUpdateWithWrongType() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $recordPos);
        $this->setExpectedException('OrientDBWrongParamsException');
        $version = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContentUpd, 0, '!');
        $this->db->recordDelete($this->clusterID  . ':' . $recordPos);
    }

    public function testRecordUpdateWithPessimisticVersion() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContentUpd);
        $record = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version, $record->version);
        $this->AssertSame(1, $version);
        $this->AssertSame($this->recordContentUpd, $record->content);
        $version2 = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContent, -1);
        $record2 = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version2, $record2->version);
        $this->AssertSame(2, $version2);
        $this->AssertSame($this->recordContent, $record2->content);
        $result = $this->db->recordDelete($this->clusterID  . ':' . $recordPos);
    }

    public function testRecordDeleteWithCorrectVersion() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContentUpd);
        $record = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version, $record->version);
        $this->AssertSame(1, $version);
        $this->AssertSame($this->recordContentUpd, $record->content);
        $version2 = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContent, $version);
        $record2 = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version2, $record2->version);
        $this->AssertSame(2, $version2);
        $this->AssertSame($this->recordContent, $record2->content);
        $result = $this->db->recordDelete($this->clusterID  . ':' . $recordPos);
    }

    public function testRecordDeleteWithIncorrectVersion() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $version = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContentUpd);
        $record = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version, $record->version);
        $this->AssertSame(1, $version);
        $this->AssertSame($this->recordContentUpd, $record->content);
        $version2 = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContent, $version + 1);
        $record2 = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->AssertSame($version2, $record2->version);
        $this->AssertSame(2, $version2);
        $this->AssertSame($this->recordContent, $record2->content);
        $result = $this->db->recordDelete($this->clusterID  . ':' . $recordPos);
    }
}