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
 * recordLoad() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBRecordLoadTest extends OrientDB_TestCase
{

    protected $clusterID = 2;

    protected $addressClusterID = 16;

    protected $recordContent = 'testrecord:0';

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testRecordLoadOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordLoad();
    }

    public function testRecordLoadOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordLoad();
    }

    public function testRecordLoadOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordLoad();
    }

    public function testRecordLoadOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $record = $this->db->recordLoad($this->clusterID . ':' . $recordPos);
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->AssertSame($this->recordContent, $record->content);
        $this->AssertSame($this->clusterID . ':' . $recordPos, $record->recordID);
        $this->assertSame($this->clusterID, $record->clusterID);
        $this->assertSame($recordPos, $record->recordPos);
        $result = $this->db->recordDelete($this->clusterID . ':' . $recordPos);
        $this->assertTrue($result);
    }

    public function testRecordLoadWithWrongOptionCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordLoad();
    }

    public function testRecordLoadWithWrongRecordIDOne()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordLoad('INVALID', '');
    }

    public function testRecordLoadWithWrongRecordIDTwo()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordLoad('INVALID:', '');
    }

    public function testRecordLoadWithWrongRecordIDThree()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordLoad(':INVALID', '');
    }

    public function testRecordLoadWithWrongRecordIDFour()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordLoad('1:INVALID', '');
    }

    public function testRecordLoadWithRecordPosZero()
    {
        $recordPos = 0;
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->recordLoad($this->clusterID . ':' . $recordPos, '');
        $this->assertInstanceOf('OrientDBRecord', $record);
    }

    public function testRecordLoadWithDeletedRecordId()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $result = $this->db->recordDelete($this->clusterID . ':' . $recordPos);
        $this->assertTrue($result);
        $record = $this->db->recordLoad($this->clusterID . ':' . $recordPos);
        $this->assertFalse($record);
    }

    public function testRecordLoadWithOutOfBoundsRecordId()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->recordLoad($this->clusterID . ':' . 1000000, '');
        $this->assertFalse($record);
    }

    public function testRecordLoadWithFetchPlan()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        // Load record Address:100
        $record = $this->db->recordLoad($this->addressClusterID . ':' . 100, '*:-1');
        $this->assertInstanceOf('OrientDBRecord', $record);
    }

    public function testRecordLoadWithFetchPlanAnyOneItem()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        // Load record Address:1
        $this->assertEmpty($this->db->cachedRecords);
        $record = $this->db->recordLoad($this->addressClusterID . ':' . 100, '*:1');
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->AssertSame(1, count($this->db->cachedRecords));
        $record = $this->db->recordLoad($this->addressClusterID . ':' . 100, '*:0');
        $this->assertEmpty($this->db->cachedRecords);
    }

    public function testRecordLoadWithFetchPlanAnyManyItems()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        // Load record Address:1
        $this->assertEmpty($this->db->cachedRecords);
        $record = $this->db->recordLoad($this->addressClusterID . ':' . 100, '*:2');
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->AssertSame(2, count($this->db->cachedRecords));
        $record = $this->db->recordLoad($this->addressClusterID . ':' . 100, '*:0');
        $this->assertEmpty($this->db->cachedRecords);
    }

    public function testRecordLoadWithFetchPlanFieldOneItem()
    {
        $info = $this->db->DBOpen('demo', 'writer', 'writer');
        foreach ($info['clusters'] as $cluster) {
            if ($cluster->name === 'city') {
                $cityClusterID = $cluster->id;
            }
        }
        // Load record City:1
        $this->assertEmpty($this->db->cachedRecords);
        $record = $this->db->recordLoad($cityClusterID . ':' . 1, 'country:1');
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->AssertSame(1, count($this->db->cachedRecords));
        $record = $this->db->recordLoad($cityClusterID . ':' . 1, '*:0');
        $this->assertEmpty($this->db->cachedRecords);
    }

    public function testRecordLoadWithFetchPlanInvalidFieldOneItem()
    {
        $info = $this->db->DBOpen('demo', 'writer', 'writer');
        foreach ($info['clusters'] as $cluster) {
            if ($cluster->name === 'address') {
                $addressClusterID = $cluster->id;
            }
        }
        // Load record Address:1. Note, that Address hasn't field country
        $this->assertEmpty($this->db->cachedRecords);
        $record = $this->db->recordLoad($addressClusterID . ':' . 1, 'country:1');
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->AssertEmpty($this->db->cachedRecords);
    }

    public function testRecordLoadWithIncorrectPlan()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordsToFetch = array('1:0', '1:1', '1:2', '2:0', '2:1', '2:2', '3:0', '3:1', '3:2', '4:0', '4:1', '4:2');
        $failedCnt = 0;
        $failedRIDs = array();

        foreach ($recordsToFetch as $RID) {
            try {
                $record = $this->db->recordLoad($RID, 'INCORRECT');
            }
            catch (OrientDBException $e) {
                $failedCnt++;
                $failedRIDs[] = $RID;
            }
        }
        if ($failedCnt != count($recordsToFetch)) {
            $passedRIDs = array_diff($recordsToFetch, $failedRIDs);
            $this->fail('Invalid fetchplan exception not thrown on: ' . join(', ', $passedRIDs));
        }
        $this->assertSame(count($recordsToFetch), $failedCnt);
    }

    public function testRecordLoadFromZeroClusterPosZero()
    {
        $rid = '0:0';
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->recordLoad($rid);
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->assertSame($rid, $record->recordID);
        $this->assertNotEmpty($record->data);
    }

    public function testRecordLoadFromZeroClusterPosOne()
    {
        $rid = '0:1';
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->recordLoad($rid);
        $this->assertInstanceOf('OrientDBRecord', $record);
        $this->assertSame($rid, $record->recordID);
        $this->assertNotEmpty($record->data->indexes);
    }
}