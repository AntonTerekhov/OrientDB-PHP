<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBRecordTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        //$this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown()
    {
        //$this->db = null;
    }

    public function testParseRecordID()
    {
        $clusterID = 1;
        $recordPos = 2;

        $record = new OrientDBRecord();
        $record->clusterID = $clusterID;
        $record->recordPos = $recordPos;
        $this->assertNull($record->recordID);
        $record->parse();
        $this->assertSame($clusterID . ':' . $recordPos, $record->recordID);
    }

    public function testParseRecordContentSimpleString()
    {
        $record = new OrientDBRecord();
        $key = 'name';
        $value = 'Василий';
        $record->content = $key . ':"' . $value . '"';
        $record->parse();

        $this->assertSame($value, $record->data->name);
    }

    public function testParseRecordContentTwoStrings()
    {
        $record = new OrientDBRecord();
        $keys = array(
                        'FirstName',
                        'LastName');
        $values = array(
                        'Василий',
                        'Иванов');

        $temp = array();
        for ($i = 0; $i < count($keys); $i++) {
            $temp[] = $keys[$i] . ':"' . $values[$i] . '"';
        }
        $record->content = implode(',', $temp);
        $record->parse();

        for ($i = 0; $i < count($keys); $i++) {
            $this->assertSame($values[$i], $record->data->$keys[$i]);
        }
    }

    public function testParseRecordContentStringsWithEscape()
    {
        $record = new OrientDBRecord();
        $record->content = 'FirstName:"Василий\\\\",LastName:"Иванов\""';
        $record->parse();

        $this->assertSame("Василий\\", $record->data->FirstName);
        $this->assertSame("Иванов\"", $record->data->LastName);
    }

    public function testParseRecordContentStringsWithBoolean()
    {
        $record = new OrientDBRecord();
        $record->content = 'False:false,LastName:"Smith",true:true';
        $record->parse();

        $this->assertFalse($record->data->False);
        $this->assertSame("Smith", $record->data->LastName);
        $this->assertTrue($record->data->true);
    }

    public function testParseRecordContentCity()
    {
        $record = new OrientDBRecord();
        $record->content = 'City@name:"Rome",country:#14:0';
        $record->parse();

        $this->assertSame('City', $record->className);
        $this->assertSame('Rome', $record->data->name);
        $this->assertSame('#14:0', $record->data->country);
    }

    public function testParseRecordContentComplex()
    {
        $record = new OrientDBRecord();
        $record->content = 'Profile@nick:"ThePresident",follows:[],followers:[#10:5,#10:6],name:"Barack",surname:"Obama",location:#3:2,invitedBy:,salary_cloned:,salary:120.3f';
        $record->parse();

        $this->assertSame('Profile', $record->className);
        $this->assertSame('ThePresident', $record->data->nick);
        $this->assertInternalType('array', $record->data->follows);
        $this->assertSame(0, count($record->data->follows));
        $this->assertInternalType('array', $record->data->followers);
        $this->assertSame(2, count($record->data->followers));
        $this->assertSame('#10:5', $record->data->followers[0]);
        $this->assertSame('#10:6', $record->data->followers[1]);
        $this->assertSame('Barack', $record->data->name);
        $this->assertSame('Obama', $record->data->surname);
        $this->assertSame('#3:2', $record->data->location);
        $this->assertNull($record->data->invitedBy);
        $this->assertNull($record->data->salary_cloned);
        $this->assertSame(120.3, $record->data->salary);
    }

    public function testParseRecordContentMap()
    {
        $record = new OrientDBRecord();
        $record->content = 'ORole@name:"reader",inheritedRole:,mode:0,rules:{"database":2,"database.cluster.internal":2,"database.cluster.orole":2,"database.cluster.ouser":2,"database.class.*":2,"database.cluster.*":2,"database.query":2,"database.command":2,"database.hook.record":2}';
        $record->parse();

        $this->assertSame('ORole', $record->className);
        $this->assertSame('reader', $record->data->name);
        $this->assertNull($record->data->inheritedRole);
        $this->assertSame(0, $record->data->mode);
        $this->assertInternalType('array', $record->data->rules);
        $this->assertSame(2, $record->data->rules['database']);
        $this->assertSame(2, $record->data->rules['database.cluster.internal']);
        $this->assertSame(2, $record->data->rules['database.cluster.orole']);
        $this->assertSame(2, $record->data->rules['database.cluster.ouser']);
        $this->assertSame(2, $record->data->rules['database.class.*']);
        $this->assertSame(2, $record->data->rules['database.cluster.*']);
        $this->assertSame(2, $record->data->rules['database.query']);
        $this->assertSame(2, $record->data->rules['database.command']);
        $this->assertSame(2, $record->data->rules['database.hook.record']);
    }
}