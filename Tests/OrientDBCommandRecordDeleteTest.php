<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDB_TestCase.php';

/**
 * recordDelete() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBRecordDeleteTest extends OrientDB_TestCase
{

    protected $clusterID = 2;

    protected $recordContent = 'testrecord:0';

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testRecordDeleteOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordDelete();
    }

    public function testRecordDeleteOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordDelete();
    }

    public function testRecordDeleteOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordDelete();
    }

    public function testRecordDeleteOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $result = $this->db->recordDelete($this->clusterID . ':' . $recordPos);
        $this->assertTrue($result);
    }

    public function testRecordDeleteWithWrongOptionCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordDelete();
    }

    public function testRecordDeleteWithWrongRecordIDOne()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordDelete('INVALID');
    }

    public function testRecordDeleteWithWrongRecordIDTwo()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordDelete('INVALID:');
    }

    public function testRecordDeleteWithWrongRecordIDThree()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordDelete(':INVALID');
    }

    public function testRecordDeleteWithWrongRecordIDFour()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordDelete('1:INVALID');
    }

    public function testRecordDeleteWithRecordPosZero()
    {
        $dbName = 'RecordZeroTest';
        $clusterName = 'test';
        $recordContent = 'testrecord:0';
        $this->db->connect('root', $this->root_password);
        if ($this->db->DBExists($dbName)) {
            $this->db->DBDelete($dbName);
        }
        $result = $this->db->DBCreate($dbName, OrientDB::DB_TYPE_LOCAL);
        $this->assertTrue($result);
        $this->db->DBOpen($dbName, 'admin', 'admin');
        $clusterID = $this->db->dataclusterAdd($clusterName, OrientDB::DATACLUSTER_TYPE_PHYSICAL);
        $this->assertInternalType('integer', $clusterID);
        $recordPos = $this->db->recordCreate($clusterID, $recordContent);
        $this->AssertSame(0, $recordPos);
        $result = $this->db->recordDelete($clusterID . ':' . $recordPos);
        $this->assertTrue($result);
        $this->db->DBDelete($dbName);
    }

    public function testRecordDeleteWithNonExistentRecordID()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recPos = $this->db->recordCreate($this->clusterID, 'name:"test"');
        $result = $this->db->recordDelete($this->clusterID . ':' . $recPos);
        $this->assertTrue($result);
        $result = $this->db->recordDelete($this->clusterID . ':' . $recPos);
        $this->assertFalse($result);
    }

    public function testRecordDeleteWithPessimisticVersion()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $result = $this->db->recordDelete($this->clusterID . ':' . $recordPos, -1);
        $this->assertTrue($result);
    }

    public function testRecordDeleteWithCorrectVersion()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = new OrientDBRecord();
        $record->content = $this->recordContent;
        $recordPos = $this->db->recordCreate($this->clusterID, $record);
        $this->assertInternalType('integer', $recordPos);
        $result = $this->db->recordDelete($this->clusterID . ':' . $recordPos, $record->version);
        $this->assertTrue($result);
    }

    public function testRecordDeleteWithIncorrectVersionIsLesser()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $updateVersion = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContent);
        $this->setExpectedException('OrientDBException');
        $result = $this->db->recordDelete($this->clusterID . ':' . $recordPos, 0);
        $this->assertFalse($result);
        $result = $this->db->recordDelete($this->clusterID . ':' . $recordPos, $updateVersion);
        $this->assertTrue($result);
    }

    public function testRecordDeleteWithIncorrectVersionIsGreater()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $updateVersion = $this->db->recordUpdate($this->clusterID . ':' . $recordPos, $this->recordContent);
        $this->setExpectedException('OrientDBException');
        $result = $this->db->recordDelete($this->clusterID . ':' . $recordPos, $updateVersion + 1);
        $this->assertFalse($result);
        $result = $this->db->recordDelete($this->clusterID . ':' . $recordPos, $updateVersion);
        $this->assertTrue($result);
    }
}