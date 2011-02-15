<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBCommandsBasicTest extends PHPUnit_Framework_TestCase {

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

    public function testConnectWithCorrectUserPassword() {
        $this->assertTrue($this->db->connect('root', $this->root_password));
    }

    public function testConnectWithIncorrectUserPassword() {
        $this->setExpectedException('OrientDBException');
        $this->db->connect('toor', $this->root_password);
    }

    public function testConnectOnAlreadyConnectedDB() {
        $result = $this->db->connect('root', $this->root_password);
        $this->assertTrue($this->db->connect('root', $this->root_password));
    }

    public function testOpenDBWithCorrectUserPassword() {
        $clusters = $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertInternalType( 'array', $clusters);
    }

    public function testOpenDBWithIncorrectUserPassword() {
        $this->setExpectedException('OrientDBException');
        $clusters = $this->db->DBOpen('demo', 'writer', 'INCORRECT');
    }

    public function testOpenDBWithNonExistentDB() {
        $this->setExpectedException('OrientDBException');
        $clusters = $this->db->DBOpen('NONEXISTENT', 'writer', 'writer');
    }

    public function testConnectOnAlreadyOpenedDB() {
        $clusters = $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertTrue($this->db->connect('root', $this->root_password));
    }

    public function testOpenDBOnAlreadyConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $clusters = $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertInternalType( 'array', $clusters);
    }

    public function testOpenDBOnAlreadyOpenedDB() {
        $clusters1 = $this->db->DBOpen('demo', 'writer', 'writer');
        $clusters2 = $this->db->DBOpen('demo', 'admin', 'admin');
        $this->assertInternalType( 'array', $clusters2);
    }

    public function testCloseDBOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $this->db->setDebug(true);
        $this->db->DBClose();
    }

    public function testCloseDBOnNotOpenedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $this->db->DBClose();
    }

    public function testCloseDBOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $this->db->DBClose();
    }

    public function testCloseDBOnOpenedDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->db->DBClose();
        $this->assertAttributeEmpty('socket', $this->db);
    }

    public function testAnyCommnandAfterDBClose() {
    	$this->db->DBOpen('demo', 'writer', 'writer');
        $this->db->DBClose();
        $this->setExpectedException('OrientDBWrongCommandException');
        $this->db->DBOpen('demo', 'writer', 'writer');
    }
}