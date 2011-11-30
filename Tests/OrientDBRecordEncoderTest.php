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
 * OrientDBRecordEncoder test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBRecordEncoderTest extends PHPUnit_Framework_TestCase
{
    public function  testIsAssocWithNonArray()
    {
        $method = new ReflectionMethod('OrientDBRecordEncoder', 'isAssoc');
        $method->setAccessible(true);

        $this->assertNull($method->invoke(null, null));
    }

    public function  testIsAssocWithSequentialArray()
    {
        $method = new ReflectionMethod('OrientDBRecordEncoder', 'isAssoc');
        $method->setAccessible(true);

        $array = range(1, 10);

        $this->assertFalse($method->invoke(null, $array));
    }

    public function  testIsAssocWithAssocArray()
    {
        $method = new ReflectionMethod('OrientDBRecordEncoder', 'isAssoc');
        $method->setAccessible(true);

        $array = array(1 => 1, 'two' => 2);

        $this->assertTrue($method->invoke(null, $array));
    }

    public function  testIsAssocWithPseudoAssocArray()
    {
        $method = new ReflectionMethod('OrientDBRecordEncoder', 'isAssoc');
        $method->setAccessible(true);

        $array = array(1 => 1, '2' => 2);

        $this->assertFalse($method->invoke(null, $array));
    }
}