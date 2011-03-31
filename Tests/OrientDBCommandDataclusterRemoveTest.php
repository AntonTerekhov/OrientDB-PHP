<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBDataclusterRemoveTest extends OrientDBBaseTesting
{

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
        $clusters = $this->db->DBOpen('demo', 'writer', 'writer');
        foreach ($clusters['clusters'] as $cluster) {
            if ($cluster->name === $this->clusterName) {
                $this->db->dataclusterRemove($cluster->id);
            }
        }
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