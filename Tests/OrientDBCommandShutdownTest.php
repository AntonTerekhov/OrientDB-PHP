<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBShutdownTest extends OrientDBBaseTesting
{

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testShutdownOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $value = $this->db->shutdown('', '');
    }

    public function testshutdownOnConnectedDB() {
        $this->markTestSkipped('Skipping shutdown');
        $this->db->connect('root', $this->root_password);
        $this->db->shutdown('root', $this->root_password);
    }

    public function testshutdownOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $value = $this->db->shutdown('', '');
    }

    public function testshutdownOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $value = $this->db->shutdown('', '');
    }

    public function testshutdownWithWrongPermissions() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBException');
        $value = $this->db->shutdown('root', '');

    }

    public function testshutdownWithWrongOptionCount() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $value = $this->db->shutdown();

    }
}