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
 * command() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBCommandTest extends OrientDBBaseTesting
{

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testCommandOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->command('');
    }

    public function testCommandOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->command('');
    }

    public function testCommandOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->command('');
    }

    public function testCommandOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select * from city limit 7');
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
    }

    public function testCommandWithWrongOptionCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->command();
    }

    public function testCommandWithWrongMode()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->command('', 'INVALID');
    }

    public function testCommandWithModeAsync()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select from city traverse( any() )', OrientDB::COMMAND_SELECT_ASYNC);
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
    }

    public function testCommandWithModeAsyncAndFetchPlan()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select from city traverse( any() )', OrientDB::COMMAND_SELECT_ASYNC, '*:-1');
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
    }

    public function testCommandWithModeSync()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select * from [13:1]', OrientDB::COMMAND_SELECT_SYNC);
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
    }

    public function testCommandWithNoRecordsAsync()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select from 11:4 where any() traverse(0,10) (address.city = "Rome")', OrientDB::COMMAND_SELECT_ASYNC);
        $this->assertFalse($records);
    }

    public function testCommandWithNoRecordsSync()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select from 11:4 where any() traverse(0,10) (address.city = "Rome")', OrientDB::COMMAND_SELECT_SYNC);
        $this->assertFalse($records);
    }

    public function testCommandWithModeAsyncAndFetchPlanEmpty()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertEmpty($this->db->cachedRecords);
        $records = $this->db->command('select from city limit 1', OrientDB::COMMAND_SELECT_ASYNC, '*:0');
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
        $this->assertEmpty($this->db->cachedRecords);
    }

    public function testCommandWithModeAsyncAndFetchPlanOneItem()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertEmpty($this->db->cachedRecords);
        $records = $this->db->command('select from city limit 1', OrientDB::COMMAND_SELECT_ASYNC, '*:1');
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
        $this->AssertSame(1, count($this->db->cachedRecords));
        $this->assertInstanceOf('OrientDBRecord', array_pop($this->db->cachedRecords));
        $records = $this->db->command('select from city limit 1', OrientDB::COMMAND_SELECT_ASYNC);
        $this->assertEmpty($this->db->cachedRecords);
    }

    public function testCommandWithModeAsyncAndFetchPlanManyItems()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertEmpty($this->db->cachedRecords);
        $records = $this->db->command('select from city', OrientDB::COMMAND_SELECT_ASYNC, '*:1');
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
        $this->assertGreaterThan(1, count($this->db->cachedRecords));
        $this->assertInstanceOf('OrientDBRecord', array_pop($this->db->cachedRecords));
        $records = $this->db->command('select from city limit 1', OrientDB::COMMAND_SELECT_ASYNC);
        $this->assertEmpty($this->db->cachedRecords);
    }

    public function testCommandWithModeSyncAndFetchPlan()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertEmpty($this->db->cachedRecords);
        $this->setExpectedException('OrientDBWrongParamsException');
        $records = $this->db->command('select from city limit 1', OrientDB::COMMAND_SELECT_SYNC, '*:1');
    }

    public function testCommandInsert()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result = $this->db->command('insert into city (name, country) values ("Moscow", #14:1)', OrientDB::COMMAND_QUERY);
        $this->assertInstanceOf('OrientDBRecord', $result);
    }

    public function testCommandUpdate()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->command('update city set name = "Moscow_" where name = "Moscow"', OrientDB::COMMAND_QUERY);
        $this->assertInternalType('string', $record);
    }

    public function testCommandUpdateZero()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->command('update city set name = "_" where name = "' . microtime(true) . '"', OrientDB::COMMAND_QUERY);
        $this->assertInternalType('string', $record);
    }

    public function testCommandFindReference()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $links = $this->db->command('find references 14:1', OrientDB::COMMAND_QUERY);
        $this->assertInternalType('array', $links);
        $this->assertInstanceOf('OrientDBTypeLink', array_pop($links));
    }

    /**
     * Test 'n' type answer
     */
    public function testCommandCreateIndex()
    {
        $className = 'Foo';
        $propertyName = 'Bar';
        $clusterName = 'testcluster';
        $this->db->DBOpen('demo', 'admin', 'admin');
        $clusterID = $this->db->dataclusterAdd($clusterName, OrientDB::DATACLUSTER_TYPE_PHYSICAL);

        $classID = $this->db->command('CREATE CLASS ' . $className . ' ' . $clusterID, OrientDB::COMMAND_QUERY);
        $this->assertInternalType('string', $classID);
        $propertyResult = $this->db->command('CREATE PROPERTY ' . $className . '.' . $propertyName . ' INTEGER', OrientDB::COMMAND_QUERY);
        $this->assertSame('0', $propertyResult);
        $indexResult = $this->db->command('CREATE INDEX ' . $className . '.' . $propertyName . ' UNIQUE', OrientDB::COMMAND_QUERY);
        $this->assertNull($indexResult);
        $dropResult = $this->db->command('DROP CLASS ' . $className);
        $this->assertFalse($dropResult);

        $this->db->dataclusterRemove($clusterID);
    }

    public function testCommandDelete()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->command('delete from city where name = "moscow_"', OrientDB::COMMAND_QUERY);
        $this->assertInternalType('string', $record);
    }

    public function testCommandDeleteZero()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->command('delete from city where name = "' . microtime(true) . '"', OrientDB::COMMAND_QUERY);
        $this->assertInternalType('string', $record);
    }

    public function testCommandWithModeQueryAndFetchPlan()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $records = $this->db->command('', OrientDB::COMMAND_QUERY, '*:-1');
    }

}