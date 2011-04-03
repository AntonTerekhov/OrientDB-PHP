# OrientDB-PHP #
A *plain* PHP driver to [OrientDB graph database](http://code.google.com/p/orient/) using [binary protocol](http://code.google.com/p/orient/wiki/NetworkBinaryProtocol).

Current status is: *Alpha*

Current OrientDB version to work with is: `1.0rc-1`

Code compatible to previous binary releases of OrientDB can be found in repository's tags.

This library requires PHP 5.3.x

## Function list ##
### Create a new instance of OrientDB class ###

    $db = new OrientDB(string $host, int $port[, int $connectTimeout]);

*Example:*

    $db = new OrientDB('localhost', 2424);

### Connect to server ###
Connects to OrientDB server (not database) with user/passwd specified.
Returns true on success or throws exception.

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

Silently closes currently opened database, if any. Socket to OrientDB server is closed, and no futher commands are possible. Will throw an exception if no database are open on OrientDB instance.

    $db->DBClose();

#### DBCreate ####
Creates new database. Return true on success or throw an exception.

    bool $db->DBCreate(string $dbName, string $dbType);

Avaliable types is: 

* `OrientDB::DB_TYPE_MEMORY` for in memory database
* `OrientDB::DB_TYPE_LOCAL` for physical database

For difference see official [OrientDB docs](http://code.google.com/p/orient/wiki/Concepts#Storage).

_Note: this function is now slightly unstable._


*Example:*

    $isCreated = $db->DBCreate('mydb', OrientDB::DB_TYPE_LOCAL);

#### DBDelete ####
Delete database with name provided. Always return true.

    bool $db->DBDelete(string $dbName);

*Example:*

    $result = $db->DBDelete('testdb');

#### DBExists ####
Check if currently opened database is exists. Return true on success or throws an exception.

    bool $db->DBExists();

*Example:*

    $isExests = $db->DBExists();

### Index functions ###

#### IndexKeys ####
Return list of keys in index as array.

    array $db->indexKeys();

*Example:*

    $keys = $db->indexKeys();

#### IndexLookup ####
Return record by index key if any, otherwise return false.


    OrientDBRecord $db->indexLookup(string $key);

*Example:*

    $record = $db->indexLookup('myindexvalue');

#### IndexPut ####
Put a record into index on key. Return previously assotiated with that key record if exist, otherwise return false.

    OrientDBRecord $db->indexPut(string $key, string $recordID[, string OrientDB::RECORD_TYPE]);

Avaliable record types are:

* `OrientDB::RECORD_TYPE_BYTES`
* `OrientDB::RECORD_TYPE_DOCUMENT`
* `OrientDB::RECORD_TYPE_FLAT`

Default value is `OrientDB::RECORD_TYPE_DOCUMENT`. For difference between types please consult OrientDB manual.

*Example:*

    $record = $db->indexPut('myindexvalue', '1:1');

#### IndexRemove ####
Remove key from index. Return record existed or false is no key exists.

    OrientDBRecord $db->indexRemove(string $key);

*Example:*

    $record = $db->indexRemove('myindexvalue');

#### IndexSize ####
Return index size (count of keys in index).

    int $db->indexSize();

*Example:*

    $count = $db->indexSize();

### Record manipulation functions ###

#### RecordCreate ####
Create record in specified cluster with content and type. Return record position in cluster.

    int $db->recordCreate( int  $clusterID, string $recordContent[, string $recordType]);

Record types avaliable: 

* `OrientDB::RECORD_TYPE_BYTES`
* `OrientDB::RECORD_TYPE_DOCUMENT`
* `OrientDB::RECORD_TYPE_FLAT`

Default type is `OrientDB::RECORD_TYPE_DOCUMENT`

*Example:*

    $recordPos = $db->recordCreate(1, 'name:"John"');

#### RecordDelete ####
Delete record with specified recordID and optionally, version.
Return true on success, false otherwise or exception.

    bool $db->recordDelete(string $recordID[, int $recordVersion]);

Default version is -1.

*Example:*

    $result = $db->recordDelete('1:1');

    $result = $db->recordDelete('1:1', 1);

#### RecordLoad ####
Load record by recordID and, optionally, [fetchplan](http://code.google.com/p/orient/wiki/FetchingStrategies). Return record.

    OrientDBRecord $db->recordLoad(string $recordID[, string $fetchPlan]);

Default fetchplan is `*:0`, which mean load only record specified.

*Example:*

    $record = $db->recordLoad('1:1');

If fetchplan is explicit and there are some records returned by OrientDB, they located in `$db->cachedRecords` as assotive array with keys from recordIDs and values are record themselves. 

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

*During next call to any method able to populate in `$db->cachedRecords` (e.g. `recordLoad()` or `command()`) this **array will be reset**.*

#### RecordUpdate ####
Update record with specified recordID and optionally, version.
Return true on success, false otherwise or exception.

    bool $db->recordUpdate(string $recordID, string $recordContent[, int $recordVersion[, string $recordType]]);

Default version is -1.

Record types avaliable:

* `OrientDB::RECORD_TYPE_BYTES`
* `OrientDB::RECORD_TYPE_DOCUMENT`
* `OrientDB::RECORD_TYPE_FLAT`

Default type is `OrientDB::RECORD_TYPE_DOCUMENT`

*Example:*

    $result = $db->recordDelete('1:1', 'Name:"Bob"');

    $result = $db->recordDelete('1:1', 'Name:"Bob"', 1, OrientDB::RECORD_TYPE_DOCUMENT);

### Config commands ###

#### ConfigList ####
Get list of configurable optons. Return assotiative array with option name as key and values themselves.

    array $db->configList();

*Example:*

    $options = $db->configList();

#### ConfigGet ####
Get value for config option. Return value as string.

    string $db->configGet(string $optionName);

*Example:*

    $value = $db->configGet('log.console.level');

#### ConfigSet ####
Set value for config option. Return true on success or thow an exception.

    bool $db->configSet(string $optionName, string $optionValue);

*Example:*

    $result = $db->configSet('log.console.level', 'info');

### Datacluster commands ###

#### DataclusterAdd ####
Add new datacluster with specified name and type. Return new cluster ID.

    int $db->dataclusterAdd(string $clusterName, string $clusterType);

Cluster types avaliable:
* `OrientDB::DATACLUSTER_TYPE_LOGICAL`
* `OrientDB::DATACLUSTER_TYPE_PHYSICAL`
* `OrientDB::DATACLUSTER_TYPE_MEMORY`

*Example:*

    $clusterID = $db->dataclusterAdd('testcluster', OrientDB::DATACLUSTER_TYPE_PHYSICAL);

#### DateclusterRemove ####
Removes datacluster by its ID. Return true on success or throw an exception.

    bool $db->dataclusterRemove(int $clusterID);

*Example:*

    $result = $db->dataclusterRemove(10);

#### DataclusterCount ####
Counts elements in cluster specified. Return count or throw an exception.

    int $db->dataclusterCount(array $clusterIDs);

*Example:*

    $count = $db->dataclusterCount(array(1, 2));

#### DataclusterDatarange ####
Return datarange for cluster ID specified. Return array of `start` and `end` positions or throw exception.

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
Get count of records in specified cluster. Return int or throw an exception.

    int $db->count(string $clusterName);

*Example:*

    $newcount = $db->count('default');

### Command (querying server) ###
This command provide an ability to execute remote [SQL commands](http://code.google.com/p/orient/wiki/SQL). Return mixed or throw an exception.

    mixed $db->command(string $query[, int $commandMode[, string $fetchplan]]);

Command mode is requred to be properly match query text.

Avaliable modes are:
* `OrientDB::COMMAND_QUERY` - for general queryes, including `INSERT`, `UPDATE`, `DELETE`, `FIND REFERENCES`, etc.
* `OrientDB::COMMAND_SELECT_SYNC` - only for `SELECT` in synchronious mode
* `OrientDB::COMMAND_SELECT_ASYNC` - only for `SELECT` in asynchronious mode

Default mode is `OrientDB::COMMAND_SELECT_ASYNC`.

[Fetchplan](http://code.google.com/p/orient/wiki/FetchingStrategies) is used to prefetch some records. **Fetchplan is only avaliable in `OrientDB::COMMAND_SELECT_ASYNC` mode.**
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
Remotely shutdown OriendDB server. Require valid user name and password. See [manual](http://code.google.com/p/orient/wiki/NetworkBinaryProtocol#SHUTDOWN) fro details.
Return nothing on success or thow an exception.

    void $db->shutdown(string $userName, string $password);

*Example:*

    $db->shutdown('root', 'password');

## Exceptions list ##
For present moment OrientDB using this list of exceptions:

* `OrientDBException` -  base exception, all exceptions listed below are extending this class. This class used as general error class (in case of OrientDB problems).
* `OrientDBConnectException` -  throws on connect errors.
* `OrientDBWrongCommandException` - wrong command sequence exception, for example recordLoad() on not opened DB.
* `OrientDBWrongParamsException` - wrong params count or other param-related issues.

## OrientDBRecord ##
This is class representing record.

## Planned TODOs ##
Fix RecordPos with 64-bit Long
