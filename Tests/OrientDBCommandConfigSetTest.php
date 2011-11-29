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
 * configSet() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBConfigSetTest extends OrientDB_TestCase
{

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testConfigSetOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->configSet('log.console.level', 'info');
    }

    public function testConfigSetOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $result = $this->db->configSet('log.console.level', 'info');
        $this->assertTrue($result);
    }

    public function testConfigSetOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->configSet('log.console.level', 'info');
    }

    public function testConfigSetOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->configSet('log.console.level', 'info');
    }

    public function testConfigSetWithWrongOption()
    {
        $this->db->connect('root', $this->root_password);
        $result = $this->db->configSet('NONEXISTENT', 'info');
        $this->assertTrue($result);
    }

    public function testConfigSetWithWrongOptionCount()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->configSet();
    }

    public function testConfigSetWithNoValue()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->configSet('log.console.level');
    }

    public function testConfigSetWithWrongValue()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBException');
        $result = $this->db->configSet('log.console.level', 'WRONGVALUE');
    }

    public function testConfigSetWithCorrectValue()
    {
        $this->db->connect('root', $this->root_password);
        $option = 'log.console.level';
        $startvalue = 'info';
        $value = 'warning';
        $result = $this->db->configSet($option, $value);
        $dbvalue = $this->db->configGet($option);
        $this->AssertSame($value, $dbvalue);
        // Return log level to info
        $result = $this->db->configSet($option, 'info');
        $returnvalue = $this->db->configGet($option);
        $this->AssertSame($startvalue, $returnvalue);
    }
}