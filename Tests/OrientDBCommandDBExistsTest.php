<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBDBExistsTest extends OrientDBBaseTesting
{

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testDBExistsOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBExists();
    }

    public function testDBExistsOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBExists();
    }

    public function testDBExistsOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBExists();
    }

    public function testDBExistsOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result = $this->db->DBExists();
        $this->assertTrue($result);
    }
}