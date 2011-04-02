<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBDBDeleteTest extends OrientDBBaseTesting
{

    protected $dbName = 'unittest_';

    protected static $dbSequence = 0;

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    protected function sequenceInc() {
        self::$dbSequence++;
    }

    protected function getDBName() {
        return $this->dbName . self::$dbSequence;
    }

    public function testDBCreateOnNotConnectedDB() {
        $this->sequenceInc();
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBDelete($this->getDBName());
    }

    public function testDBDeleteOnConnectedDB() {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $this->assertTrue($result);
        $result = $this->db->DBDelete($this->getDBName());
        $this->assertTrue($result);
    }

    public function testDBDeleteOnNotOpenDB() {
        $this->sequenceInc();
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBDelete($this->getDBName());
    }

    public function testDBDeleteOnOpenDB() {
        $this->sequenceInc();
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBDelete($this->getDBName());
    }

    public function testDBDeleteWithTypeMemory() {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $result = $this->db->DBDelete($this->getDBName());
        $this->assertTrue($result);
    }

    public function testDBDeleteWithTypeLocal() {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $result = $this->db->DBDelete($this->getDBName());
        $this->assertTrue($result);
    }

    public function testDBDeleteWithNonExistDB() {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBDelete('INVALID');
        $this->assertTrue($result);
    }

    public function testDBDeleteWithWrongOptionCount() {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->DBDelete();
    }
}