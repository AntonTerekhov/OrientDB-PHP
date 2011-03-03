<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBDBCreateTest extends PHPUnit_Framework_TestCase
{

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    protected $db;

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
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
    }

    public function testDBCreateOnConnectedDB() {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $this->assertTrue($result);
        // @TODO Not implemented in OrientDB 0.9.2.4
        //$result = $this->db->DBDelete($this->getDBName());
    }

    public function testDBCreateOnNotOpenDB() {
        $this->sequenceInc();
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
    }

    public function testDBCreateOnOpenDB() {
        $this->sequenceInc();
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
    }

    /**
     * Its strange, but as 0.9.2.4 it is possible to create memory databases with same name
     */
    public function testDBCreateWithExistNameAndSameTypeMemory() {
        $this->markTestSkipped('Disabled because of unstable behavior of OrientDB');
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $this->assertTrue($result);
    }

    public function testDBCreateWithExistNameAndSameTypeLocal() {
        $this->markTestSkipped('Disabled because of unstable behavior of OrientDB');
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $this->setExpectedException('OrientDBException');
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);

    }

    public function testDBCreateWithExistNameAndDifferentTypeOne() {
        $this->markTestSkipped('Disabled because of unstable behavior of OrientDB');
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBException');
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
    }

    public function testDBCreateWithExistNameAndDifferentTypeTwo() {
        $this->markTestSkipped('Disabled because of unstable behavior of OrientDB');
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $this->setExpectedException('OrientDBException');
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
    }

    public function testDBCreateWithWrongOptionCount() {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->DBCreate($this->getDBName());
    }

    public function testDBCreateWithTypeMemory() {
        $this->markTestSkipped('Disabled because of unstable behavior of OrientDB');
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_MEMORY);
        $this->assertTrue($result);
    }

    public function testDBCreateWithTypeLocal() {
        $this->markTestSkipped('Disabled because of unstable behavior of OrientDB');
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $result = $this->db->DBCreate($this->getDBName(), OrientDB::DB_TYPE_LOCAL);
        $this->assertTrue($result);
    }

    public function testDBCreateWithTypeWrong() {
        $this->sequenceInc();
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->DBCreate($this->getDBName(), 'INCORRECT');
    }
}