<?php
/**
 * This file can be used to speedtest OrientDB-PHP library on creating,
 * modifying and deleting records.
 * Before use, you must create DB named 'speedtest' manually.
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 * @subpackage Example
 */

require 'OrientDB/OrientDB.php';

$records = 100000;

$time_c = microtime(true);
echo 'Socket' . PHP_EOL;
$db = new OrientDB('localhost', 2424);
echo microtime(true) - $time_c . PHP_EOL;

$time_c = microtime(true);
echo 'OpenDB DB' . PHP_EOL;
$clusters = $db->DBOpen('speedtest', 'admin', 'admin');
echo microtime(true) - $time_c . PHP_EOL;

$time_c = microtime(true);
$result = array();
for ($i = 0; $i < $records; $i++) {
    try {
        $position = $db->recordCreate(2, 'Name:"Bill",Id:' . $i);
        $result[] = $position;
    }
    catch (OrientDBException $e) {
        echo $e->getMessage() . PHP_EOL;
    }
}
echo 'Done create ' . $records . PHP_EOL;
echo microtime(true) - $time_c . PHP_EOL;

$callback = function (&$item, $key)
{
    $item = '#2:' . $item;
};

$time_c = microtime(true);
$linked_records = array();
for ($i = 0; $i < $records / 100; $i++) {
    $rand_record = $result[array_rand($result)];
    $keys = array_rand($result, $records / 100);
    $links = array();
    foreach ($keys as $key) {
        $links[] = $result[$key];
    }
    array_walk($links, $callback);
    try {
        $db->recordUpdate('2:' . $rand_record, 'Name:"Bill",Id:' . $i . ', map:[' . implode(',', $links) . ']');
        $linked_records[] = $rand_record;
    }
    catch (OrientDBException $e) {
        echo $e->getMessage() . PHP_EOL;
    }
}
echo 'Done update ' . $records . PHP_EOL;
echo microtime(true) - $time_c . PHP_EOL;

$time_c = microtime(true);
$record = $db->recordLoad('2:' . $result[count($result) - 1], '*:-1');
echo 'Done get last' . PHP_EOL;
echo microtime(true) - $time_c . PHP_EOL;

$time_c = microtime(true);
$db->setDebug(true);
try {
    $record = $db->recordLoad('2:' . $linked_records[count($linked_records) - 1], '*:10');
}
catch (OrientDBException $e) {
    echo $e->getMessage() . PHP_EOL;
    echo OrientDBCommandAbstract::$transactionId . PHP_EOL;
}
$db->setDebug(false);
echo 'Done get linked' . PHP_EOL;
echo microtime(true) - $time_c . PHP_EOL;
var_dump($record->content);

$time_c = microtime(true);
for ($i = 0; $i < count($result); $i++) {
    try {
        $db->recordDelete('2:' . $result[$i]);
    }
    catch (OrientDBException $e) {
        echo $e->getMessage() . PHP_EOL;
    }
}
echo 'Done delete ' . $records . PHP_EOL;
echo microtime(true) - $time_c . PHP_EOL;



