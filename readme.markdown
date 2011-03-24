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
Open database for work with.

#### DBClose ####
Closes currently opened database.

#### DBCreate ####
Creates new database.

#### DBExists ####
Check if currently opened database is exists.

### Index functions ###

#### IndexKeys ####
Return list of keys in index.

#### IndexLookup ####
Return record by index key.

#### IndexPut ####
Put a record into index on key. Return record.

#### IndexRemove ####
Remove key from index. Return record.


#### IndexSize ####
Return index size.
## Planned TODOs ##
Fix RecordPos with 64-bit Long
