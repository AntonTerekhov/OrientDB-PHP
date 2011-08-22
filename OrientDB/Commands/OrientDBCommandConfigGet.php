<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * configGet() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandConfigGet extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::CONFIG_GET;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 1) {
            throw new OrientDBWrongParamsException('This command requires config name');
        }
        // Add option name
        $this->addString($this->attribs[0]);
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return string
     */
    protected function parseResponse()
    {
        $this->debugCommand('config_value');
        $value = $this->readString();

        return $value;
    }
}