<?php

$rootPassword = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';
$dbName = 'concurrent';
$recordsCreateFoo = 100000;

$clusterIDFoo = 5;





require 'OrientDB/OrientDB.php';

$db = new OrientDB('localhost');

// $db->setDebug(true);

$db->connect('root', $rootPassword);

$db->DBDelete($dbName);
$db->connect('root', $rootPassword);
$db->DBCreate($dbName, OrientDB::DB_TYPE_LOCAL);
$db->DBOpen($dbName, 'admin', 'admin');

$res = $db->query('CREATE CLASS Foo');
$res = $db->query('CREATE PROPERTY Foo.Bar integer');
$res = $db->query('CREATE INDEX Foo.Bar unique');
$res = $db->query('CREATE PROPERTY Foo.Qux integer');


echo 'Creating records Foo...' . PHP_EOL;
for ($i = 0; $i < $recordsCreateFoo; $i++) {
    $record = new OrientDBRecord();
    $record->className = 'Foo';
    $record->data->Bar = $i;
    $record->data->Qux = $recordsCreateFoo - $i;
    $db->recordCreate($clusterIDFoo, $record);

    if ($i % 100 == 0) {
        echo $i . '..';
    }
}

echo 'Done!' . PHP_EOL;