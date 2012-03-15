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
 * DBDelete() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBDBDeleteTest extends OrientDB_TestCase
{

    protected $dbName = 'unittest_';

    protected static $dbSequence = 0;

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    protected function sequenceInc()
    {
        self::$dbSequence++;
    }

    protected function getDBName()
    {
        return $this->dbName . self::$dbSequence;
    }

    public function testDBCreateOnNotConnectedDB()
    {
        $this->sequenceInc();
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBDelete($this->getDBName());
    }

    public function testDBDeleteOnConnectedDB()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $this->assertTrue($result);
        $result = $this->db->DBDelete($this->getDBName());
        $this->assertTrue($result);
    }

    public function testDBDeleteOnNotOpenDB()
    {
        $this->sequenceInc();
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBDelete($this->getDBName());
    }

    public function testDBDeleteOnOpenDB()
    {
        $this->sequenceInc();
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBDelete($this->getDBName());
    }

    public function testDBDeleteWithTypeMemory()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $result = $this->db->DBDelete($this->getDBName());
        $this->assertTrue($result);
    }

    public function testDBDeleteWithTypeLocal()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $result = $this->db->DBDelete($this->getDBName());
        $this->assertTrue($result);
    }

    public function testDBDeleteWithNonExistDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBException', 'com.orientechnologies.orient.core.exception.OStorageException: Database with name \'INVALID\' doesn\'t exits.');
        $result = $this->db->DBDelete('INVALID');
    }

    public function testDBDeleteWithWrongOptionCount()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->DBDelete();
    }
}