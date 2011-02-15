<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBConfigListTest extends PHPUnit_Framework_TestCase
{

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

    public function testConfigListOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->configList();
    }

    public function testConfigListOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $list = $this->db->configList();
        $this->assertInternalType('array', $list);
    }

    public function testConfigListOnNotOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->configList();
    }

    public function testConfigListOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->configList();
    }
}