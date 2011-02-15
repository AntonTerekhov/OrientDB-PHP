<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBConfigSetTest extends PHPUnit_Framework_TestCase
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

    public function testConfigSetOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->configSet('log.console.level', 'info');
    }

    public function testConfigSetOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $result = $this->db->configSet('log.console.level', 'info');
        $this->assertTrue($result);
    }

    public function testConfigSetOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->configSet('log.console.level', 'info');
    }

    public function testConfigSetOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->configSet('log.console.level', 'info');
    }

    public function testConfigSetWithWrongOption() {
        $this->db->connect('root', $this->root_password);
        $result = $this->db->configSet('NONEXISTENT', 'info');
        $this->assertTrue($result);
    }

    public function testConfigSetWithWrongOptionCount() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->configSet();
    }

    public function testConfigSetWithNoValue() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->configSet('log.console.level');
    }

    public function testConfigSetWithWrongValue() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBException');
        $result = $this->db->configSet('log.console.level', 'WRONGVALUE');
    }

    public function testConfigSetWithCorrectValue() {
        $this->db->connect('root', $this->root_password);
        $option = 'log.console.level';
        $value = 'warning';
        $result = $this->db->configSet($option, $value);
        $dbvalue = $this->db->configGet($option);
        $this->assertEquals($value, $dbvalue);
        // Return log level to info
        $result = $this->db->configSet($option, 'info');
    }
}