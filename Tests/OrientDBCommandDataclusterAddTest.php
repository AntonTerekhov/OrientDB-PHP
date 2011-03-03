<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBDataclusterAddTest extends PHPUnit_Framework_TestCase
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

    public function testDataclusterAddOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterAdd('name', OrientDB::DATACLUSTER_TYPE_LOGICAL);
    }

    public function testDataclusterAddOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterAdd($this->clusterName, OrientDB::DATACLUSTER_TYPE_LOGICAL);
    }

    public function testDataclusterAddOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterAdd($this->clusterName, OrientDB::DATACLUSTER_TYPE_LOGICAL);
    }

    public function testDataclusterAddOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result = $this->db->dataclusterAdd($this->clusterName, OrientDB::DATACLUSTER_TYPE_LOGICAL);
        $this->assertInternalType('integer', $result);
        $this->db->dataclusterRemove($result);
        $result = $this->db->dataclusterAdd($this->clusterName, OrientDB::DATACLUSTER_TYPE_PHYSICAL);
        $this->assertInternalType('integer', $result);
        $this->db->dataclusterRemove($result);
        $result = $this->db->dataclusterAdd($this->clusterName, OrientDB::DATACLUSTER_TYPE_MEMORY);
        $this->assertInternalType('integer', $result);
        $this->db->dataclusterRemove($result);
        $this->assertInternalType('integer', $result);
    }

    public function testDataclusterAddWithWrongParamCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterAdd('name');
    }

    public function testDataclusterAddWithWrongParamType() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterAdd('name', 'INVALID');
    }


}