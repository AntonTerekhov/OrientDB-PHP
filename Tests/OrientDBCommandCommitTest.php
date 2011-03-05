<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBCommitTest extends OrientDBBaseTesting
{

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testRecordCreateOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->commit();
    }

    public function testRecordCreateOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->commit();
    }

    public function testRecordCreateOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->commit();
    }

    public function testRecordCreateOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->markTestSkipped('Not implemented');
        $recordPos = $this->db->commit();
    }

}