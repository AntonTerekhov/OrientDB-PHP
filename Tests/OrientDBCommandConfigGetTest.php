<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBConfigGetTest extends OrientDBBaseTesting
{

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testConfigGetOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $value = $this->db->configGet('log.console.level');
    }

    public function testConfigGetOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $value = $this->db->configGet('log.console.level');
        $this->assertInternalType('string', $value);
    }

    public function testConfigGetOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $value = $this->db->configGet('log.console.level');
    }

    public function testConfigGetOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $value = $this->db->configGet('log.console.level');
    }

    public function testConfigGetWithWrongOption() {
        $this->db->connect('root', $this->root_password);
        $value = $this->db->configGet('NONEXISTENT');
        $this->assertEmpty($value);
    }

    public function testConfigGetWithWrongOptionCount() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $value = $this->db->configGet();

    }
}