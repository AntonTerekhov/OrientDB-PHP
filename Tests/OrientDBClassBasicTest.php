<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBClassBasicTest extends PHPUnit_Framework_TestCase {

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    protected $db;

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    /**
     * @outputBuffering enabled
     */
    public function testDebug() {
        $this->assertFalse($this->db->isDebug());
        $this->db->setDebug(true);
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->recordLoad('1:1');
        $this->assertTrue($this->db->isDebug());
        $this->db->setDebug(false);
        $this->assertFalse($this->db->isDebug());
    }

    public function testMethodNotImplemented() {
    	$this->setExpectedException('OrientDBWrongCommandException');
    	$this->db->methodNotExist();
    }

    public function testProtocolVersion() {
    	$this->setExpectedException('OrientDBException');
    	$this->db->setProtocolVersion(3);
    }

}