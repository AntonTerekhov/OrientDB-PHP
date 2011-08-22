<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * count() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandCount extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::COUNT;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 1) {
            throw new OrientDBWrongParamsException('This command requires cluster name');
        }
        // Add cluster name
        $this->addString($this->attribs[0]);
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return int
     */
    protected function parseResponse()
    {
        $this->debugCommand('count');
        $count = $this->readLong();
        return $count;
    }
}