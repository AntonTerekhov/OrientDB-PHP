<?php



require 'OrientDB/OrientDB.php';

require 'OrientDB/helpers/hex_dump.php';

try {
    $failed = new OrientDB('localhost', 2420);
} catch (Exception $e) {
	echo 'Failded to connect' .  PHP_EOL;
}


$db_connect = new OrientDB('localhost', 2424);

echo 'Connect...' . PHP_EOL;

$connect = $db_connect->connect('root', "60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130");

echo 'Config list' . PHP_EOL;
$options = $db_connect->configList();

var_dump($options);

$optionName = 'log.console.level';
echo 'Get option "' . $optionName . '"' . PHP_EOL;
try {
    $result = $db_connect->ConfigGet($optionName);
    var_dump($result);
} catch (OrientDBException $e) {
    echo $e->getMessage() . PHP_EOL;
}

$optionValue = 'info';
echo 'Set option "' . $optionName . '":"' . $optionValue . '"' . PHP_EOL;
try {
    $result = $db_connect->ConfigSet($optionName, $optionValue);
    var_dump($result);
} catch (OrientDBException $e) {
    echo $e->getMessage() . PHP_EOL;
}

echo 'DB Create' . PHP_EOL;
try {
    //$result = $db_connect->DBCreate('name2', 'local');
    //var_dump($result);
} catch (OrientDBException $e) {
	echo $e->getMessage() . PHP_EOL;
}


echo 'DB Exists' . PHP_EOL;
try {
    $result = $db_connect->DBExists();
    var_dump($result);
} catch (OrientDBException $e) {
    echo $e->getMessage() . PHP_EOL;
}

$db = new OrientDB('localhost', 2424);

echo 'OpenDB non-existent DB' . PHP_EOL;
try {
    $fault = $db->DBOpen('demo2', 'writer', 'writer');
} catch (OrientDBException $e) {
	echo $e->getMessage() . PHP_EOL;
}

echo 'OpenDB DB' . PHP_EOL;
try {
    $clusters = $db->DBOpen('demo', 'writer', 'writer');
    var_dump($clusters);
} catch (OrientDBException $e) {
    echo $e->getMessage() . PHP_EOL;
}

echo 'Count class' . PHP_EOL;
$count = $db->count('default');

var_dump($count);

echo 'Load record City:1' . PHP_EOL;
$record = $db->recordLoad('12:1', '*:1');

var_dump($record);

echo 'Create record City:' . PHP_EOL;
$recordId = $db->recordCreate(12, 'name:"Moscow"');

var_dump($recordId);

echo 'Load record City:' . $recordId . ' ' . PHP_EOL;
$record2 = $db->recordLoad('12:' . $recordId, '');

var_dump($record2);

$db->setDebug(true);
echo 'Delete record City:' . $recordId . ' with version' . PHP_EOL;
try {
    $db->recordDelete('12:' . $recordId, 100);
} catch (OrientDBException $e) {
	echo $e->getMessage() . PHP_EOL;
}

echo 'Retry load record City:' . $recordId . ' ' . PHP_EOL;
$record3 = $db->recordLoad('12:' . $recordId, '');

var_dump($record3);
$db->setDebug(false);

echo 'Retry delete record City:' . $recordId . ' ' . PHP_EOL;
$db->recordDelete('12:' . $recordId);

echo 'Retry delete record City:' . $recordId . ' #2' . PHP_EOL;
try {
    $db->recordDelete('12:' . $recordId);
} catch (OrientDBException $e) {
	echo $e->getMessage() . PHP_EOL;
}

echo 'Create record City:' . PHP_EOL;
$recordId2 = $db->recordCreate(12, 'name:"Spb"pop:12000');

var_dump($recordId2);

echo 'Load record City:' . $recordId2 . ' ' . PHP_EOL;
$record2 = $db->recordLoad('12:' . $recordId2, '');

echo 'Update record City:' . $recordId2 . ' ' . PHP_EOL;
$result = $db->recordUpdate('12:' . $recordId2, $record2->content);

var_dump($result);

echo 'Load record City:' . $recordId2 . ' ' . PHP_EOL;
$record2 = $db->recordLoad('12:' . $recordId2, '');

var_dump($record2);

echo 'Update record City:' . $recordId2 . ' with wrong version ' . PHP_EOL;
try {
    $result = $db->recordUpdate('12:' . $recordId2, $record2->content . 'version:100', 0);
    var_dump($result);
} catch (OrientDBException $e) {
	echo $e->getMessage() . PHP_EOL;
}

echo 'Update record City:' . $recordId2 . ' with correct version ' . PHP_EOL;
try {
    $result = $db->recordUpdate('12:' . $recordId2, 'version:100', $record2->version);
} catch (OrientDBException $e) {
    echo $e->getMessage() . PHP_EOL;
}
var_dump($result);


echo 'Load record City:' . $recordId2 . ' ' . PHP_EOL;
$record2 = $db->recordLoad('12:' . $recordId2, '');

var_dump($record2);

echo 'Update record City:' . $recordId2 . ' with wrong document type ' . PHP_EOL;
try {
    $result = $db->recordUpdate('12:' . $recordId2, '789:"123123"', $record2->version, OrientDB::RECORD_TYPE_COLUMN);
} catch (OrientDBException $e) {
    echo $e->getMessage() . PHP_EOL;
}
var_dump($result);

echo 'Load record City:' . $recordId2 . ' ' . PHP_EOL;
$record3 = $db->recordLoad('12:' . $recordId2, '');

var_dump($record3);

echo 'Delete record City:' . $recordId2 . ' ' . PHP_EOL;
$result = $db->recordDelete('12:' . $recordId2);

var_dump($result);


echo 'Dictionary size' . PHP_EOL;
$result = $db->DictionarySize();
var_dump($result);


echo 'Create record City:' . PHP_EOL;
$recordId3 = $db->recordCreate(12, 'name:"Tunguska"pop:100');

$db_admin = new OrientDB('localhost', 2424);
$db_admin->DBOpen('demo', 'admin', 'admin');
$db_admin->setDebug(true);
echo 'Dictionary put:' . $recordId3 . PHP_EOL;
try {
    $result = $db_admin->dictionaryPut('mykey', OrientDB::RECORD_TYPE_DOCUMENT, '12:' . $recordId3);
} catch (OrientDBException $e) {
    echo $e->getMessage() . PHP_EOL;
}
var_dump($result);


echo 'Dictionary size' . PHP_EOL;
$result = $db_admin->DictionarySize();
var_dump($result);


echo 'Dictionary keys' . PHP_EOL;
$result = $db_admin->DictionaryKeys();
var_dump(count($result));

//$db->closeDB();



//$v = 2147483649;
//var_dump($v);
//
//$p = pack('N', $v);
//hex_dump($p);
//
//$u = reset(unpack('N', $p));
//var_dump($u);
