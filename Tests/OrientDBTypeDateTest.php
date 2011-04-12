<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBTypeDateTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        //$this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        //$this->db = null;
    }

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