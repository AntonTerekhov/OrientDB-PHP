<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBDataclusterRemoveTest extends PHPUnit_Framework_TestCase
{

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    protected $clusterName = 'testdatacluster';

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testDataclusterRemoveOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterRemove(10000);
    }

    public function testDataclusterRemoveOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterRemove(10000);
    }

    public function testDataclusterRemoveOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterRemove();
    }

    public function testDataclusterRemoveOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result = $this->db->dataclusterAdd($this->clusterName, OrientDB::DATACLUSTER_TYPE_LOGICAL);
        $this->assertInternalType('integer', $result);
        $result = $this->db->dataclusterRemove($result);
        $this->assertInternalType('boolean', $result);
    }

    public function testDataclusterRemoveWithWrongParamCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterRemove();
    }

    public function testDataclusterRemoveWithWrongParamType() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterRemove('INVALID');
    }

    public function testDataclusterRemoveOnClusterNotExist() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $result = $this->db->dataclusterRemove(10000);
    }
}