<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBQueryTest extends PHPUnit_Framework_TestCase
{

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testCommandOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->command('');
    }

    public function testCommandOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->command('');
    }

    public function testCommandOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->command('');
    }

    public function testCommandOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select * from city limit 7');
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
    }

    public function testCommandWithWrongOptionCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->command();
    }

    public function testCommandWithWrongMode() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->command('', 'INVALID');
    }

    public function testCommandWithModeAsync() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select from city traverse( any() )', OrientDB::COMMAND_MODE_ASYNC);
        $this->assertInternalType('array', $records);
        $this->assertInstanceOf('OrientDBRecord', array_pop($records));
    }

    public function testCommandWithModeSync() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $record = $this->db->command('select * from [10:1]', OrientDB::COMMAND_MODE_SYNC);
    }

    public function testCommandWithNoRecords() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $records = $this->db->command('select from 11:4 where any() traverse(0,10) (address.city = "Rome")');
        $this->assertFalse($records);
    }

}