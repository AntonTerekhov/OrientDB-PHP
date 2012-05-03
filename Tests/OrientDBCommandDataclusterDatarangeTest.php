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
 * dataclusterDatarange() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBDataclusterDatarangeTest extends OrientDB_TestCase
{
    protected $clusterID = 2;

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testDataclusterDatarangeOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterDatarange($this->clusterID);
    }

    public function testDataclusterDatarangeOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterDatarange($this->clusterID);
    }

    public function testDataclusterDatarangeOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterDatarange($this->clusterID);
    }

    public function testDataclusterDatarangeOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result = $this->db->dataclusterDatarange($this->clusterID);
        $this->assertInternalType('array', $result);
    }

    public function testDataclusterDatarangeWithWrongParamCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterDatarange();
    }

    public function testDataclusterDatarangeWithWrongParamType()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterDatarange('string');
    }

    public function testDataclusterDatarangeOnClusterNotExist()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $result = $this->db->dataclusterDatarange(10000);
    }

}