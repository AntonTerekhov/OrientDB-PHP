<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

require_once 'OrientDB/OrientDB.php';

/**
 * OrientDBData() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBDataTest extends PHPUnit_Framework_TestCase
{
    public function  testConstructWithOrientDBRecord()
    {
        $record = $this->getMock('OrientDBRecord');
        $data = new OrientDBData($record);
        $this->assertInstanceOf('OrientDBData', $data);
    }

    public function  testConstructWithOther()
    {
        $this->setExpectedException('OrientDBException');
        $data = new OrientDBData(true);
    }
    // @TODO: add more unittests
}