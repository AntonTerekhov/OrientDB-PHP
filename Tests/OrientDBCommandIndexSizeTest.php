<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBindexSizeTest extends OrientDBBaseTesting
{

    protected $key = 'testkey';

    protected $recordID = '1:1';

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testindexSizeOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexSize();
    }

    public function testindexSizeOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexSize();
    }

    public function testindexSizeOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexSize();
    }

    public function testindexSizeOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result = $this->db->indexSize();
        $this->assertInternalType('integer', $result);
    }

    public function testindexSizeWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'admin', 'admin');
        $result1 = $this->db->indexSize();
        $record = $this->db->indexPut($this->key, $this->recordID);
        $result2 = $this->db->indexSize();
        $record = $this->db->indexRemove($this->key);
        $result3 = $this->db->indexSize();
        $this->AssertSame($result1 + 1, $result2);
        $this->AssertSame($result1, $result3);
    }

}