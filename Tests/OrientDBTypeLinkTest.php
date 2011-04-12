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
        $value = '10:0';
        $link = new OrientDBTypeLink('#' . $value);

        $this->assertSame('#' . $value, (string) $link);
        $this->assertSame('#' . $value, $link->getHash());
        $this->assertSame($value, $link->get());
    }

    public function testOrientDBTypeLinkValueWithoutHash()
    {
        $value = '10:0';
        $link = new OrientDBTypeLink($value);

        $this->assertSame('#' . $value, (string) $link);
        $this->assertSame('#' . $value, $link->getHash());
        $this->assertSame($value, $link->get());
    }

    public function testOrientDBTypeLinkValueInvalid()
    {
        $value = 10;
        $link = new OrientDBTypeLink($value);

        $this->assertSame('', (string) $link);
        $this->assertNull($link->get());
        $this->assertNull($link->getHash());
    }
}