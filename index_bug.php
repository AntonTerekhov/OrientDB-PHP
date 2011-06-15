<?php
require 'OrientDB/OrientDB.php';

$db = new OrientDB('localhost');


$db->connect('root', '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130');

$result = $db->DBCreate('antontest', OrientDB::DB_TYPE_LOCAL);

$db->DBOpen('antontest', 'admin', 'admin');
$clusterID = $db->dataclusterAdd('qux', OrientDB::DATACLUSTER_TYPE_PHYSICAL);
$db->setDebug(true);
$db->query('CREATE CLASS qux');
$db->query('CREATE PROPERTY qux.Bar integer');
$db->query('CREATE INDEX qux.Bar UNIQUE');



$record = new OrientDBRecord();
$record->className = 'qux';
$record->data->Bar = 2000;


$db->recordCreate($clusterID, $record);
//$db->query('INSERT INTO qux (Bar) VALUES (2000)');
$db->setDebug(false);
$info = $db->selectAsync('select from qux where Bar = 2000');
var_dump($info);