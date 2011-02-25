<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBShutdownTest extends PHPUnit_Framework_TestCase
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
        $this->setExpectedException('OrientDBException');
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