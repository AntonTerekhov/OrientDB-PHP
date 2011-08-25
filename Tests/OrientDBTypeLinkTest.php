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
 * OrientDBTypeLink() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBTypeLinkTest extends PHPUnit_Framework_TestCase
{

    public function testOrientDBTypeLinkValueWithHash()
    {
        $clusterID = 10;
        $recordPos = 0;
        $value = $clusterID . ':' . $recordPos;
        $link = new OrientDBTypeLink('#' . $value);

        $this->assertSame('#' . $value, (string) $link);
        $this->assertSame('#' . $value, $link->getHash());
        $this->assertSame($value, $link->get());
        $this->assertSame($clusterID, $link->clusterID);
        $this->assertSame($recordPos, $link->recordPos);
    }

    public function testOrientDBTypeLinkValueWithoutHash()
    {
        $clusterID = 10;
        $recordPos = 0;
        $value = $clusterID . ':' . $recordPos;
        $link = new OrientDBTypeLink($value);

        $this->assertSame('#' . $value, (string) $link);
        $this->assertSame('#' . $value, $link->getHash());
        $this->assertSame($value, $link->get());
        $this->assertSame($clusterID, $link->clusterID);
        $this->assertSame($recordPos, $link->recordPos);
    }

    public function testOrientDBTypeLinkValueInvalid()
    {
        $value = 10;
        $link = new OrientDBTypeLink($value);

        $this->assertSame('', (string) $link);
        $this->assertNull($link->get());
        $this->assertSame('#', $link->getHash());
        $this->assertNull($link->clusterID);
        $this->assertNull($link->recordPos);
    }

    public function testOrientDBTypeLinkValuesCorrect()
    {
        $clusterID = 100;
        $recordPos = 0;
        $value = $clusterID . ':' . $recordPos;

        $link = new OrientDBTypeLink($clusterID, $recordPos);

        $this->assertSame('#' . $value, (string) $link);
        $this->assertSame('#' . $value, $link->getHash());
        $this->assertSame($value, $link->get());
        $this->assertSame($clusterID, $link->clusterID);
        $this->assertSame($recordPos, $link->recordPos);
    }

    public function testOrientDBTypeLinkValuesInvalidOne()
    {
        $clusterID = 'one';
        $recordPos = 0;

        $link = new OrientDBTypeLink($clusterID, $recordPos);

        $this->assertSame('', (string) $link);
        $this->assertSame('#', $link->getHash());
        $this->assertNull($link->get());
        $this->assertNull($link->clusterID);
        $this->assertNull($link->recordPos);
    }

    public function testOrientDBTypeLinkValuesInvalidTwo()
    {
        $clusterID = 101;
        $recordPos = '';

        $link = new OrientDBTypeLink($clusterID, $recordPos);

        $this->assertSame('', (string) $link);
        $this->assertSame('#', $link->getHash());
        $this->assertNull($link->get());
        $this->assertNull($link->clusterID);
        $this->assertNull($link->recordPos);
    }

    public function testOrientDBTypeLinkValuesCorrectLong()
    {
        $clusterID = 100;
        if (PHP_INT_SIZE == 8) {
            $recordPos = 9223372036854775807;
        } else {
            $recordPos = '9223372036854775807';
        }
        $value = $clusterID . ':' . $recordPos;

        $link = new OrientDBTypeLink($clusterID, $recordPos);

        $this->assertSame('#' . $value, (string) $link);
        $this->assertSame('#' . $value, $link->getHash());
        $this->assertSame($value, $link->get());
        $this->assertSame($clusterID, $link->clusterID);
        $this->assertSame($recordPos, $link->recordPos);
    }

    public function testOrientDBTypeLinkValuesincorrectLong()
    {
        $clusterID = 100;
        $recordPos = '9223372036854775807 ';
        $value = $clusterID . ':' . $recordPos;

        $link = new OrientDBTypeLink($clusterID, $recordPos);

        $this->assertSame('', (string) $link);
        $this->assertSame('#', $link->getHash());
        $this->assertNull($link->get());
        $this->assertNull($link->clusterID);
        $this->assertNull($link->recordPos);
    }
}