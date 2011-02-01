<?php



require 'OrientDB.php';

require '../orientdb/hex_dump.php';
require '../orientdb/orient_unserialize.php';

$db_connect = new OrientDB('localhost', 2424);

echo 'Connect...' . PHP_EOL;

$connect = $db_connect->connect('root', "95D5BA75CDCEDDDE610534B4D8BB13C6CD730665F7F10BCBF3F13F7E43F36A7F");

echo 'Config list' . PHP_EOL;

$options = $db_connect->configList();

var_dump($options);

$db = new OrientDB('localhost', 2424);

echo 'OpenDB non-existent DB' . PHP_EOL;

//$fault = $db->openDB('demo2', 'writer', 'writer');

echo 'OpenDB DB' . PHP_EOL;

$clusters = $db->openDB('demo', 'writer', 'writer');

var_dump($clusters);

echo 'Count class' . PHP_EOL;

$count = $db->count('default');

//var_dump($count);

echo 'Load record City:1' . PHP_EOL;

define('DEBUGE', true);

$record = $db->recordLoad('12:1', '*:1');

var_dump($record);

echo 'Create record City:' . PHP_EOL;

$recordId = $db->recordCreate(12, 'name:"Moscow"');
var_dump($recordId);

echo 'Load record City:' . $recordId . ' ' . PHP_EOL;

$record2 = $db->recordLoad('12:' . $recordId, '');

var_dump($record2);

echo 'Delete record City:' . $recordId . ' with version' . PHP_EOL;
try {
    $db->recordDelete('12:' . $recordId, 100);
} catch (OrientDBException $e) {
	echo $e->getMessage() . PHP_EOL;
}
echo 'Retry load record City:' . $recordId . ' ' . PHP_EOL;
$record3 = $db->recordLoad('12:' . $recordId, '');

var_dump($record3);

echo 'Retry delete record City:' . $recordId . ' ' . PHP_EOL;
$db->recordDelete('12:' . $recordId);

echo 'Retry delete record City:' . $recordId . ' #2' . PHP_EOL;
try {
    $db->recordDelete('12:' . $recordId);
} catch (OrientDBException $e) {
	echo $e->getMessage() . PHP_EOL;
}

//$db->closeDB();



//$v = 2147483649;
//var_dump($v);
//
//$p = pack('N', $v);
//hex_dump($p);
//
//$u = reset(unpack('N', $p));
//var_dump($u);