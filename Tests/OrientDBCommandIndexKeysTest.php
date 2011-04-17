<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBIndexKeysTest extends OrientDBBaseTesting
{

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testIndexKeysOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexKeys();
    }

    public function testIndexKeysOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexKeys();
    }

    public function testIndexKeysOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexKeys();
    }

    public function testIndexKeysOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->markTestSkipped('Temporary skip test on x64');
        $list = $this->db->indexKeys();
        $this->assertInternalType('array', $list);
    }
}