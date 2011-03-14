# OrientDB-PHP #
A *plain* PHP driver to [OrientDB graph database](http://code.google.com/p/orient/) using [binary protocol](http://code.google.com/p/orient/wiki/NetworkBinaryProtocol).

Current status is: Alpha

Current OrientDB version to work with is: 1.0rc-1

Can work with 0.9.2.5-binary, but this OrientDB release had bug with only 20 new connections is accepted per server startup.


This library requires PHP 5.3.x

## Function list ##
### Create a new instance of OrientDB class ###
`
$db = new OrientDB(string $host, int $port[, int $connectTimeout]);
`

*Example:*

`
$db = new OrientDB('localhost', 2424);
`

### Connect to server ###
Connects to OrientDB server (not database) with user/passwd specified.
Returns true on success or throws exception.

`
bool $db->connect(string $userName, string $password);
`

*Example:*

`
$connected = $db->connect('root', 'passwd');
`

### Database functions ###

#### DBOpen ####
Open database for work with or throws exception on failure (non-existent DB, wrong login or password). Return array consist of cluster information and config.

`
array $db->DBOpen(string $dbName, string $userName, string $password);
`

*Example:*

`
$config = $db->DBOpen('demo', 'writer', 'writer');
`

#### DBClose ####
Closes currently opened database. 

Silently closes currently opened database, if any. Socket to OrientDB server is closed, and no futher commands are possible. Will throw an exception if no database are open on OrientDB instance.

`
$db->DBClose();
`

#### DBCreate ####
Creates new database. Return true on success or throw an exception.

`
bool $db->DBCreate(string $dbName, string $dbType);
`

Avaliable types is: 

* `OrientDB::DB_TYPE_MEMORY` for in memory database
* `OrientDB::DB_TYPE_LOCAL` for physical database

For difference see official [OrientDB docs](http://code.google.com/p/orient/wiki/Concepts#Storage).

_Note: this function is now slightly unstable._


*Example:*

`
$isCreated = $db->DBCreate('mydb', OrientDB::DB_TYPE_LOCAL);
`

#### DBExists ####
Check if currently opened database is exists. Return true on success or throws an exception.

`
bool $db->DBExists();
`

*Example:*

`
$isExests = $db->DBExists();
`

### Index functions ###

#### IndexKeys ####
Return list of keys in index as array.

`
array $db->indexKeys();
`

*Example:*

`
$keys = $db->indexKeys();
`


#### IndexLookup ####
Return record by index key if any, otherwise return false.

`
OrientDBRecord $db->indexLookup(string $key);
`

*Example:*

`
$record = $db->indexLookup('myindexvalue');
`

#### IndexPut ####
Put a record into index on key. Return previously assotiated with that key record if exist, otherwise return false.

`
OrientDBRecord $db->indexPut(string $key, string $recordID[, string OrientDB::RECORD_TYPE]);
`

Avaliable record types are:

* `OrientDB::RECORD_TYPE_BYTES`
* `OrientDB::RECORD_TYPE_DOCUMENT`
* `OrientDB::RECORD_TYPE_FLAT`

Default value is `OrientDB::RECORD_TYPE_DOCUMENT`. For difference between types please consult OrientDB manual.

*Example:*

`
$record = $db->indexPut('myindexvalue', '1:1');
`

#### IndexRemove ####
Remove key from index. Return record existed or false is no key exists.

`
OrientDBRecord $db->indexRemove(string $key);
`

*Example:*

`
$record = $db->indexRemove('myindexvalue');
`

#### IndexSize ####
Return index size (count of keys in index).

`
int $db->indexSize();
`

*Example:*

`
$count = $db->indexSize();
`

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
