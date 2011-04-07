<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBConfigListTest extends OrientDBBaseTesting
{

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testConfigListOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->configList();
    }

    public function testConfigListOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $list = $this->db->configList();
        $this->assertInternalType('array', $list);
    }

    public function testConfigListOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->configList();
    }

    public function testConfigListOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->configList();
    }
}