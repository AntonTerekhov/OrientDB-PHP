# OrientDB-PHP #
A *plain* PHP driver to [OrientDB graph database](http://code.google.com/p/orient/) using [binary protocol](http://code.google.com/p/orient/wiki/NetworkBinaryProtocol).

Current status is: Alpha

Current OrientDB version to work with is: 1.0rc-1

Can work with 0.9.2.5-binary, but this OrientDB release had bug with only 20 new connections is accepted per server startup.


This library requires PHP 5.3.x

## Function list ##
### Create a new instance of OrientDB class ###
`
$db = new OrientDB($host, $port, $connectTimeout);
`

*Example:*

`
$db = new OrientDB('localhost', 2424);
`

### Connect to server ###
Connects to OrientDB server (not database) with user/passwd specified.
Returns true on success or throws exception.

`
$db->connect($userName, $password);
`

*Example:*

`
$db->connect('root', 'passwd');
`

### Database functions ###

#### DBOpen ####
Open database for work with or throws exception on failure (non-existent DB, wrong login or password).

`
$db->DBOpen($dbName, $userName, $password);
`

*Example:*

`
$db->DBOpen('demo', 'writer', 'writer');
`

#### DBClose ####
Closes currently opened database. 

Silently closes currently opened database, if any. Socket to OrientDB server is closed, and no futher commands are possible. Will throw an exception if no database are open on OrientDB instance.

`
$db->DBClose();
`

#### DBCreate ####
Creates new database.

`
$db->DBCreate($dbName, $dbType);
`

Avaliable types is: 

*   OrientDB::DB_TYPE_MEMORY for in memory database
*   OrientDB::DB_TYPE_LOCAL for physical database

For difference see official [OrientDB docs](http://code.google.com/p/orient/wiki/Concepts#Storage).

_Note: this function is now slightly unstable._

#### DBExists ####
Check if currently opened database is exists.

### Index functions ###

#### IndexKeys ####
Return list of keys in index.

#### IndexLookup ####
Return record by index key.

#### IndexPut ####
Put a record into index on key. Return previous record if exist.

#### IndexRemove ####
Remove key from index. Return record existed.


#### IndexSize ####
Return index size.


## Planned TODOs ##
Fix RecordPos with 64-bit Long
