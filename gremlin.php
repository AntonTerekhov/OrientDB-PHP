<?php

require_once('OrientDB/OrientDB.php');

$db = new OrientDB('localhost');

$db->DBOpen('tinkerpop', 'admin', 'admin');
$result = $db->selectGremlin('g.v1.outE', '*:-1');

var_dump(count($result));

var_dump(count($db->cachedRecords));