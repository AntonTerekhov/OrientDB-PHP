# OrientDB-PHP #
A *plain* PHP driver to [OrientDB graph database](http://code.google.com/p/orient/) using [binary protocol](http://code.google.com/p/orient/wiki/NetworkBinaryProtocol).

Current status is: *Alpha*

Current OrientDB version to work with is: `1.0rc-1`

Code compatible to previous binary releases of OrientDB can be found in repository's tags.

This library requires:

* PHP 5.3.x
    * spl extension (since PHP 5.3.0 this extension is always available)
    * PCRE extension (as of PHP 5.3.0 this extension cannot be disabled and is therefore always present)

## Function list ##
### Create a new instance of OrientDB class ###

    $db = new OrientDB(string $host, int $port[, int $connectTimeout]);

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

    $db->DBClose();

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
Checks if currently opened database is exists. Return `true` on success or throws an exception.

    bool $db->DBExists();

*Example:*

    $isExists = $db->DBExists();

### Index functions ###

#### IndexKeys ####
Returns list of keys in index as array.

    array $db->indexKeys();

*Example:*

    $keys = $db->indexKeys();

#### IndexLookup ####
Returns record by index key if any, otherwise return `false`.


    OrientDBRecord $db->indexLookup(string $key);

*Example:*

    $record = $db->indexLookup('myindexvalue');

#### IndexPut ####
Put a record into index on key. Returns record previously associated with that key if exist, otherwise returns `false`.

    OrientDBRecord $db->indexPut(string $key, string $recordID[, string OrientDB::RECORD_TYPE]);

Available record types are:

* `OrientDB::RECORD_TYPE_BYTES`
* `OrientDB::RECORD_TYPE_DOCUMENT`
* `OrientDB::RECORD_TYPE_FLAT`

Default value is `OrientDB::RECORD_TYPE_DOCUMENT`. For difference between types please consult with OrientDB manual.

*Example:*

    $record = $db->indexPut('myindexvalue', '1:1');

#### IndexRemove ####
Remove key from index. Returns existed record, if any, or `false` if no key exists.

    OrientDBRecord $db->indexRemove(string $key);

*Example:*

    $record = $db->indexRemove('myindexvalue');

#### IndexSize ####
Returns index size (count of keys in index).

    int $db->indexSize();

*Example:*

    $count = $db->indexSize();

### Record manipulation functions ###

#### RecordCreate ####
Create record in specified cluster with content and type. Returns record position in cluster.

    int $db->recordCreate( int  $clusterID, string $recordContent[, string $recordType]);

Available record types are:

* `OrientDB::RECORD_TYPE_BYTES`
* `OrientDB::RECORD_TYPE_DOCUMENT`
* `OrientDB::RECORD_TYPE_FLAT`

Default type used is `OrientDB::RECORD_TYPE_DOCUMENT`.

*Example:*

    $recordPos = $db->recordCreate(1, 'name:"John"');

#### RecordDelete ####
Delete record with specified recordID and optionally, version.
Returns `true` on success, `false` otherwise or throws an exception.

    bool $db->recordDelete(string $recordID[, int $recordVersion]);

Default version is `-1`. This means no version check will be done.

*Example:*

    $result = $db->recordDelete('1:1');

    $result = $db->recordDelete('1:1', 1);

#### RecordLoad ####
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
            ["classID"]=>
            int(7)
            ...

*During next call to any method which is able to populate `$db->cachedRecords` (e.g. `recordLoad()` or `command()`) this **array will be reset**.*

#### RecordUpdate ####
Update record with specified recordID and, optionally, version.
Returns `true` on success, `false` otherwise or throws an exception.

    bool $db->recordUpdate(string $recordID, string $recordContent[, int $recordVersion[, string $recordType]]);

Default version is `-1`. This means no version check will be done.

Available record types are:

* `OrientDB::RECORD_TYPE_BYTES`
* `OrientDB::RECORD_TYPE_DOCUMENT`
* `OrientDB::RECORD_TYPE_FLAT`

Default type used is `OrientDB::RECORD_TYPE_DOCUMENT`.

*Examples:*

    $result = $db->recordUpdate('1:1', 'Name:"Bob"');
    $result = $db->recordUpdate('1:1', 'Name:"Bob"', 1, OrientDB::RECORD_TYPE_DOCUMENT);

### Config commands ###

#### ConfigList ####
Get list of configurable options. Returns associative array with keys from option names and values themselves.

    array $db->configList();

*Example:*

    $options = $db->configList();

#### ConfigGet ####
Get value for config option. Returns value as `string`. If option name not found returns empty `string`.

    string $db->configGet(string $optionName);

*Example:*

    $value = $db->configGet('log.console.level');

#### ConfigSet ####
Set value for config option. Returns `true` on success or throws an exception.

    bool $db->configSet(string $optionName, string $optionValue);

*Example:*

    $result = $db->configSet('log.console.level', 'info');

### Datacluster commands ###

#### DataclusterAdd ####
Add new datacluster with specified name and type. Returns new cluster ID or throws an exception.

    int $db->dataclusterAdd(string $clusterName, string $clusterType);

Cluster types available are:

* `OrientDB::DATACLUSTER_TYPE_LOGICAL`
* `OrientDB::DATACLUSTER_TYPE_PHYSICAL`
* `OrientDB::DATACLUSTER_TYPE_MEMORY`

*Example:*

    $clusterID = $db->dataclusterAdd('testcluster', OrientDB::DATACLUSTER_TYPE_PHYSICAL);

#### DateclusterRemove ####
Removes datacluster by its ID. Returns `true` on success or throws an exception.

    bool $db->dataclusterRemove(int $clusterID);

*Example:*

    $result = $db->dataclusterRemove(10);

#### DataclusterCount ####
Counts elements in clusters specified by cluster IDs. Returns count or throws an exception.

    int $db->dataclusterCount(array $clusterIDs);

*Example:*

    $count = $db->dataclusterCount(array(1, 2));

#### DataclusterDatarange ####
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

### Commit ###
Commits a transaction. **Not yet implemented**.

### Count ###
Get count of records in cluster specified by clusterName. Returns `int` or throws an exception.

    int $db->count(string $clusterName);

*Example:*

    $newcount = $db->count('default');

### Command (querying server) ###
This command provide an ability to execute remote [SQL commands](http://code.google.com/p/orient/wiki/SQL). Returns mixed or throws an exception.

    mixed $db->command(string $query[, int $commandMode[, string $fetchplan]]);

Command mode is required to be properly match with query text.

Command modes available are:

* `OrientDB::COMMAND_QUERY` - for general queries, including `INSERT`, `UPDATE`, `DELETE`, `FIND REFERENCES`, etc.
* `OrientDB::COMMAND_SELECT_SYNC` - only for `SELECT` in synchronous mode
* `OrientDB::COMMAND_SELECT_ASYNC` - only for `SELECT` in asynchronous mode

Default mode is `OrientDB::COMMAND_SELECT_ASYNC`.

[Fetchplan](http://code.google.com/p/orient/wiki/FetchingStrategies) is used to pre-fetch some records. **Fetchplan is only available in `OrientDB::COMMAND_SELECT_ASYNC` mode.**
Using fetchplan will populate `$db->cachedRecords` array as for `recordLoad()`.

Default fetchplan is `*:0`.

*Examples:*

    $records = $db->command('select * from city limit 7');
    $records = $db->command('select from city traverse( any() )', OrientDB::COMMAND_SELECT_ASYNC, '*:-1');
    $false = $db->command('select from 11:4 where any() traverse(0,10) (address.city = "Rome")', OrientDB::COMMAND_SELECT_SYNC);
    $links = $db->command('find references 14:1', OrientDB::COMMAND_QUERY);
    $record = $db->command('insert into city (name, country) values ("Potenza", #14:1)', OrientDB::COMMAND_QUERY);
    $updatedCount = $db->command('update city set name = "Taranto" where name = "Potenza"', OrientDB::COMMAND_QUERY);
    $deletedCount = $this->db->command('delete from city where name = "Taranto"', OrientDB::COMMAND_QUERY);

### Shutdown ###
Remotely shutdown OriendDB server. Require valid user name and password. See [manual](http://code.google.com/p/orient/wiki/NetworkBinaryProtocol#SHUTDOWN) for details.
Returns nothing on success or throws an exception.

    void $db->shutdown(string $userName, string $password);

*Example:*

    $db->shutdown('root', 'password');

## Exceptions list ##
For present moment OrientDB-PHP is using this list of exceptions:

* `OrientDBException` -  base exception, all exceptions listed below are extending this class. This class used as general error class (in case of OrientDB problems).
* `OrientDBConnectException` -  thrown on connect errors.
* `OrientDBWrongCommandException` - wrong command sequence exception, for example thrown on call `recordLoad()` on DB not opened.
* `OrientDBWrongParamsException` - wrong params count or other param-related issues.

## OrientDBRecord ##
This is class representing record.

## Datatypes ##
Due to small quantity of PHP's built-in datatypes, this library is introducing some own datatypes.

### OrientDBLink ##
Used to link records with each other.

    OrientDBTypeLink(string $value);

Value can be defined with or without leading hash sign.

*Example 1*:

    $link = new OrientDBTypeLink('#100:99');
    echo $link . PHP_EOL;
    echo $link->getHash() . PHP_EOL;
    echo $link->get() . PHP_EOL;

*Example 2*:

    $link2 = new OrientDBTypeLink('#100:99');
    echo $link2 . PHP_EOL;
    echo $link2->getHash() . PHP_EOL;
    echo $link2->get() . PHP_EOL;

Output of these two examples would be the same:

    #100:99
    #100:99
    100:99

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

## Planned TODOs ##
Fix RecordPos with 64-bit Long
