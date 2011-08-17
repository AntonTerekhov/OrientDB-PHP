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
class OrientDBRecordSpeedBigTest extends PHPUnit_Framework_TestCase
{

    public function testDecodeRecordBig()
    {
        $runs = 10;
        $fieldsCnt = 10;

        $pwd = dirname(realpath(__FILE__));
        $text = file_get_contents($pwd . '/data/CharterofFundamentalRightsoftheEuropeanUnion.txt');

        $content = array();
        // Prepare some strings
        for ($i = 0; $i < $fieldsCnt; $i++) {
            $temp = $text;
            for ($j = 0; $j < 5; $j++) {
                $pos = rand(0, strlen($text));
                $temp = substr($temp, 0, $pos) . '"' . substr($temp, $pos + 1);
            }
            $content[] = 'text_' . $i . ':' . OrientDBRecordEncoder::encodeString($temp);
        }
        // Prepare some booleans
        for ($i = 0; $i < $fieldsCnt; $i++) {
            $content[] = sprintf('bool_%1$s:%2$s',  $i, (rand(0, 1) ? 'true' : 'false'));
        }
        // Prepare some links
        for ($i = 0; $i < $fieldsCnt; $i++) {
            $content[] = sprintf('link_%1$s:#%2$s:%3$s', $i, rand(1, 20), rand(0, 500000));
        }
        // Prepare some numbers
        for ($i = 0; $i < $fieldsCnt; $i++) {
            $content[] = sprintf('num_%1$s:%2$ff',  $i, rand(-2000000, 2000000));
        }
        // Some map
        $map = array();
        for ($i = 0; $i < $fieldsCnt; $i++) {
            $map[] = sprintf('"very.long.map_%1$05d":%2$d',  $i, rand(-2000, 2000));
        }
        $content[] = 'map:{' . implode(',', $map) . '}';
        $content = implode(',', $content);
        var_dump(strlen($content));
        $timeStart = microtime(true);
        for ($i = 0; $i < $runs; $i++) {
            $record = new OrientDBRecord();
            $record->content = $content;
            $record->parse();
        }
        $timeEnd = microtime(true);
        $this->assertNotEmpty($record->data);
         echo $timeEnd - $timeStart;
    }
}