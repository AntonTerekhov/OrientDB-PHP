<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBTypeLinkTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        //$this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        //$this->db = null;
    }

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
        $value = $clusterID . ':' . $recordPos;

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
        $value = $clusterID . ':' . $recordPos;

        $link = new OrientDBTypeLink($clusterID, $recordPos);

        $this->assertSame('', (string) $link);
        $this->assertSame('#', $link->getHash());
        $this->assertNull($link->get());
        $this->assertNull($link->clusterID);
        $this->assertNull($link->recordPos);
    }
}