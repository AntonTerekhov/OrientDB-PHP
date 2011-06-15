<?php

$rootPassword = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';
$dbName = 'updatetest';
$recordsCreateFoo = 20000;
$recordsCreateBar = 10;

$clusterIDFoo = 5;
$clusterIDBar = 6;





require 'OrientDB/OrientDB.php';

$db = new OrientDB('localhost');

//$db->setDebug(true);

$db->connect('root', $rootPassword);

$db->DBDelete($dbName);
$db->connect('root', $rootPassword);
$db->DBCreate($dbName, OrientDB::DB_TYPE_LOCAL);
$db->DBOpen($dbName, 'admin', 'admin');

$timeStart = microtime(true);

$res = $db->query('CREATE CLASS Foo');
//$res = $db->query('CREATE PROPERTY Foo.Int integer');
$res = $db->query('CREATE CLASS Bar');
$res = $db->query('CREATE PROPERTY Bar.Num integer');
$res = $db->query('CREATE INDEX Bar.Num unique');
$res = $db->query('CREATE PROPERTY Bar.Foos linklist Foo');


echo 'Creating records Bar...' . PHP_EOL;
$timeBarStart = microtime(true);
for ($i = 0; $i < $recordsCreateBar; $i++) {
    $record = new OrientDBRecord();
    $record->className = 'Bar';
    $record->data->Num = $i;
    $record->data->Foos = array();
    $db->recordCreate($clusterIDBar, $record);
}
echo 'Done create ' . $recordsCreateBar . ' Bar records in ' . (microtime(true) - $timeBarStart) . PHP_EOL;
//die();

echo 'Creating records Foo...' . PHP_EOL;
$timeFooStart = microtime(true);
$timeFooLap = microtime(true);
for ($i = 0; $i < $recordsCreateFoo; $i++) {
    $record = new OrientDBRecord();
    $record->className = 'Foo';
//    $record->data->Int = $i;
    $db->recordCreate($clusterIDFoo, $record);

    $sql = 'UPDATE Bar ADD Foos = #' . $record->recordID . ' WHERE Num = ' . rand(0, 9);
    $result = $db->query($sql);

    if ($i % 100 == 0) {
        echo $i . "\t..\t" . (microtime(true) - $timeFooLap) . PHP_EOL;
        $timeFooLap = microtime(true);
    }
}

echo PHP_EOL . 'Done create ' . $recordsCreateFoo . ' Foo records in ' . (microtime(true) - $timeFooStart) . PHP_EOL;

echo 'Done! In ' . (microtime(true) - $timeStart) . PHP_EOL;