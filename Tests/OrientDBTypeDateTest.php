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
 * OrientDBTypeDate() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBTypeDateTest extends PHPUnit_Framework_TestCase
{

    public function testOrientDBTypeDateValueWithT()
    {
        $value = time();
        $date = new OrientDBTypeDate($value . 't');

        $this->assertSame($value . 't', (string) $date);
        $this->assertSame($value . 't', $date->getValue());
        $this->assertSame($value, $date->getTime());
    }

    public function testOrientDBTypeDateValueWithoutT()
    {
        $value = time();
        $date = new OrientDBTypeDate($value);

        $this->assertSame($value . 't', (string) $date);
        $this->assertSame($value . 't', $date->getValue());
        $this->assertSame($value, $date->getTime());
    }

    public function testOrientDBTypeDateValueInvalid()
    {
        $value = '';
        $date = new OrientDBTypeDate($value);

        $this->assertSame('', (string) $date);
        $this->assertNull($date->getValue());
        $this->assertNull($date->getTime());
    }
}