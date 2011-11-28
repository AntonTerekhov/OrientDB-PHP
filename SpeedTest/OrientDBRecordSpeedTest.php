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
class OrientDBRecordSpeedTest extends PHPUnit_Framework_TestCase
{

    public function testDecodeRecordTextValue()
    {
        $runs = 10;
        $pwd = dirname(realpath(__FILE__));
        $text = file_get_contents($pwd . '/data/CharterofFundamentalRightsoftheEuropeanUnion.txt');
        $content = 'text:' . OrientDBRecordEncoder::encodeString($text);
        $timeStart = microtime(true);
        for ($i = 0; $i < $runs; $i++) {
            $record = new OrientDBRecord();
            $record->content = $content;
            $record->parse();
        }
        $timeEnd = microtime(true);
        $this->assertSame($text, $record->data->text);
        // echo $timeEnd - $timeStart;
    }

    public function testDecodeRecordFieldNames()
    {
        $runs = 10;
        $fieldsCnt = 100;
        // Prepare a document
        $document = array();
        for ($i = 0; $i < $fieldsCnt; $i++) {
            $document[] = sprintf('%1$s_%2$s:"%2$s"', 'fieldname', $i);
        }
        $document = implode(',', $document);
        $timeStart = microtime(true);
        for ($i = 0; $i < $runs; $i++) {
            $record = new OrientDBRecord();
            $record->content = $document;
            $record->parse();
        }
        $timeEnd = microtime(true);
        $this->assertNotEmpty($record->data);
        // echo $timeEnd - $timeStart;
    }

    public function testDecodeRecordBooleanValue()
    {
        $runs = 10;
        $fieldCnt = 100;
        // Prepare a document
        $document = array();
        for ($i = 0; $i < $fieldCnt; $i++) {
            $document[] = sprintf('%1$s:%2$s',  $i, (rand(0, 1) ? 'true' : 'false'));
        }
        $document = implode(',', $document);
        $timeStart = microtime(true);
        for ($i = 0; $i < $runs; $i++) {
            $record = new OrientDBRecord();
            $record->content = $document;
            $record->parse();
        }
        $timeEnd = microtime(true);
        $this->assertNotEmpty($record->data);
        // echo $timeEnd - $timeStart;
    }

    public function testDecodeRecordLinkValue()
    {
        $runs = 10;
        $fieldCnt = 100;
        // Prepare a document
        $document = array();
        $rid = 100;
        for ($i = 0; $i < $fieldCnt; $i++) {
            $document[] = sprintf('%1$s:#%1$s:%2$s',  $i, $rid--);
        }
        $document = implode(',', $document);
        $timeStart = microtime(true);
        for ($i = 0; $i < $runs; $i++) {
            $record = new OrientDBRecord();
            $record->content = $document;
            $record->parse();
        }
        $timeEnd = microtime(true);
        $this->assertNotEmpty($record->data);
        // echo $timeEnd - $timeStart;
    }

    public function testDecodeRecordNumberValue()
    {
        $runs = 10;
        $fieldCnt = 100;
        // Prepare a document
        $document = array();
        for ($i = 0; $i < $fieldCnt; $i++) {
            $document[] = sprintf('%1$s:%2$ff',  $i, rand(-2000000, 2000000));
        }
        $document = implode(',', $document);
        $timeStart = microtime(true);
        for ($i = 0; $i < $runs; $i++) {
            $record = new OrientDBRecord();
            $record->content = $document;
            $record->parse();
        }
        $timeEnd = microtime(true);
        $this->assertNotEmpty($record->data);
        // echo $timeEnd - $timeStart;
    }

    public function testDecodeRecordMapValue()
    {
        $runs = 10;
        $fieldCnt = 100;
        // Prepare a document
        $map = array();
        for ($i = 0; $i < $fieldCnt; $i++) {
            $map[] = sprintf('"very.long.fieldname_%1$05d":%2$d',  $i, rand(-2000, 2000));
        }
        $map = implode(',', $map);
        $timeStart = microtime(true);
        for ($i = 0; $i < $runs; $i++) {
            $record = new OrientDBRecord();
            $record->content = 'map:{' . $map . '}';
            $record->parse();
        }
        $timeEnd = microtime(true);
        $this->assertNotEmpty($record->data->map);
        // echo $timeEnd - $timeStart;
    }

    public function testFullResetSpeed()
    {
        $steps = 10000;

        $new_start = microtime(true);
        for ($i = 0; $i < $steps; $i++) {
            $record = new OrientDBRecord();
            $record->className = 'TestClass';
            $record->data->field1 = 'Data 1';
            $record->data->field2 = 13121982;
            $record->data->field3 = true;
        }
        $new_end = microtime(true) - $new_start;

        $reset_start = microtime(true);
        $record = new OrientDBRecord();
        for ($i = 0; $i < $steps; $i++) {
            $record->reset();
            $record->className = 'TestClass';
            $record->data->field1 = 'Data 1';
            $record->data->field2 = 13121982;
            $record->data->field3 = true;
        }
        $reset_end = microtime(true) - $reset_start;
        $this->assertLessThanOrEqual($new_end, $reset_end, $new_end . ' !> ' . $reset_end);
    }

    public function testResetDataSpeed()
    {
        $steps = 10000;

        $new_start = microtime(true);
        for ($i = 0; $i < $steps; $i++) {
            $record = new OrientDBRecord();
            $record->className = 'TestClass';
            $record->data->field1 = 'Data 1';
            $record->data->field2 = 13121982;
            $record->data->field3 = true;
        }
        $new_end = microtime(true) - $new_start;

        $reset_start = microtime(true);
        $record = new OrientDBRecord();
        for ($i = 0; $i < $steps; $i++) {
            $record->resetData();
            $record->data->field1 = 'Data 1';
            $record->data->field2 = 13121982;
            $record->data->field3 = true;
        }
        $reset_end = microtime(true) - $reset_start;
        $this->assertLessThanOrEqual($new_end, $reset_end, $new_end . ' !> ' . $reset_end);
    }
}