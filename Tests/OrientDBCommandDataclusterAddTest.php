<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

/**
 * dataclusterAdd() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBDataclusterAddTest extends OrientDBBaseTesting
{

    protected $clusterName = 'testdatacluster';

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testDataclusterAddOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterAdd('name', OrientDB::DATACLUSTER_TYPE_LOGICAL);
    }

    public function testDataclusterAddOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterAdd($this->clusterName, OrientDB::DATACLUSTER_TYPE_LOGICAL);
    }

    public function testDataclusterAddOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterAdd($this->clusterName, OrientDB::DATACLUSTER_TYPE_LOGICAL);
    }

    public function testDataclusterAddOnOpenDB()
    {
        $clusters = $this->db->DBOpen('demo', 'writer', 'writer');
        foreach ($clusters['clusters'] as $cluster) {
            if ($cluster->name === $this->clusterName) {
                $this->db->dataclusterRemove($cluster->id);
            }
        }
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

    public function testDataclusterAddWithWrongParamCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterAdd('name');
    }

    public function testDataclusterAddWithWrongParamType()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterAdd('name', 'INVALID');
    }

}