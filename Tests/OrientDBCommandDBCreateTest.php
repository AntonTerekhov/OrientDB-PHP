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
 * DBCreate() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBDBCreateTest extends OrientDB_TestCase
{

    protected $dbName = 'unittest_';

    protected static $dbSequence = 0;

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        if ($this->db->isConnected()) {
            try {
                $result = $this->db->DBDelete($this->getDBName());
            } catch (OrientDBException $e) {

            }
        }
        $this->db = null;
    }

    protected function sequenceInc()
    {
        self::$dbSequence++;
//        if (self::$dbSequence == 5) {
//            echo self::$dbSequence . PHP_EOL;
//            debug_print_backtrace();
//        }
    }

    protected function getDBName()
    {
        return $this->dbName . self::$dbSequence;
    }

    public function testDBCreateOnNotConnectedDB()
    {
        $this->sequenceInc();
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
    }

    public function testDBCreateOnConnectedDB()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $this->assertTrue($result);
        $result = $this->db->DBDelete($this->getDBName());
    }

    public function testDBCreateOnNotOpenDB()
    {
        $this->sequenceInc();
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
    }

    public function testDBCreateOnOpenDB()
    {
        $this->sequenceInc();
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
    }

    /**
     * In 0.9.2.4 till 1.0rc2-snapshot r3082 it was possible to create memory databases with same name
     */
    public function testDBCreateWithExistNameAndSameTypeMemory()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $this->assertTrue($result);
        try {
            $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        }
        catch (OrientDBException $e) {
            $this->db->DBDelete($this->getDBName());
            return;
        }
        $this->db->DBDelete($this->getDBName());
        $this->fail('Created new DB with existed name and same type: MEMORY');
    }

    public function testDBCreateWithExistNameAndSameTypeLocal()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $this->assertTrue($result);
        try {
            $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        }
        catch (OrientDBException $e) {
            $this->db->DBDelete($this->getDBName());
            return;
        }
        $this->db->DBDelete($this->getDBName());
        $this->fail('Created new DB with existed name and same type: LOCAL');
    }

    /**
     * In 0.9.2.4 till 1.0rc2-snapshot r3110 it was possible to create different databases types with same name
     */
    public function testDBCreateWithExistNameAndDifferentTypeOne()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $this->assertTrue($result);
        try {
            $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        }
        catch (OrientDBException $e) {
            $this->db->DBDelete($this->getDBName());
            return;
        }
        $this->db->DBDelete($this->getDBName());
        $this->fail('Created new DB with existed name');
    }

    /**
     * In 0.9.2.4 till 1.0rc2-snapshot r3082 it was possible to create different databases types with same name
     */
    public function testDBCreateWithExistNameAndDifferentTypeTwo()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $this->assertTrue($result);
        try {
            $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        }
        catch (OrientDBException $e) {
            $this->db->DBDelete($this->getDBName());
            return;
        }
        $this->db->DBDelete($this->getDBName());
        $this->fail('Created new DB with existed name');
    }

    public function testDBCreateWithWrongOptionCount()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->DBCreate($this->getDBName());
    }

    public function testDBCreateWithTypeMemory()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $this->assertTrue($result);
        $this->db->DBDelete($this->getDBName());
    }

    public function testDBCreateWithTypeLocal()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $this->assertTrue($result);
        $this->db->DBDelete($this->getDBName());
    }

    public function testDBCreateWithTypeWrong()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->DBCreate($this->getDBName(), 'INCORRECT');
    }
}