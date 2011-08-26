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

    public function testConvertComplementIntx32NegativeBound()
    {
        $int = (int) -2147483648;

        $this->assertSame($int, OrientDBCommandAbstract::convertComplementInt($int));
    }

    public function testConvertComplementIntx32PositiveBound()
    {
        $int = (int) 2147483647;

        $this->assertSame($int, OrientDBCommandAbstract::convertComplementInt($int));
    }

    public function testConvertComplementIntZero()
    {
        $int = (int) 0;

        $this->assertSame($int, OrientDBCommandAbstract::convertComplementInt($int));
    }

    public function testConvertComplementIntNegativeBoundViaUnpack()
    {
        $int = (int) -2147483648;

        $packed = pack('N', $int);
        $unpacked = unpack('N', $packed);
        $unpacked = reset($unpacked);

        if (PHP_INT_SIZE == 8) {
            $this->assertNotSame($int, $unpacked);
        }
        $this->assertSame($int, OrientDBCommandAbstract::convertComplementInt($unpacked));
    }

    public function testConvertComplementIntMinusOneViaUnpack()
    {
        $int = (int) -1;

        $packed = pack('N', $int);
        $unpacked = unpack('N', $packed);
        $unpacked = reset($unpacked);

        if (PHP_INT_SIZE == 8) {
            $this->assertNotSame($int, $unpacked);
        }
        $this->assertSame($int, OrientDBCommandAbstract::convertComplementInt($unpacked));
    }

    public function testConvertComplementIntPositiveBoundViaUnpack()
    {
        $int = (int) 2147483647;

        $packed = pack('N', $int);
        $unpacked = unpack('N', $packed);
        $unpacked = reset($unpacked);

        $this->assertSame($int, $unpacked);
        $this->assertSame($int, OrientDBCommandAbstract::convertComplementInt($unpacked));
    }

    public function testConvertComplementIntZeroViaUnpack()
    {
        $int = (int) 0;

        $packed = pack('N', $int);
        $unpacked = unpack('N', $packed);
        $unpacked = reset($unpacked);

        $this->assertSame($int, $unpacked);
        $this->assertSame($int, OrientDBCommandAbstract::convertComplementInt($unpacked));
    }

    public function testConvertComplementShortNegativeBound()
    {
        $short = -32768;

        $this->assertSame($short, OrientDBCommandAbstract::convertComplementShort($short));
    }

    public function testConvertComplementShortPositiveBound()
    {
        $short = 32767;

        $this->assertSame($short, OrientDBCommandAbstract::convertComplementShort($short));
    }

    public function testConvertComplementShortZero()
    {
        $short = 0;

        $this->assertSame($short, OrientDBCommandAbstract::convertComplementShort($short));
    }

    public function testConvertComplementShortNegativeBoundViaUnpack()
    {
        $short = -32768;

        $packed = pack('n', $short);
        $unpacked = unpack('n', $packed);
        $unpacked = reset($unpacked);

        $this->assertNotSame($short, $unpacked);
        $this->assertSame($short, OrientDBCommandAbstract::convertComplementShort($unpacked));
    }

    public function testConvertComplementShortMinusOneViaUnpack()
    {
        $short = -1;

        $packed = pack('n', $short);
        $unpacked = unpack('n', $packed);
        $unpacked = reset($unpacked);

        $this->assertNotSame($short, $unpacked);
        $this->assertSame($short, OrientDBCommandAbstract::convertComplementShort($unpacked));
    }

    public function testConvertComplementShortPositiveBoundViaUnpack()
    {
        $short = 32767;

        $packed = pack('n', $short);
        $unpacked = unpack('n', $packed);
        $unpacked = reset($unpacked);

        $this->assertSame($short, $unpacked);
        $this->assertSame($short, OrientDBCommandAbstract::convertComplementShort($unpacked));
    }

    public function testConvertComplementShortZeroViaUnpack()
    {
        $short = 0;

        $packed = pack('n', $short);
        $unpacked = unpack('n', $packed);
        $unpacked = reset($unpacked);

        $this->assertSame($short, $unpacked);
        $this->assertSame($short, OrientDBCommandAbstract::convertComplementShort($unpacked));
    }
}