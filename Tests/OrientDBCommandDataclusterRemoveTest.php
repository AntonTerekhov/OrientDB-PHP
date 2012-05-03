<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDB_TestCase.php';

/**
 * dataclusterRemove() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBDataclusterRemoveTest extends OrientDB_TestCase
{

    protected $clusterName;

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
        $this->clusterName = 'testdataclusterremove_' . rand(10, 99);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testDataclusterRemoveOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterRemove(10000);
    }

    public function testDataclusterRemoveOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterRemove(10000);
    }

    public function testDataclusterRemoveOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterRemove();
    }

    public function testDataclusterRemoveOnOpenDBAdmin()
    {
        $clusters = $this->db->DBOpen('demo', 'admin', 'admin');
        foreach ($clusters['clusters'] as $cluster) {
            if ($cluster->name === $this->clusterName) {
                $this->db->dataclusterRemove($cluster->id);
            }
        }
        $result = $this->db->dataclusterAdd($this->clusterName, OrientDB::DATACLUSTER_TYPE_PHYSICAL);
        $this->assertInternalType('integer', $result);
        $result = $this->db->dataclusterRemove($result);
        $this->assertInternalType('boolean', $result);
    }

//    As of r3013 Its still possible to remove datacluster with user 'writer'. Fixer in r3030
    public function testDataclusterRemoveOnOpenDBWriter()
    {
        $clusters = $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $result = $this->db->dataclusterRemove(1);
    }

    public function testDataclusterRemoveWithWrongParamCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterRemove();
    }

    public function testDataclusterRemoveWithWrongParamType()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterRemove('INVALID');
    }

    public function testDataclusterRemoveOnClusterNotExist()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $result = $this->db->dataclusterRemove(10000);
    }
}