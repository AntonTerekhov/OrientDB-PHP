# OrientDB-PHP #
A *plain* PHP driver to [OrientDB graph database](http://code.google.com/p/orient/) using its [binary protocol](http://code.google.com/p/orient/wiki/NetworkBinaryProtocol).

## Description ##

Current status is: *Beta*. (Meaning specs can be changed. However, it's surely usable right now)

Code is licensed under New BSD License and provided "as is". For complete license information see file `LICENSE`.

Current OrientDB version to work with is: `1.2.0-SNAPSHOT`  (2012-08-20) (revision r6467).
It can be downloaded from OrientDB's [Downloads page](http://code.google.com/p/orient/downloads/list).

Code compatible to previous [binary releases](http://code.google.com/p/orient/downloads/list) of OrientDB can be found in repository's tags or in [Downloads](https://github.com/AntonTerekhov/OrientDB-PHP/archives/master) section.

Current protocol version implemented: **12**

## Requirements ##

This library requires:

* PHP 5.3.x
    * spl extension (since PHP 5.3.0 this extension is always available)
    * PCRE extension (as of PHP 5.3.0 this extension cannot be disabled and is therefore always present)
    * bcmath extension (Since PHP 4.0.4, libbcmath is bundled with PHP. These functions are only available if PHP was configured with --enable-bcmath .). Used on 32bit systems for dealing with 64bit long.

If PHP 5.3.x is a concern, you can try to run this code in version 5.2.x, however, this is not supported.

## Installing OrientDB-PHP ##
Main public repository of OrientDB-PHP is hosted at [https://github.com/AntonTerekhov/OrientDB-PHP](https://github.com/AntonTerekhov/OrientDB-PHP).

To install most recent version of library, just type

    git clone git://github.com/AntonTerekhov/OrientDB-PHP.git

where you want its file to be located.

You can also want to get latest stable version, so check out [Downloads](https://github.com/AntonTerekhov/OrientDB-PHP/archives/master) section. Stables are marked with tags including this library version and OrientDB version.

## Using OrientDB-PHP ##
OrientDB-PHP uses autoload functionality, so you only need to include `OrientDB.php` file.

    require 'OrientDB/OrientDB.php';

For a complex usage example see file `example.php`.

## Testing OrientDB-PHP ##
OrientDB-PHP is covered with automatic tests by [phpUnit](http://www.phpunit.de/manual/3.6/en/index.html). Tests are located in `Tests/` directory.

You can always re-test the whole library by typing

    phpunit Tests/

## Function list ##

Some functions requires to be already connected to OrientDB server (using `connect()`) or to have database opened (using `DBOpen()`). This can be referenced at [protocol description](http://code.google.com/p/orient/wiki/NetworkBinaryProtocol#Operation_types). If sequence is wrong - exception `OrientDBWrongCommandException` will be thrown and no interaction with server will be made.

### Create a new instance of OrientDB class ###

    $db = new OrientDB(string $host[, int $port[, int $connectTimeout]]);

*Example:*

    $db = new OrientDB('localhost', 2424);

### Connect to server ###
Connects to OrientDB server (not database) with user and password specified.
Returns `true` on success or throws exception.

    bool $db->connect(string $userName, string $password);

*Example:*

    $connected = $db->connect('root', 'passwd');

### Database functions ###

#### DBOpen ####
Open database for work with or throws exception on failure (non-existent DB, wrong login or password). Return array consist of cluster information and config.

    array $db->DBOpen(string $dbName, string $userName, string $password);

*Example:*

    $config = $db->DBOpen('demo', 'writer', 'writer');

#### DBClose ####
Closes currently opened database.

Silently closes currently opened database, if any. Socket to OrientDB server is closed, and no further commands are possible. Will throw an exception if no database are open on OrientDB instance.

    void $db->DBClose();

#### DBCreate ####
Creates new database. Return `true` on success or throw an exception.

    bool $db->DBCreate(string $dbName, string $dbType);

Available types is:

* `OrientDB::DB_TYPE_MEMORY` for in memory database
* `OrientDB::DB_TYPE_LOCAL` for physical database

For difference see official [OrientDB docs](http://code.google.com/p/orient/wiki/Concepts#Storage).

*Example:*

    $isCreated = $db->DBCreate('mydb', OrientDB::DB_TYPE_LOCAL);

#### DBDelete ####
Delete database with name provided. Always return `true`.

    bool $db->DBDelete(string $dbName);

*Example:*

    $result = $db->DBDelete('testdb');

#### DBExists ####
Checks if database with name provided is exists. Return `true` on success, `false` is no database exists or throws an exception.

    bool $db->DBExists(string $dbName);

*Example:*

    $isExists = $db->DBExists('demo');

#### DBList ####
Returns list of databases on server as array, where key is database name and value is string with schema and full disk path to database files on server.

    array $db->DBList();

*Example:*

    $list = $db->DBExists('demo');
    var_dump($list);

Can produce something like:

    array(3) {
      ["demo"]=>
      string(76) "local:/home/orientdb/databases/demo"
      ["temp"]=>
      string(11) "memory:temp"
    }

### Record manipulation functions ###

#### recordCreate ####
Create record in specified cluster with content and type. Returns record position in cluster.

    int $db->recordCreate( int  $clusterID, string|OrientDBRecord $recordContent[, string $recordType]);

Available record types are:

* `OrientDB::RECORD_TYPE_BYTES`
* `OrientDB::RECORD_TYPE_DOCUMENT`
* `OrientDB::RECORD_TYPE_FLAT`

Default type used is `OrientDB::RECORD_TYPE_DOCUMENT`.

*Example 1:*

    $recordPos = $db->recordCreate(1, 'name:"John"');

You can, however, use instance of class OrientDBRecord to create new entry in OrientDB server. If so, some of this instance properties (`clusterID`, `recordPos`, `recordID`, `version`) will be filled with correct values. See example below:

*Example 2:*

    $record = new OrientDBRecord();
    $record->data->name = 'John';

    $recordPos = $db->recordCreate(1, $record);

    echo $record->recordPos . PHP_EOL;
    echo $record->clusterID . PHP_EOL;
    echo $record->recordID . PHP_EOL;
    echo $record->version . PHP_EOL;

Can produce something like:

    1
    5
    1:5
    0

Due to [PHP's behavior](http://www.php.net/manual/en/language.oop5.references.php), objects are always passed by reference instead of int, for example. This, if automatically updating of record fields is not an option, can get you in trouble. So, in that case you should see example below:

*Example 3:*

    $record = new OrientDBRecord();
    $record->data->name = 'John';

    $recordPos = $db->recordCreate(1, (string) $record);

Please, note, that using OrientDBRecord instance doesn't automatically fill up other function parameters.

#### recordDelete ####
Delete record with specified recordID and optionally, version.
Returns `true` on success, `false` otherwise or throws an exception.

    bool $db->recordDelete(string $recordID[, int $recordVersion]);

Default version is `-1`. This means no version check will be done.

*Example:*

    $result = $db->recordDelete('1:1');

    $result = $db->recordDelete('1:1', 1);

#### recordLoad ####
Load record by recordID and, optionally, [fetchplan](http://code.google.com/p/orient/wiki/FetchingStrategies). Returns record or `false`. In some cases (e.g. recordPos is out of file bounds) can throw an exception

    OrientDBRecord $db->recordLoad(string $recordID[, string $fetchPlan]);

Default fetchplan is `*:0`, which mean load only record specified.

*Example:*

    $record = $db->recordLoad('1:1');

If fetchplan is explicit and there are some records returned by OrientDB, they located in `$db->cachedRecords` as associative array with keys from recordIDs and values are record themselves.

This *example*

    $record = $db->recordLoad('1:1', '*:-1');
    var_dump($db->cachedRecords);

Will produce something like this:

    array(2) {
        ["11:0"]=>
        object(OrientDBRecord)#178 (8) {
            ...

*During next call to any method which is able to populate `$db->cachedRecords` (e.g. `recordLoad()` or `command()`) this **array will be reset**.*

#### recordUpdate ####
Update record with specified recordID and, optionally, version.
Returns new record version on success, -1 otherwise or throws an exception.

    int $db->recordUpdate(string $recordID, string|OrientDBRecord $recordContent[, int $recordVersion[, string $recordType]]);

Default version is `-1`. This means no version check will be done.

Available record types are:

* `OrientDB::RECORD_TYPE_BYTES`
* `OrientDB::RECORD_TYPE_DOCUMENT`
* `OrientDB::RECORD_TYPE_FLAT`

Default type used is `OrientDB::RECORD_TYPE_DOCUMENT`.

*Examples 1:*

    $version = $db->recordUpdate('1:1', 'Name:"Bob"');
    $version = $db->recordUpdate('1:1', 'Name:"Sam"', 1, OrientDB::RECORD_TYPE_DOCUMENT);

You can, however, use instance of class OrientDBRecord to update record in OrientDB server. If so, some of this instance properties (`clusterID`, `recordPos`, `recordID`, `version`) will be filled with correct values. See example below:

*Example 2:*

    $record = new OrientDBRecord();
    $record->data->name = 'John';

    $recordPos = $db->recordUpdate('1:1', $record);

    echo $record->recordPos . PHP_EOL;
    echo $record->clusterID . PHP_EOL;
    echo $record->recordID . PHP_EOL;
    echo $record->version . PHP_EOL;

Can produce something like:

    1
    1
    1:1
    3

Due to [PHP's behavior](http://www.php.net/manual/en/language.oop5.references.php), objects are always passed by reference instead of int, for example. This, if automatically updating of record fields is not an option, can get you in trouble. So, in that case you should see example below:

*Example 3:*

    $record = new OrientDBRecord();
    $record->data->name = 'John';

    $recordPos = $db->recordUpdate('1:1', (string) $record);

Please, note, that using OrientDBRecord instance doesn't automatically fill up other function parameters.

### Config commands ###

#### configList ####
Get list of configurable options. Returns associative array with keys from option names and values themselves.

    array $db->configList();

*Example:*

    $options = $db->configList();

#### configGet ####
Get value for config option. Returns value as `string`. If option name not found returns empty `string`.

    string $db->configGet(string $optionName);

*Example:*

    $value = $db->configGet('log.console.level');

#### configSet ####
Set value for config option. Returns `true` on success or throws an exception.

    bool $db->configSet(string $optionName, string $optionValue);

*Example:*

    $result = $db->configSet('log.console.level', 'info');

### Datacluster commands ###

#### dataclusterAdd ####
Add new datacluster with specified name and type. Returns new cluster ID or throws an exception.

    int $db->dataclusterAdd(string $clusterName, string $clusterType);

Cluster types available are:

* `OrientDB::DATACLUSTER_TYPE_LOGICAL`
* `OrientDB::DATACLUSTER_TYPE_PHYSICAL`
* `OrientDB::DATACLUSTER_TYPE_MEMORY`

*Example:*

    $clusterID = $db->dataclusterAdd('testcluster', OrientDB::DATACLUSTER_TYPE_PHYSICAL);

#### dataclusterRemove ####
Removes datacluster by its ID. Returns `true` on success or throws an exception.

    bool $db->dataclusterRemove(int $clusterID);

*Example:*

    $result = $db->dataclusterRemove(10);

#### dataclusterCount ####
Counts elements in clusters specified by cluster IDs. Returns count or throws an exception.

    int $db->dataclusterCount(array $clusterIDs);

*Example:*

    $count = $db->dataclusterCount(array(1, 2));

#### dataclusterDatarange ####
Returns datarange for specified cluster ID. Returns array of `start` and `end` positions or throws an exception.

    array $db->dataclusterDatarange(int $clusterID);

*Example:*

    $data = $db->dataclusterDatarange(int $clusterID);

    array(2) {
        ["start"]=>
        int(0)
        ["end"]=>
        int(126)
    }

### Datasegment commands ###
Not implemented yet.

### commit ###
Commits a transaction. **Not yet implemented**.

### count ###
Get count of records in cluster specified by clusterName. Returns `int` or throws an exception.

    int $db->count(string $clusterName);

*Example:*

    $newcount = $db->count('default');

## Querying server ##
### command ###
This command provide an ability to execute remote [SQL commands](http://code.google.com/p/orient/wiki/SQL). Returns mixed or throws an exception.

    mixed $db->command(int $commandMode, string $query[, string $fetchplan]);

Command mode is required to be properly match with query text.

Command modes available are:

* `OrientDB::COMMAND_QUERY` - for general queries, including `INSERT`, `UPDATE`, `DELETE`, `FIND REFERENCES`, etc.
* `OrientDB::COMMAND_SELECT_SYNC` - only for `SELECT` in synchronous mode
* `OrientDB::COMMAND_SELECT_ASYNC` - only for `SELECT` in asynchronous mode

[Fetchplan](http://code.google.com/p/orient/wiki/FetchingStrategies) is used to pre-fetch some records. **Fetchplan is only available in `OrientDB::COMMAND_SELECT_ASYNC` mode.**
Using fetchplan will populate `$db->cachedRecords` array as for `recordLoad()`.

Default fetchplan is `*:0`.

*Examples:*

    $records = $db->command(OrientDB::COMMAND_SELECT_ASYNC, 'select * from city limit 7');
    $records = $db->command(OrientDB::COMMAND_SELECT_ASYNC, 'select from city traverse( any() )', '*:-1');
    $false = $db->command(OrientDB::COMMAND_SELECT_SYNC, 'select from 11:4 where any() traverse(0,10) (address.city = "Rome")');
    $links = $db->command(OrientDB::COMMAND_QUERY, 'find references 14:1');
    $record = $db->command(OrientDB::COMMAND_QUERY, 'insert into city (name, country) values ("Potenza", #14:1)');
    $updatedCount = $db->command(OrientDB::COMMAND_QUERY, 'update city set name = "Taranto" where name = "Potenza"');
    $deletedCount = $this->db->command(OrientDB::COMMAND_QUERY, 'delete from city where name = "Taranto"');

### select ###
Is an alias for command(OrientDB::COMMAND_SELECT_SYNC, string $query).

    mixed $db->select(string $query);

*Example:*

    $records = $db->select('select from city traverse( any() )');

### selectAsync ###
Is an alias for command(OrientDB::COMMAND_SELECT_ASYNC, string $query[, string $fetchplan]).

    mixed $db->selectAsync(string $query[, string $fetchplan]);

*Example:*

    $records = $db->selectAsync('select * from city limit 7', '*:-1');

### query ###
Is an alias for command(OrientDB::COMMAND_QUERY, string $query).

    mixed $db->query(string $query);

*Example:*

    $records = $db->query('insert into city (name, country) values ("Potenza", #14:1)   ');

### shutdown ###
Remotely shutdown OrientDB server. Require valid user name and password. See [manual](http://code.google.com/p/orient/wiki/NetworkBinaryProtocol#SHUTDOWN) for details.
Returns nothing on success or throws an exception.

    void $db->shutdown(string $userName, string $password);

*Example:*

    $db->shutdown('root', 'password');

## Exceptions list ##
For present moment OrientDB-PHP is using this list of exceptions:

* `OrientDBException` -  base exception, all exceptions listed below are extending this class. This class used as general error class (in case of OrientDB problems).
* `OrientDBConnectException` -  thrown on connect errors.
* `OrientDBDeSerializeException` -  thrown on de-serialization errors.
* `OrientDBWrongCommandException` - wrong command sequence exception, for example thrown on call `recordLoad()` if DB is not opened yet.
* `OrientDBWrongParamsException` - wrong params count or other param-related issues.

## OrientDBRecord ##
This class is representing OrientDB record.

Class is holding as much information from OrientDB as we received.

### Class fields ###

Class fields are:

* `className` - Class name from OrientDB.
* `type` - Document type from OrientDB. E.g. `OrientDB::RECORD_TYPE_DOCUMENT`.
* `clusterID` - Cluster ID, from which record was loaded.
* `recordPos` - Record position in cluster.
* `recordID` - Fully qualified record ID in format `clusterID:recordPos`.
* `version` - Document version from OrientDB.
* `content` - Document content as string in OrientDB's representation.
* `data` - placeholder where data, deserialized from `content`, is stored. Developer should manipulate this data in applications.

For complete information on fields data types see PHPDoc in class.

**At this point some class fields are public. Please, be careful.**

However, class fields `content`, `clusterID`, `recordPos`, `recordID` and `className` are using magic methods. All of them are available for reading, while only fields `content`, `clusterID`, `recordPos` and `className` for writing.

### OrientDBRecord Class methods ###

Class methods are:

* `parse()` - can be called after maximum amount of fields was populated. Parses `content` and fill up fields `data` and `className`. Field `recordPos` are filled up automatically on setting `recordID` or `clusterID` via magic method `__set()`.
In general, there is no need to call this method directly from user code, as record content is parsed automatically on request to any `data` or `className` fields. This is done via `OrientDBRecordData` class. This magic parsing only done once, until new `content` is assigned.
* `setParsed()` - forces that record was already parsed.
* `__toString()` - serialize back all fields from `data`. Return a string. Also can be called implicitly as type casting, e.g. `(string) $record`.
* `reset()` -  fully reset class fields, equals to `new`
* `resetData()` - will reset class data, except for `clusterID` and `className`.

Class is able to parse almost any [record format](http://code.google.com/p/orient/wiki/NetworkBinaryProtocol#Record_format) as received from OrientDB server. However, there are some limitations about few [Java primitive data types](http://download.oracle.com/javase/tutorial/java/nutsandbolts/datatypes.html), e.g. short. This is a planned TODO.

### OrientDBData Class ###
This class is used to store deserialized content of record. Deserialization is done "on the fly" while code accessing some of the class fields.

Class `OrientDBData` implements `Countable`, `Iterator` interfaces. As a result, you can use `foreach()` loop and `count()`:

    foreach ($record->data as $key => $value) {
        echo $key . '=' . $value . PHP_EOL;
    }

    echo count($record->data);

Class `OrientDBData` contains magic methods `__isset()` and `__unset()`, so any of class's properties can be checked with `isset()` and unsetted with `unset()`.

    if (isset($reord->data->key)) {
        unset($record->data->key);
    }

Also, class has method `getKeys()` which is similar to `array_keys`.

### Examples ###

*recordLoad*:

    $record = $db->recordLoad('12:1', '*:2');
    var_dump($record);

will produce

    object(OrientDBRecord)#197 (9) {
      ["className"]=>
      string(7) "Address"
      ["type"]=>
      string(1) "d"
      ["clusterID"]=>
      int(12)
      ["recordPos"]=>
      int(1)
      ["recordID"]=>
      string(4) "12:1"
      ["version"]=>
      int(0)
      ["content"]=>
      string(61) "Address@street:"Piazza Navona, 1",type:"Residence",city:#13:0"
      ["data"]=>
      object(stdClass)#172 (3) {
        ["street"]=>
        string(16) "Piazza Navona, 1"
        ["type"]=>
        string(9) "Residence"
        ["city"]=>
        object(OrientDBTypeLink)#195 (1) {
          ["link":"OrientDBTypeLink":private]=>
          string(4) "13:0"
        }
      }
    }

*recordCreate*

    $record = new OrientDBRecord();
    $record->data->FirstName = 'Bruce';
    $record->data->LastName = 'Wayne';
    $record->data->appearance = 1939;
    $recordPos = $db->recordCreate($clusterID, (string) $record);
    var_dump($db->recordLoad($clusterID . ':' . $recordPos));

will produce

    object(OrientDBRecord)#176 (9) {
      ["className"]=>
      NULL
      ["type"]=>
      string(1) "d"
      ["clusterID"]=>
      int(1)
      ["recordPos"]=>
      int(138)
      ["recordID"]=>
      string(5) "1:138"
      ["version"]=>
      int(0)
      ["content"]=>
      string(50) "FirstName:"Bruce",LastName:"Wayne",appearance:1939"
      ["data"]=>
      object(stdClass)#179 (3) {
        ["FirstName"]=>
        string(5) "Bruce"
        ["LastName"]=>
        string(5) "Wayne"
        ["appearance"]=>
        int(1939)
      }
    }


## Datatypes ##
Due to small quantity of PHP's built-in datatypes, this library is introducing some own datatypes.

### OrientDBLink ##
Used to link records with each other.

Two variants of constructing new instance is available:

    OrientDBTypeLink(string $value);

String value can be defined with or without leading hash sign.

    OrientDBTypeLink(int $clusterID, int $recordPos);

*Example 1*: String with hash sign

    $link = new OrientDBTypeLink('#100:99');
    echo $link . PHP_EOL;
    echo $link->getHash() . PHP_EOL;
    echo $link->get() . PHP_EOL;
    echo $link->clusterID . PHP_EOL;
    echo $link->recordPos . PHP_EOL;

*Example 2*: String without hash sign

    $link2 = new OrientDBTypeLink('100:99');
    echo $link2 . PHP_EOL;
    echo $link2->getHash() . PHP_EOL;
    echo $link2->get() . PHP_EOL;
    echo $link->clusterID . PHP_EOL;
    echo $link->recordPos . PHP_EOL;

*Example 3*: Two integers

    $link3 = new OrientDBTypeLink(100, 99);
    echo $link2 . PHP_EOL;
    echo $link2->getHash() . PHP_EOL;
    echo $link2->get() . PHP_EOL;
    echo $link->clusterID . PHP_EOL;
    echo $link->recordPos . PHP_EOL;

Output of all these examples would be the same:

    #100:99
    #100:99
    100:99
    100
    99

### OrientDBTypeTime ###
Used to store OrientDB date format with timestamps.

    OrientDBTypeLink(mixed $value);

*Example*:

    $date = new OrientDBTypeDate('1302631023t');
    $date2 = new OrientDBTypeDate(1302631023);


    echo (string) $date . PHP_EOL;
    echo  $date->getValue() . PHP_EOL;
    echo  $date->getTime() . PHP_EOL;

Both `$date` and `$date2` will output the same:

    1302631023t
    1302631023t
    1302631023

## Debugging with OrientDB-PHP ##
For debug purposes you can enable or disable debug output at anytime.

*Example*:

    $db->DBOpen('demo', 'writer', 'writer');
    $recordPos = $db->recordCreate($clusterID, $recordContent);
    $this->db->setDebug(true);
    $record = $db->recordLoad($clusterID . ':' . $recordPos);
    $this->db->setDebug(false);
    $result = $db->recordDelete($clusterID . ':' . $recordPos);

The above example will output debug messages only for `recordLoad()` to standard output stream (browser or console) in this manner:

         0 : 1e 00 00 00 04 00 01 00 00 00 00 00 00 00 8f 00 [................]
        10 : 00 00 03 2a 3a 30                               [...*:0]
    >request_status
         0 : 00                                              [.]
    >TransactionID
         0 : 00 00 00 04                                     [....]
    >record_status_first
         0 : 01                                              [.]
    >record_content
         0 : 00 00 00 0c                                     [....]
         0 : 74 65 73 74 72 65 63 6f 72 64 3a 30             [testrecord:0]
    >record_version
         0 : 00 00 00 00                                     [....]
    >record_type
         0 : 64                                              [d]
    >record_status_cache
         0 : 00                                              [.]

## Planned TODOs ##
* Full support on Java primitive data types, e.g. short or byte.
* Possible more OOP-style work with OrientDBRecord.
* Possible using [libevent](http://php.net/manual/en/book.libevent.php) for selectAsync().
* Support for async mode for RECORD_CREATE, RECORD_UPDATE, RECORD_DELETE
* Support for converting string `'true'` to actual boolean `true` (and other values) in SQL
* Use aliases for query serialization ('q' and 'c') instead of long class names
* Parse [special Linkset](http://code.google.com/p/orient/wiki/NetworkBinaryProtocol#Special_use_of_LINKSET_types)
* Internally process some OrientDB's exceptions and return false (For example - `DBDelete()`)

## Known bugs ##
* Connecting to OrientDB instance, which is listening 0.0.0.0 (default for OrientDB) can cause errors. Change to 127.0.0.1 in Orient's configuration. [See issue](http://code.google.com/p/orient/issues/detail?id=605)
* Only database with type 'document' is supported right now.

## If you found a bug ##
If you found a bug - feel free to contact me via gitHub, email, or open a new issue.