<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBDataclusterDatarangeTest extends PHPUnit_Framework_TestCase
{

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testDataclusterDatarangeOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterDatarange(1);
    }

    public function testDataclusterDatarangeOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterDatarange(1);
    }

    public function testDataclusterDatarangeOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterDatarange(1);
    }

    public function testDataclusterDatarangeOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result = $this->db->dataclusterDatarange(1);
        $this->assertInternalType('array', $result);
    }

    public function testDataclusterDatarangeWithWrongParamCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterDatarange();
    }

    public function testDataclusterDatarangeWithWrongParamType() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterDatarange('string');
    }

    public function testDataclusterDatarangeOnClusterNotExist() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $result = $this->db->dataclusterDatarange(10000);
    }

}