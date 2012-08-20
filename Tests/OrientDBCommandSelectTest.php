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
 * select() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBSelectTest extends OrientDB_TestCase
{

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testSelectOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->select('');
    }

    public function testSelectOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->select('');
    }

    public function testSelectOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->select('');
    }

    public function testSelectOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->select('select * from city limit 7');
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
    }

    public function testSelectWithWrongOptionCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->select();
    }

    public function testSelectWithFetchPlan()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertEmpty($this->db->cachedRecords);
        $this->setExpectedException('OrientDBWrongParamsException');
        $records = $this->db->select('select from city limit 1', '*:1');
    }

    public function testSelectWithNoRecordsSync()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->select('select from 11:4 where any() traverse(0,10) (address.city = "Rome")');
        $this->assertFalse($records);
    }

    public function testFieldsSelect()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->select('SELECT name FROM City WHERE name = "Rome" LIMIT 1');
        $this->assertInternalType('array', $records);
        $record = reset($records);
        $this->assertSame($record->data->name, 'Rome');
        $this->assertSame(-2, $record->clusterID);
        $this->assertSame(0, $record->recordPos);
        $this->assertNull($record->recordID);
    }

    public function testFieldsSelectWithRid()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->select('SELECT name, @rid FROM City WHERE name = "Rome" LIMIT 1');
        $record = reset($record);
        $this->assertSame('Rome', $record->data->name);
        $this->assertSame('#18:0', (string) $record->data->rid);
        $this->assertSame(-2, $record->clusterID);
        $this->assertSame(0, $record->recordPos);
        $this->assertNull($record->recordID);
    }
}