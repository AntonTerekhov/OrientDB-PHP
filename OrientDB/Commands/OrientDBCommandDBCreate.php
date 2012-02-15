<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * DBCreate() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandDBCreate extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::DB_CREATE;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 2) {
            throw new OrientDBWrongParamsException('This command requires DB name and type');
        }
        // Add DB name
        $this->addString($this->attribs[0]);
        // Add DB type. Since rc9. Right now only document is supported
        $this->addString('document');
        // Add DB storage type
        $db_types = array(
            OrientDB::DB_TYPE_MEMORY,
            OrientDB::DB_TYPE_LOCAL);
        if (!in_array($this->attribs[1], $db_types)) {
            throw new OrientDBWrongParamsException('Not supported DB type. Supported types is: ' . implode(', ', $db_types));
        }
        $this->addString($this->attribs[1]);
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return bool
     */
    protected function parseResponse()
    {
        return true;
    }
}