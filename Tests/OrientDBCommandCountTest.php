<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBCountTest extends OrientDBBaseTesting
{

    protected $clustername = 'default';

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testCountOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->count($this->clustername);
    }

    public function testCountOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->count($this->clustername);
    }

    public function testCountOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->count($this->clustername);
    }

    public function testCountOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result = $this->db->count($this->clustername);
        $this->assertInternalType('integer', $result);
    }

    public function testCountWithWrongCluster()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $result = $this->db->count('NONEXISTENT');
    }

    public function testCountWithWrongOptionCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->count();
    }

    public function testCountWithCorrectValue()
    {
        $clusters = $this->db->DBOpen('demo', 'writer', 'writer');
        $count = $this->db->count($this->clustername);
        // Find out clusterID
        $clusterID = 0;
        foreach ($clusters['clusters'] as $cluster) {
            if ($cluster->name == $this->clustername) {
                $clusterID = $cluster->id;
                break;
            }
        }
        if ($clusterID == 0) {
            $this->markTestSkipped('No cluster ' . $this->clustername . ' found');
        }
        // Create temporary record
        $id = $this->db->recordCreate($clusterID, 'record');
        $newcount = $this->db->count($this->clustername);
        $this->AssertSame($count + 1, $newcount);
        // Delete temporary record
        $this->db->recordDelete($clusterID . ':' . $id);
        $newcount = $this->db->count($this->clustername);
        $this->AssertSame($count, $newcount);
    }
}