<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBIndexRemoveTest extends OrientDBBaseTesting
{

    protected $key = 'testkey';

    protected $recordID = '1:1';

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testIndexRemoveOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexRemove();
    }

    public function testIndexRemoveOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexRemove();
    }

    public function testIndexRemoveOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexRemove();
    }

    public function testIndexRemoveOnOpenDBWriter()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $record = $this->db->indexRemove($this->key, $this->recordID);
    }

    public function testIndexRemoveOnOpenDBAdmin()
    {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->indexPut($this->key, $this->recordID);
        $record = $this->db->indexRemove($this->key);
        $this->AssertSame($this->recordID, $record->recordID);
        $result = $this->db->indexLookup($this->key);
        $this->assertFalse($result);
    }

    public function testIndexRemoveWithWrongOptionCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->indexRemove();
    }

    public function testIndexRemoveWithWrongKey()
    {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $record = $this->db->indexRemove('INVALID');
        $this->assertFalse($record);
    }
}