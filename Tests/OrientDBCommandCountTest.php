<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBCountTest extends PHPUnit_Framework_TestCase
{

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    protected $db;

    protected $clustername = 'default';

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testCountOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->count($this->clustername);
    }

    public function testCountOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->count($this->clustername);
    }

    public function testCountOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->count($this->clustername);
    }

    public function testCountOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result = $this->db->count($this->clustername);
        $this->assertInternalType('integer', $result);
    }

    public function testCountWithWrongCluster() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $result = $this->db->count('NONEXISTENT');
    }

    public function testCountWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->count();
    }

    public function testCountWithCorrectValue() {
        $clusters = $this->db->DBOpen('demo', 'writer', 'writer');
        $count = $this->db->count($this->clustername);
        // Find out clusterId
        $clusterId = 0;
        foreach ($clusters['clusters'] as $cluster) {
            if ($cluster->name == $this->clustername) {
                $clusterId = $cluster->id;
                break;
            }
        }
        if ($clusterId == 0) {
            $this->markTestSkipped('No cluster ' . $this->clustername . ' found');
        }
        // Create temporary record
        $id = $this->db->recordCreate($clusterId, 'record');
        $newcount = $this->db->count($this->clustername);
        $this->assertEquals($count + 1, $newcount);
        // Delete temporary record
        $this->db->recordDelete($clusterId . ':' . $id);
        $newcount = $this->db->count($this->clustername);
        $this->assertEquals($count, $newcount);
    }
}