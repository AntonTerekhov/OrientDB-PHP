<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * indexKeys() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandIndexKeys extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::INDEX_KEYS;
    }

    public function prepare()
    {
        parent::prepare();
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parse()
     * @return array
     */
    protected function parse()
    {
        $this->debugCommand('keys_count');
        $keysCount = $this->readInt();
        $keys = array();
        for ($i = 0; $i < $keysCount; $i++) {
            $this->debugCommand('read_key');
            $keys[] = $this->readString();
        }
        return $keys;
    }
}