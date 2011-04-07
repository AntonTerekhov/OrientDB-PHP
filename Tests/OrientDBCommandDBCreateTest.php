<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBDBCreateTest extends OrientDBBaseTesting
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
            $result = $this->db->DBDelete($this->getDBName());
        }
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
     * @TODO Its strange, but as 0.9.2.4 it is possible to create memory databases with same name
     */
    public function testDBCreateWithExistNameAndSameTypeMemory()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $this->assertTrue($result);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $this->assertTrue($result);
        $this->db->DBDelete($this->getDBName());
    }

    public function testDBCreateWithExistNameAndSameTypeLocal()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->db->DBDelete($this->getDBName());
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $this->setExpectedException('OrientDBException');
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $this->db->DBDelete($this->getDBName());
    }

    /**
     * @TODO Its strange, but as 0.9.2.4 it is possible to different databases types with same name
     */
    public function testDBCreateWithExistNameAndDifferentTypeOne()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->db->DBDelete($this->getDBName());
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $this->assertTrue($result);
        $this->db->DBDelete($this->getDBName());
    }

    /**
     * @TODO Its strange, but as 0.9.2.4 it is possible to create different databases types with same name
     */
    public function testDBCreateWithExistNameAndDifferentTypeTwo()
    {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->db->DBDelete($this->getDBName());
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $this->assertTrue($result);
        $this->db->DBDelete($this->getDBName());
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