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
 * commit() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBCommitTest extends OrientDB_TestCase
{

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testCommitOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->commit();
    }

    public function testCommitOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->commit();
    }

    public function testCommitOnNotOpenDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $list = $this->db->commit();
    }

    public function testCommitOnOpenDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException', 'Not implemented');
        $recordPos = $this->db->commit();
    }

}