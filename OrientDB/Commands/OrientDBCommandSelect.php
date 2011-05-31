<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * select() command for OrientDB-PHP
 * This is pseudo command, and its underlying is command()
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandSelect extends OrientDBCommandCommand
{
    public function __construct($parent)
    {
        $this->mode = OrientDB::COMMAND_SELECT_SYNC;
        parent::__construct($parent);
    }
}