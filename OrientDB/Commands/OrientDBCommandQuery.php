<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * query() command for OrientDB-PHP
 * This is pseudo command, and its underlying is command()
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandQuery extends OrientDBCommandCommand
{
    public function __construct($parent)
    {
        $this->mode = OrientDB::COMMAND_QUERY;
        parent::__construct($parent);
    }
}