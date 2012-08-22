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
 * OrientDBRecord() test in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
class OrientDBRecordTest extends PHPUnit_Framework_TestCase
{

    public function testParseRecordID()
    {
        $clusterID = 1;
        $recordPos = 2;

        $record = new OrientDBRecord();
        $this->assertNull($record->recordID);
        $record->clusterID = $clusterID;
        $record->recordPos = $recordPos;
        $this->assertSame($clusterID . ':' . $recordPos, $record->recordID);
    }

    public function testParseRecordIDNull()
    {
        $clusterID = 1;
        $recordPos = 2;

        $record = new OrientDBRecord();
        $this->assertNull($record->recordID);
        $this->assertNull($record->recordID);

        $record = new OrientDBRecord();
        $record->clusterID = $clusterID;
        $this->assertNull($record->recordID);

        $record = new OrientDBRecord();
        $record->recordPos = $recordPos;
        $this->assertNull($record->recordID);
    }

    public function testParseRecordIDZero()
    {
        $clusterID = 3;
        $recordPos = 4;

        $record = new OrientDBRecord();
        $record->clusterID = $clusterID;
        $record->recordPos = 0;
        $this->assertSame($clusterID . ':' . 0, $record->recordID);

        $record = new OrientDBRecord();
        $record->clusterID = 0;
        $record->recordPos = $recordPos;
        $this->assertSame(0 . ':' . $recordPos, $record->recordID);
    }

    public function testParseRecordPosLongCorrect()
    {
        $clusterID = 3;
        $recordPos = '9223372036854775807';

        $record = new OrientDBRecord();
        $record->clusterID = $clusterID;
        $record->recordPos = $recordPos;
        $record->parse();

        $this->assertSame($clusterID, $record->clusterID);
        $this->assertSame($recordPos, $record->recordPos);
        $this->assertSame($clusterID . ':' . $recordPos, $record->recordID);
    }

    public function testParseRecordPosNegative()
    {
        $clusterID = 3;
        $recordPos = '-1';

        $record = new OrientDBRecord();
        $record->clusterID = $clusterID;
        $record->recordPos = $recordPos;
        $record->parse();

        $this->assertSame($clusterID, $record->clusterID);
        $this->assertSame($recordPos, $record->recordPos);
        $this->assertNull($record->recordID);
    }

    public function testParseRecordPosLongIncorrect()
    {
        $clusterID = 3;
        $recordPos = '9223372036854775807 ';

        $record = new OrientDBRecord();
        $record->clusterID = $clusterID;
        $record->recordPos = $recordPos;
        $record->parse();

        $this->assertSame($clusterID, $record->clusterID);
        $this->assertNull($record->recordPos);
        $this->assertNull($record->recordID);
        $this->assertEmpty($record->data->getKeys());
    }

    public function testParseRecordContentSimpleString()
    {
        $record = new OrientDBRecord();
        $key = 'name';
        $value = 'Василий';
        $record->content = $key . ':"' . $value . '"';

        $this->assertSame($value, $record->data->name);
        $this->assertSame(array($key), $record->data->getKeys());
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

        for ($i = 0; $i < count($keys); $i++) {
            $this->assertSame($values[$i], $record->data->$keys[$i]);
        }
        $this->assertSame($record->data->getKeys(), $keys);
    }

    public function testParseRecordContentStringsWithEscape()
    {
        $record = new OrientDBRecord();
        $record->content = 'FirstName:"Василий\\\\",LastName:"Иванов\""';

        $this->assertSame("Василий\\", $record->data->FirstName);
        $this->assertSame("Иванов\"", $record->data->LastName);

        $this->assertSame($record->content, $record->__toString());
    }

    public function testParseRecordContentWithBoolean()
    {
        $record = new OrientDBRecord();
        $record->content = 'False:false,LastName:"Smith",true:true';

        $this->assertFalse($record->data->False);
        $this->assertSame("Smith", $record->data->LastName);
        $this->assertTrue($record->data->true);

        $this->assertSame($record->content, $record->__toString());
    }

    public function testParseRecordContentCity()
    {
        $record = new OrientDBRecord();
        $record->content = 'City@name:"Rome",country:#14:0';

        $this->assertSame('City', $record->className);
        $this->assertSame('Rome', $record->data->name);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->country);
        $this->assertSame('#14:0', (string) $record->data->country);

        $this->assertSame($record->content, $record->__toString());
    }

    public function testParseRecordContentCollection()
    {
        $record = new OrientDBRecord();
        $record->content = 'people:["Alice","Bob","Eva"]';

        $this->assertInternalType('array', $record->data->people);
        $this->assertSame('Alice', $record->data->people[0]);
        $this->assertSame('Bob', $record->data->people[1]);
        $this->assertSame('Eva', $record->data->people[2]);
        $this->assertSame($record->data->getKeys(), array('people'));

        $this->assertSame($record->content, $record->__toString());
    }

    public function testParseRecordContentComplex()
    {
        $record = new OrientDBRecord();
        $record->content = 'Profile@nick:"ThePresident",follows:[],followers:[#10:5,#10:6],name:"Barack",surname:"Obama",location:#3:2,invitedBy:,salary_cloned:,salary:120.3f';

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

        $this->assertSame($record->content, $record->__toString());
    }

    public function testParseRecordContentMap()
    {
        $record = new OrientDBRecord();
        $record->content = 'ORole@name:"reader",inheritedRole:,mode:0,rules:{"database":2,"database.cluster.internal":2,"database.cluster.orole":2,"database.cluster.ouser":2,"database.class.*":2,"database.cluster.*":2,"database.query":2,"database.command":2,"database.hook.record":2}';

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

        $this->assertSame($record->content, $record->__toString());
    }

    public function testParseRecordContentEmptyLink()
    {
        $record = new OrientDBRecord();
        $record->content = 'name:"Rome",country:#14:0,district:#,sea:#';

        $this->assertSame('Rome', $record->data->name);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->country);
        $this->assertSame('#14:0', (string) $record->data->country);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->district);
        $this->assertSame('', (string) $record->data->district);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->sea);
        $this->assertSame('', (string) $record->data->sea);

        $this->assertSame($record->content, $record->__toString());
    }

    public function testParseRecordContentNumberFormats()
    {
        $record = new OrientDBRecord();
        $record->content = 'integer:123,byte:112b,short:30s,long:2147483648l,float:999.999f,double:456.7654d,decimal:12.34c';

        $this->assertSame(123, $record->data->integer);
        $this->assertSame(112, $record->data->byte);
        $this->assertSame(30, $record->data->short);
        $this->assertSame((double) 2147483648, $record->data->long);
        $this->assertSame(999.999, $record->data->float);
        $this->assertSame(456.7654, $record->data->double);
    }

    public function testParseRecordContentNumberFormatsNegative()
    {
        $record = new OrientDBRecord();
        $record->content = 'integer:-123,byte:-112b,short:-30s,long:-2147483648l,float:-999.999f,double:-456.7654d,decimal:12.34c';

        $this->assertSame(-123, $record->data->integer);
        $this->assertSame(-112, $record->data->byte);
        $this->assertSame(-30, $record->data->short);
        $this->assertSame((double) -2147483648, $record->data->long);
        $this->assertSame(-999.999, $record->data->float);
        $this->assertSame(-456.7654, $record->data->double);
    }

    public function testParseRecordContentNumberFormatFormats()
    {
        $record = new OrientDBRecord();
        $record->content = 'one:1.0E2f,two:-1.0E2f,three:9.8E-4f,four:1.0e2f,five:1.13e10f';

        $this->assertSame(1.0E2, $record->data->one);
        $this->assertSame(-1.0e2, $record->data->two);
        $this->assertSame(9.8E-4, $record->data->three);
        $this->assertSame(1.0e2, $record->data->four);
        $this->assertSame(1.13e10, $record->data->five);
    }

    public function testParseRecordContentDate()
    {
        $record = new OrientDBRecord();
        $record->content = 'filename:"readme.markdown",permissions:"0644",user:"nobody",group:"nobody",size:11958,modified:1302627138t';

        $this->assertSame('readme.markdown', $record->data->filename);
        $this->assertSame('0644', $record->data->permissions);
        $this->assertSame('nobody', $record->data->user);
        $this->assertSame('nobody', $record->data->group);
        $this->assertSame(11958, $record->data->size);
        $this->assertSame(1302627138, $record->data->modified->getTime());

        $this->assertSame($record->content, $record->__toString());
    }

    public function testParseRecordContentMapWithNull()
    {
        $record = new OrientDBRecord();
        $record->content = 'rules:{"database":,"database.cluster.internal":,"database.cluster.orole":}';

        $this->assertInternalType('array', $record->data->rules);
        $this->assertNull($record->data->rules['database']);
        $this->assertNull($record->data->rules['database.cluster.internal']);
        $this->assertNull($record->data->rules['database.cluster.orole']);

        $this->assertSame($record->content, $record->__toString());
    }

    public function testParseRecordEmbeddedDoc()
    {
        $record = new OrientDBRecord();
        $record->content = 'City@name:"Rome",country:#14:0,embedded:(City@name:"Rome",country:#14:0)';

        $this->assertSame('City', $record->className);
        $this->assertSame('Rome', $record->data->name);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->country);
        $this->assertSame('#14:0', (string) $record->data->country);
        $this->assertInstanceOf('OrientDBRecord', $record->data->embedded);
        $this->assertSame('City', $record->data->embedded->className);
        $this->assertSame('Rome', $record->data->embedded->data->name);
        $this->assertInstanceOf('OrientDBTypeLink', $record->data->embedded->data->country);
        $this->assertSame('#14:0', (string) $record->data->embedded->data->country);

        $this->assertSame($record->content, $record->__toString());
    }

    public function testParseRecordEmbeddedDocsInCollection()
    {
        $record = new OrientDBRecord();
        $record->content = 'values:[(name:"John"),(City:"New York"),(color:"#FFF")]';

        $this->assertInternalType('array', $record->data->values);
        $this->assertInstanceOf('OrientDBRecord', $record->data->values[0]);
        $this->assertSame('John', $record->data->values[0]->data->name);
        $this->assertInstanceOf('OrientDBRecord', $record->data->values[1]);
        $this->assertSame('New York', $record->data->values[1]->data->City);
        $this->assertInstanceOf('OrientDBRecord', $record->data->values[2]);
        $this->assertSame('#FFF', $record->data->values[2]->data->color);

        $this->assertSame($record->content, $record->__toString());

        $this->assertSame($record->data->getKeys(), array('values'));
    }

    public function testCreateRecord()
    {
        $record = new OrientDBRecord();
        $record->data->FirstName = 'Bruce';
        $record->data->LastName = 'Wayne';
        $record->data->appearance = 1939;

        $this->assertSame('FirstName:"Bruce",LastName:"Wayne",appearance:1939', (string) $record);
    }

    public function testOversizedRecord()
    {
        $record = new OrientDBRecord();
        $record->content = 'False:false,LastName:"Smith",true:true     ';

        $this->assertFalse($record->data->False);
        $this->assertSame("Smith", $record->data->LastName);
        $this->assertTrue($record->data->true);
    }

    public function testRecordMagicMethods()
    {
        $clusterID = 9;
        $recordPos = 8;
        $version = 7;
        $record = new OrientDBRecord();

        $this->assertNull($record->clusterID);
        $this->assertNull($record->recordPos);
        $this->assertNull($record->recordID);
        $this->assertNull($record->version);

        $record->clusterID = $clusterID;

        $this->assertSame($clusterID, $record->clusterID);
        $this->assertNull($record->recordPos);
        $this->assertNull($record->recordID);
        $this->assertNull($record->version);

        $record->clusterID = null;
        $record->recordPos = $recordPos;

        $this->assertNull($record->clusterID);
        $this->assertSame($recordPos, $record->recordPos);
        $this->assertNull($record->recordID);
        $this->assertNull($record->version);

        $record->clusterID = $clusterID;
        $record->recordPos = $recordPos;

        $this->assertSame($clusterID, $record->clusterID);
        $this->assertSame($recordPos, $record->recordPos);
        $this->assertSame($clusterID . ':' . $recordPos, $record->recordID);
        $this->assertNull($record->version);

        $record->version = $version;

        $this->assertSame($clusterID, $record->clusterID);
        $this->assertSame($recordPos, $record->recordPos);
        $this->assertSame($clusterID . ':' . $recordPos, $record->recordID);
        $this->assertSame($version, $record->version);

        $record->clusterID = 0;
        $record->recordPos = 0;

        $this->assertSame(0, $record->clusterID);
        $this->assertSame(0, $record->recordPos);
        $this->assertSame(0 . ':' . 0, $record->recordID);
    }

    public function testSetRecordID()
    {
        $record = new OrientDBRecord();
        $this->setExpectedException('PHPUnit_Framework_Error_Notice');
        $record->recordID = '#1:1';
    }

    public function testSetUnknownProperty()
    {
        $record = new OrientDBRecord();
        $this->setExpectedException('PHPUnit_Framework_Error_Notice');
        $record->unknown = true;
    }

    public function testGetUnknownProperty()
    {
        $record = new OrientDBRecord();
        $this->setExpectedException('PHPUnit_Framework_Error_Notice');
        $var = $record->unknown;
    }


    /**
     * @return void
     * @see http://code.google.com/p/orient/issues/detail?id=464
     */
    public function testParseRecordContentWithSingleQuote()
    {
        $record = new OrientDBRecord();
        $record->content = 'automatic:true,ignoreChars:" ' . chr(0x0d) . chr(0x0a) . chr(0x09) . ':;,.|+*/\\\\=!?[]()\'\"",type:"FULLTEXT"';

        $this->assertSame(true, $record->data->automatic);
        $this->assertSame(" \r\n\t:;,.|+*/\\=!?[]()'\"", $record->data->ignoreChars);
        $this->assertSame('FULLTEXT', $record->data->type);
    }

    /**
     * @return void
     * @see http://code.google.com/p/orient/issues/detail?id=464
     */
    public function testCreateRecordWithSingleQuote()
    {
        $record = new OrientDBRecord();
        $record->data->field = "'";

        $this->assertSame("'", $record->data->field);
        $this->assertSame('field:"\'"', (string) $record);
    }

    public function testParseRecordWithNumbersInFieldName()
    {
        $fieldName = 'FieldName_With-CharsAndNumbers56';
        $record = new OrientDBRecord();
        $record->content = $fieldName . ':1';

        $this->assertNotEmpty($record->data->$fieldName);
    }

    public function testParseRecordWithInvalidBoolean()
    {
        $record = new OrientDBRecord();
        $record->content = 'test:"test",fieldname:[fa]';
        $this->setExpectedException('OrientDBDeSerializeException');
        $record->parse();
    }

    public function testRecordCountable()
    {
        $record = new OrientDBRecord();
        $record->content = 'field1:1,field2:2';

        $this->assertSame(1, $record->data->field1);
        $this->assertSame(2, $record->data->field2);
        $this->assertSame(2, count($record->data));
    }

    public function testRecordIterator()
    {
        $record = new OrientDBRecord();
        $record->content = 'field1:1,field2:2';

        $fieldsAvailable = 0;
        foreach ($record->data as $key => $value) {
            $fieldsAvailable++;
        }
        $this->assertSame(2, $fieldsAvailable, 'Foreach failed');
    }

    public function testRecordNoKey()
    {
        $record = new OrientDBRecord();

        $this->setExpectedException('PHPUnit_Framework_Error_Notice', 'Undefined index: noSuchKey');
        $value = $record->data->noSuchKey;
    }

    public function testRecordDataIsSet()
    {
        $record = new OrientDBRecord();

        $this->assertFalse(isset($record->data->field));

        $record->content = 'field:"value"';
        $this->assertTrue(isset($record->data->field));
        $this->assertFalse(isset($record->data->none));
    }

    public function testRecordDataUnset()
    {
        $record = new OrientDBRecord();
        $record->content = 'field:"value"';
        $this->assertTrue(isset($record->data->field));
        $this->assertSame($record->data->getKeys(), array('field'));
        unset($record->data->field);
        $this->assertFalse(isset($record->data->field));
        $this->assertSame($record->data->getKeys(), array());
    }

    public function testParseRecordForced()
    {
        $content = 'ClassName@field:"text",link:#,bool:false';
        $record = new OrientDBRecord();
        $record->content = $content;

        $this->assertSame('ClassName', $record->className);
        $this->assertSame('text', $record->data->field);
        $this->assertSame('', (string) $record->data->link);
        $this->assertSame(false, $record->data->bool);
        $this->assertSame($content, (string) $record);
        $this->assertSame($record->data->getKeys(), array('field', 'link', 'bool'));

        $recordForced = new OrientDBRecord();
        $recordForced->content = $content;
        $recordForced->parse();
        $this->assertSame('ClassName', $recordForced->className);
        $this->assertSame('text', $recordForced->data->field);
        $this->assertSame('', (string) $recordForced->data->link);
        $this->assertSame(false, $recordForced->data->bool);
        $this->assertSame($content, (string) $recordForced);
        $this->assertSame($recordForced->data->getKeys(), array('field', 'link', 'bool'));
    }

    public function testRecordisParsedFlag()
    {
        $record = new OrientDBRecord();
        $flag = new ReflectionProperty('OrientDBRecord', 'isParsed');
        $flag->setAccessible(true);
        $record->content = 'field:"value"';
        $this->assertFalse($flag->getValue($record));
        $this->assertTrue(isset($record->data->field));
        $this->assertFalse(isset($record->data->none));
        $this->assertTrue($flag->getValue($record));
        $record->content = 'none:true';
        $this->assertFalse($flag->getValue($record));
        $this->assertTrue(isset($record->data->none));
        $this->assertFalse(isset($record->data->field));
        $this->assertTrue($flag->getValue($record));
    }

    public function testRecordGetClassName()
    {
        $class_name = 'testclass';
        $record = new OrientDBRecord();
        $record->className = $class_name;
        $this->assertSame($class_name, $record->className);
    }

    public function testRecordGetClassNameFromContent()
    {
        $content = 'NewClass@name:"Value"';
        $class_name = 'OldClass';
        $record = new OrientDBRecord();
        $record->className = $class_name;
        $this->assertSame('OldClass', $record->className);
        $record->content = $content;
        $this->assertNotSame($class_name, $record->className);
        $this->assertSame('NewClass', $record->className);
    }

    public function testRecordFullResetWithData()
    {
        $class_name = 'ResetTest';
        $cluster_id = 1;
        $record_pos = 2;
        $version = 3;

        $record = new OrientDBRecord();
        $record->className = $class_name;
        $record->data->Field = true;
        $record->clusterID = $cluster_id;
        $record->recordPos = $record_pos;
        $record->version = $version;

        $this->assertSame($class_name, $record->className);
        $this->assertTrue($record->data->Field);
        $this->assertSame($cluster_id, $record->clusterID);
        $this->assertSame($record_pos, $record->recordPos);
        $this->assertSame($cluster_id . ':' . $record_pos, $record->recordID);
        $this->assertSame($version, $record->version);
        $this->assertSame($record->data->getKeys(), array('Field'));

        $record->reset();

        $this->assertNull($record->className);
        $this->assertFalse(isset($record->data->Field));
        $this->assertNull($record->clusterID);
        $this->assertNull($record->recordPos);
        $this->assertNull($record->recordID);
        $this->assertNull($record->version);
        $this->assertSame($record->data->getKeys(), array());
    }

    public function testRecordFullResetWithString()
    {
        $class_name = 'ResetTest';
        $cluster_id = 1;
        $record_pos = 2;
        $version = 3;

        $record = new OrientDBRecord();
        $content = $class_name . '@Key:"Value"';
        $record->content = $content;
        $record->clusterID = $cluster_id;
        $record->recordPos = $record_pos;
        $record->version = $version;

        $this->assertSame('Value', $record->data->Key);
        $this->assertSame($class_name, $record->className);
        $this->assertSame($content, $record->content);
        $this->assertSame($record->data->getKeys(), array('Key'));

        $record->reset();

        $this->assertNull($record->className);
        $this->assertFalse(isset($record->data->Key));
        $this->assertNull($record->clusterID);
        $this->assertNull($record->recordPos);
        $this->assertNull($record->recordID);
        $this->assertNull($record->version);
        $this->assertNull($record->content);
        $this->assertSame($record->data->getKeys(), array());
    }

    public function testRecordResetDataWithData()
    {
        $class_name = 'ResetTest';
        $cluster_id = 1;
        $record_pos = 2;
        $version = 3;

        $record = new OrientDBRecord();
        $record->className = $class_name;
        $record->data->Field = true;
        $record->clusterID = $cluster_id;
        $record->recordPos = $record_pos;
        $record->version = $version;

        $this->assertSame($class_name, $record->className);
        $this->assertTrue($record->data->Field);
        $this->assertSame($cluster_id, $record->clusterID);
        $this->assertSame($record_pos, $record->recordPos);
        $this->assertSame($cluster_id . ':' . $record_pos, $record->recordID);
        $this->assertSame($version, $record->version);
        $this->assertSame($record->data->getKeys(), array('Field'));

        $record->resetData();

        $this->assertSame($class_name, $record->className);
        $this->assertFalse(isset($record->data->Field));
        $this->assertSame($cluster_id, $record->clusterID);
        $this->assertNull($record->recordPos);
        $this->assertNull($record->recordID);
        $this->assertNull($record->version);
        $this->assertSame($record->data->getKeys(), array());
    }

    public function testRecordResetDataWithString()
    {
        $class_name = 'ResetTest';
        $cluster_id = 1;
        $record_pos = 2;
        $version = 3;

        $record = new OrientDBRecord();
        $content = $class_name . '@Key:"Value"';
        $record->content = $content;
        $record->clusterID = $cluster_id;
        $record->recordPos = $record_pos;
        $record->version = $version;

        $this->assertSame('Value', $record->data->Key);
        $this->assertSame($class_name, $record->className);
        $this->assertSame($content, $record->content);
        $this->assertSame($record_pos, $record->recordPos);
        $this->assertSame($cluster_id . ':' . $record_pos, $record->recordID);
        $this->assertSame($version, $record->version);
        $this->assertSame($record->data->getKeys(), array('Key'));

        $record->resetData();

        $this->assertSame($class_name, $record->className);
        $this->assertFalse(isset($record->data->Key));
        $this->assertSame($cluster_id, $record->clusterID);
        $this->assertNull($record->recordPos);
        $this->assertNull($record->recordID);
        $this->assertNull($record->version);
        $this->assertNull($record->content);
        $this->assertSame($record->data->getKeys(), array());
    }
}