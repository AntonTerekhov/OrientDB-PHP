<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * indexSize() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandIndexSize extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::INDEX_SIZE;
    }

    public function prepare()
    {
        parent::prepare();
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parse()
     * @return int
     */
    protected function parse()
    {
        $this->debugCommand('size');
        $result = $this->readInt();
        return $result;
    }
}