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
 * indexLookup() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBIndexLookupTest extends OrientDBBaseTesting
{

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testIndexLookupOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexLookup();
    }

    public function testIndexLookupOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexLookup();
    }

    public function testIndexLookupOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->indexLookup();
    }

    public function testIndexLookupOnOpenDB()
    {
        $key = 'testkey';
        $this->db->DBOpen('demo', 'admin', 'admin');
        $this->db->indexPut($key, '13:1');
        $db = new OrientDB('localhost', 2424);
        $db->DBOpen('demo', 'writer', 'writer');
        $record = $db->indexLookup($key);
        $result = $this->db->indexRemove($key);
        $this->assertInstanceOf('OrientDBRecord', $record);

    }

    public function testIndexLookupWithWrongOptionCount()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->indexLookup();
    }

    public function testIndexLookupWithIncorrectKey()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $record = $this->db->indexLookup('NONEXIST');
        $this->assertFalse($record);
    }
}