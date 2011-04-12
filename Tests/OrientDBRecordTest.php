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

    public function testParseRecordIDNull()
    {
        $clusterID = 1;
        $recordPos = 2;

        $record = new OrientDBRecord();
        $this->assertNull($record->recordID);
        $record->parse();
        $this->assertNull($record->recordID);

        $record = new OrientDBRecord();
        $record->clusterID = $clusterID;
        $record->parse();
        $this->assertNull($record->recordID);

        $record = new OrientDBRecord();
        $record->recordPos = $recordPos;
        $record->parse();
        $this->assertNull($record->recordID);
    }

    public function testParseRecordIDZero()
    {
        $clusterID = 3;
        $recordPos = 4;

        $record = new OrientDBRecord();
        $record->clusterID = $clusterID;
        $record->recordPos = 0;
        $record->parse();
        $this->assertSame($clusterID . ':' . 0, $record->recordID);

        $record = new OrientDBRecord();
        $record->clusterID = 0;
        $record->recordPos = $recordPos;
        $record->parse();
        $this->assertNull($record->recordID);
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

    public function testParseRecordContentWithBoolean()
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
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->country);
        $this->assertSame('#14:0', (string) $record->data->country);
    }

    public function testParseRecordContentCollection()
    {
        $record = new OrientDBRecord();
        $record->content = 'people:["Alice","Bob","Eva"]';
        $record->parse();

        $this->assertInternalType('array', $record->data->people);
        $this->assertSame('Alice', $record->data->people[0]);
        $this->assertSame('Bob', $record->data->people[1]);
        $this->assertSame('Eva', $record->data->people[2]);
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
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->followers[0]);
        $this->assertSame('#10:5', (string) $record->data->followers[0]);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->followers[1]);
        $this->assertSame('#10:6', (string) $record->data->followers[1]);
        $this->assertSame('Barack', $record->data->name);
        $this->assertSame('Obama', $record->data->surname);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->location);
        $this->assertSame('#3:2', (string) $record->data->location);
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

    public function testParseRecordContentEmptyLink()
    {
        $record = new OrientDBRecord();
        $record->content = 'name:"Rome",country:#14:0,district:#,sea:#';
        $record->parse();

        $this->assertSame('Rome', $record->data->name);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->country);
        $this->assertSame('#14:0', (string) $record->data->country);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->district);
        $this->assertSame('', (string) $record->data->district);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->sea);
        $this->assertSame('', (string) $record->data->sea);
    }

    public function testParseRecordContentNumberFormats()
    {
        $record = new OrientDBRecord();
        $record->content = 'integer:123,byte:112b,short:30s,long:2147483648l,float:999.999f,double:456.7654d';
        $record->parse();

        $this->assertSame(123, $record->data->integer);
        $this->assertSame(112, $record->data->byte);
        $this->assertSame(30, $record->data->short);
        $this->assertSame(2147483648, $record->data->long);
        $this->assertSame(999.999, $record->data->float);
        $this->assertSame(456.7654, $record->data->double);
    }

    public function testParseRecordContentNumberFormatsNegative()
    {
        $record = new OrientDBRecord();
        $record->content = 'integer:-123,byte:-112b,short:-30s,long:-2147483648l,float:-999.999f,double:-456.7654d';
        $record->parse();

        $this->assertSame(-123, $record->data->integer);
        $this->assertSame(-112, $record->data->byte);
        $this->assertSame(-30, $record->data->short);
        $this->assertSame(-2147483648, $record->data->long);
        $this->assertSame(-999.999, $record->data->float);
        $this->assertSame(-456.7654, $record->data->double);
    }

    public function testParseRecordContentNumberFormatFormats()
    {
        $record = new OrientDBRecord();
        $record->content = 'one:1.0E2f,two:-1.0E2f,three:9.8E-4f,four:1.0e2f,five:1.13e10f';
        $record->parse();

        $this->assertSame(1.0E2, $record->data->one);
        $this->assertSame(-1.0e2, $record->data->two);
        $this->assertSame(9.8E-4, $record->data->three);
        $this->assertSame(1.0e2, $record->data->four);
        $this->assertSame(1.13e10, $record->data->five);
    }

    public function testParseRecordContentMapWithNull()
    {
        $record = new OrientDBRecord();
        $record->content = 'rules:{"database":,"database.cluster.internal":,"database.cluster.orole":}';
        $record->parse();

        $this->assertInternalType('array', $record->data->rules);
        $this->assertNull($record->data->rules['database']);
        $this->assertNull($record->data->rules['database.cluster.internal']);
        $this->assertNull($record->data->rules['database.cluster.orole']);
    }

    public function testParseRecordEmbeddedDoc()
    {
        $record = new OrientDBRecord();
        $record->content = 'City@name:"Rome",country:#14:0,embedded:(City@name:"Rome",country:#14:0)';
        $record->parse();

        $this->assertSame('City', $record->className);
        $this->assertSame('Rome', $record->data->name);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->country);
        $this->assertSame('#14:0', (string) $record->data->country);
        $this->assertInstanceOf('OrientDBRecord', $record->data->embedded);
        $this->assertSame('City', $record->data->embedded->className);
        $this->assertSame('Rome', $record->data->embedded->data->name);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->embedded->data->country);
        $this->assertSame('#14:0', (string) $record->data->embedded->data->country);
    }

    public function testParseRecordEmbeddedDocsInCollection()
    {
        $record = new OrientDBRecord();
        $record->content = 'values:[(name:"John"),(City:"New York"),(color:"#FFF")]';
        $record->parse();

        $this->assertInternalType('array', $record->data->values);
        $this->assertInstanceOf('OrientDBRecord', $record->data->values[0]);
        $this->assertSame('John', $record->data->values[0]->data->name);
        $this->assertInstanceOf('OrientDBRecord', $record->data->values[1]);
        $this->assertSame('New York', $record->data->values[1]->data->City);
        $this->assertInstanceOf('OrientDBRecord', $record->data->values[2]);
        $this->assertSame('#FFF', $record->data->values[2]->data->color);
    }
}