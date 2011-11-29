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
 * connect(), DBOpen() and related properties test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBCommandsBasicTest extends OrientDB_TestCase
{

    protected function setUp()
    {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    public function testConnectWithCorrectUserPassword()
    {
        $this->assertFalse($this->db->isConnected());
        $this->assertTrue($this->db->connect('root', $this->root_password));
        $this->assertTrue($this->db->isConnected());
        $this->assertFalse($this->db->isDBOpen());
    }

    public function testConnectWithIncorrectUserPassword()
    {
        $this->setExpectedException('OrientDBException');
        $this->db->connect('toor', $this->root_password);
    }

    public function testConnectWithNotEnougnParams()
    {
        $this->setExpectedException('OrientDBWrongParamsException');
        $this->db->connect('root');
    }

    public function testConnectOnAlreadyConnectedDB()
    {
        $this->assertFalse($this->db->isConnected());
        $result = $this->db->connect('root', $this->root_password);
        $this->assertTrue($this->db->connect('root', $this->root_password));
        $this->assertTrue($this->db->isConnected());
        $this->assertFalse($this->db->isDBOpen());
    }

    public function testOpenDBWithCorrectUserPassword()
    {
        $this->assertFalse($this->db->isDBOpen());
        $clusters = $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertInternalType('array', $clusters);
        $this->assertFalse($this->db->isConnected());
        $this->assertTrue($this->db->isDBOpen());
    }

    public function testOpenDBWithIncorrectUserPassword()
    {
        $this->setExpectedException('OrientDBException');
        $clusters = $this->db->DBOpen('demo', 'writer', 'INCORRECT');
    }

    public function testOpenDBWithNonExistentDB()
    {
        $this->setExpectedException('OrientDBException');
        $clusters = $this->db->DBOpen('NONEXISTENT', 'writer', 'writer');
    }

    public function testOpenDBWithNotEnoughParams()
    {
        $this->setExpectedException('OrientDBWrongParamsException');
        $clusters = $this->db->DBOpen('demo');
    }

    public function testConnectOnAlreadyOpenedDB()
    {
        $this->assertFalse($this->db->isConnected());
        $clusters = $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertTrue($this->db->isDBOpen());
        $this->assertTrue($this->db->connect('root', $this->root_password));
        $this->assertTrue($this->db->isConnected());
    }

    public function testOpenDBOnAlreadyConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $clusters = $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertTrue($this->db->isDBOpen());
        $this->assertInternalType('array', $clusters);
        $this->assertTrue($this->db->isConnected());
    }

    public function testOpenDBOnAlreadyOpenedDB()
    {
        $this->assertFalse($this->db->isDBOpen());
        $clusters1 = $this->db->DBOpen('demo', 'writer', 'writer');
        $clusters2 = $this->db->DBOpen('demo', 'admin', 'admin');
        $this->assertInternalType('array', $clusters2);
        $this->assertTrue($this->db->isDBOpen());
        $this->assertFalse($this->db->isConnected());
    }

    public function testCloseDBOnNotConnectedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $this->db->DBClose();
    }

    public function testCloseDBOnNotOpenedDB()
    {
        $this->setExpectedException('OrientDBWrongCommandException');
        $this->db->DBClose();
    }

    public function testCloseDBOnConnectedDB()
    {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $this->db->DBClose();
    }

    public function testCloseDBOnOpenedDB()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->assertTrue($this->db->isDBOpen());
        $this->db->DBClose();
        $this->assertFalse($this->db->isDBOpen());
        $this->assertEmpty($this->db->socket);
    }

    public function testAnyCommnandAfterDBClose()
    {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->db->DBClose();
        $this->setExpectedException('OrientDBWrongCommandException');
        $this->db->DBOpen('demo', 'writer', 'writer');
    }

    public function testDBOpenCount()
    {
        $i = 0;
        $tries = 1000;
        try {
            while ($i < $tries) {
                $clusters = $this->db->DBOpen('demo', 'admin', 'admin');
                $i++;
                $this->db = new OrientDB('localhost', 2424);
            }
        }
        catch (OrientDBException $e) {
            // echo 'Tries: ' . $i . PHP_EOL;
        }
        if (isset($e)) {
            $message = 'New connections is not accepted, reason: ' . $e->getMessage();
        } else {
            $message = '';
        }
        $this->AssertSame($tries, $i, $message);
    }
}