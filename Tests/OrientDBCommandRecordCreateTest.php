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
 * recordCreate() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBRecordCreateTest extends OrientDB_TestCase
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

    public function testRecordCreateOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordCreate();
    }

    public function testRecordCreateOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordCreate();
    }

    public function testRecordCreateOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->recordCreate();
    }

    public function testRecordCreateOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent);
        $this->assertInternalType('integer', $recordPos);
        $this->db->recordDelete($this->clusterID . ':' . $recordPos);
    }

    public function testRecordCreateWithWrongOptionCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->recordCreate($this->clusterID);
    }

    public function testRecordCreateWithWrongClusterID()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->recordCreate(1000000, 'INVALID');
    }

    public function testRecordCreateWithTypeBytes()
    {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent, OrientDB::RECORD_TYPE_BYTES);
        $this->assertInternalType('integer', $recordPos);
        $this->db->recordDelete($this->clusterID . ':' . $recordPos);
    }

    public function testRecordCreateWithTypeDocument()
    {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent, OrientDB::RECORD_TYPE_DOCUMENT);
        $this->assertInternalType('integer', $recordPos);
        $this->db->recordDelete($this->clusterID . ':' . $recordPos);
    }

    public function testRecordCreateWithTypeFlat()
    {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent, OrientDB::RECORD_TYPE_FLAT);
        $this->assertInternalType('integer', $recordPos);
        $this->db->recordDelete($this->clusterID . ':' . $recordPos);
    }

    public function testRecordCreateWithWrongType()
    {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->setExpectedException('OrientDBWrongParamsException');
        $recordPos = $this->db->recordCreate($this->clusterID, $this->recordContent, '!');
    }

    public function testRecordCreateWithOrientDBRecordType()
    {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $record = new OrientDBRecord();
        $record->data->field = 'value';
        $this->assertNull($record->recordPos);
        $this->assertNull($record->clusterID);
        $this->assertNull($record->recordID);
        $this->assertNull($record->version);

        $recordPos = $this->db->recordCreate($this->clusterID, $record);
        $this->db->recordDelete($this->clusterID . ':' . $recordPos);

        $this->assertInternalType('integer', $recordPos);
        $this->assertSame($recordPos, $record->recordPos);
        $this->assertSame($this->clusterID, $record->clusterID);
        $this->assertSame($this->clusterID . ':' . $recordPos, $record->recordID);
        $this->assertInternalType('integer', $record->version);
    }
}