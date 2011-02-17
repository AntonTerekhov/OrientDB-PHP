<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBDBExistsTest extends PHPUnit_Framework_TestCase
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

    public function testDBExistsOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBExists();
    }

    public function testDBExistsOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->markTestSkipped('java.lang.NullPointerException');
        $result = $this->db->DBExists();
        $this->assertTrue($result);
    }

    public function testDBExistsOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBExists();
    }

    public function testDBExistsOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBExists();
    }
}