<?php

require_once 'OrientDB/OrientDB.php';

class OrientDBConnectTest extends PHPUnit_Framework_TestCase
{

    public function testNewFailedConnection()
    {
        $this->setExpectedException('Exception');
        $db = new OrientDB('localhost', 2000);
    }

    public function testNewSucsessfullConnection()
    {
        $db = new OrientDB('localhost', 2424);
        $this->assertInstanceOf('OrientDB', $db);
    }
}

