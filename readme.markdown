# OrientDB-PHP #
A *plain* PHP driver to [OrientDB graph database](http://code.google.com/p/orient/).

Current status is: Alpha
Current OrientDB version is: 1.0rc-1

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

## Planned TODOs ##
Fix RecordPos with 64-bit Long
