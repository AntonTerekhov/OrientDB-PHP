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
 * OrientDBCommandAbstract::convertComplement() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBConvertComplementTest extends PHPUnit_Framework_TestCase
{

    public function testConvertComplementx32NegativeBound()
    {
        $int = (int) -2147483648;

        $this->assertSame($int, OrientDBCommandAbstract::convertComplement($int));
    }

    public function testConvertComplementx32PositiveBound()
    {
        $int = (int) 2147483647;

        $this->assertSame($int, OrientDBCommandAbstract::convertComplement($int));
    }

    public function testConvertComplementZero()
    {
        $int = (int) 0;

        $this->assertSame($int, OrientDBCommandAbstract::convertComplement($int));
    }

    public function testConvertComplementNegativeBoundViaUnpack()
    {
        $int = (int) -2147483648;

        $packed = pack('N', $int);
        $unpacked = unpack('N', $packed);
        $unpacked = reset($unpacked);

        $this->assertNotSame($int, $unpacked);
        $this->assertSame($int, OrientDBCommandAbstract::convertComplement($unpacked));
    }

    public function testConvertComplementPositiveBoundViaUnpack()
    {
        $int = (int) 2147483647;

        $packed = pack('N', $int);
        $unpacked = unpack('N', $packed);
        $unpacked = reset($unpacked);

        $this->assertSame($int, $unpacked);
        $this->assertSame($int, OrientDBCommandAbstract::convertComplement($unpacked));
    }

    public function testConvertComplementZeroViaUnpack()
    {
        $int = (int) 0;

        $packed = pack('N', $int);
        $unpacked = unpack('N', $packed);
        $unpacked = reset($unpacked);

        $this->assertSame($int, $unpacked);
        $this->assertSame($int, OrientDBCommandAbstract::convertComplement($unpacked));
    }
}