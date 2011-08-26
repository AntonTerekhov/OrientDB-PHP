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
 * OrientDBCommandAbstract::I64() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBI64Test extends PHPUnit_Framework_TestCase
{

    public function testUnpackI64Zero()
    {
        $result = 0;
        $hi = 0x00000000;
        $low = 0x00000000;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64One()
    {
        $result = 1;
        $hi = 0x00000000;
        $low = 0x00000001;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64NegativeOne()
    {
        $result = -1;
        $hi = 0xFFFFFFFF;
        $low = 0xFFFFFFFF;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64NegativeTwo()
    {
        $result = -2;
        $hi = 0xFFFFFFFF;
        $low = 0xFFFFFFFE;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64AboveNegative32Bound()
    {
        $result = -2147483647;
        $hi = 0xFFFFFFFF;
        $low = 0x80000001;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64Negative32Bound()
    {
        $result = -2147483648;
        $hi = 0xFFFFFFFF;
        $low = 0x80000000;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64BelowNegative32Bound()
    {
        if (PHP_INT_SIZE == 8) {
            $result = -2147483649;
        } else {
            $result = '-2147483649';
        }
        $hi = 0xFFFFFFFF;
        $low = 0x7FFFFFFF;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64AbovePositive32Bound()
    {
        $result = 2147483648;
        $hi = 0x00000000;
        $low = 0x80000000;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64Positive32Bound()
    {
        $result = 2147483647;
        $hi = 0x00000000;
        $low = 0x7FFFFFFF;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64BelowPositive32Bound()
    {
        $result = 2147483646;
        $hi = 0x00000000;
        $low = 0x7FFFFFFE;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64NegativeBound()
    {
        if (PHP_INT_SIZE == 8) {
            $result = (int) -9223372036854775808;
        } else {
            $result = '-9223372036854775808';
        }

        $hi = 0x80000000;
        $low = 0x00000000;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64AboveNegativeBound()
    {
        if (PHP_INT_SIZE == 8) {
            $result = -9223372036854775807;
        } else {
            $result = '-9223372036854775807';
        }

        $hi = 0x80000000;
        $low = 0x00000001;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64PositiveBound()
    {
        if (PHP_INT_SIZE == 8) {
            $result = 9223372036854775807;
        } else {
            $result = '9223372036854775807';
        }

        $hi = 0x7FFFFFFF;
        $low = 0xFFFFFFFF;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }

    public function testUnpackI64BelowPositiveBound()
    {
        if (PHP_INT_SIZE == 8) {
            $result = 9223372036854775806;
        } else {
            $result = '9223372036854775806';
        }

        $hi = 0x7FFFFFFF;
        $low = 0xFFFFFFFE;

        $this->assertSame($result, OrientDBCommandAbstract::unpackI64($hi, $low));
    }
}