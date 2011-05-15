<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBClassBasicTest extends OrientDBBaseTesting
{

    protected function setUp()
    {
        $this->db = new OrientDB('localhost');
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    /**
     * @outputBuffering enabled
     */
    public function testDebug()
    {
        $this->assertFalse($this->db->isDebug());
        $this->db->setDebug(true);
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->recordLoad('1:1');
        $this->assertTrue($this->db->isDebug());
        $this->db->setDebug(false);
        $this->assertFalse($this->db->isDebug());
    }

    public function testMethodNotImplemented()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $this->db->methodNotExist();
    }

    public function testProtocolVersion()
    {
        $this->setExpectedException('OrientDBException');
        $this->db->setProtocolVersion(1);
    }

}