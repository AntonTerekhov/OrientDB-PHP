<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * configList() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandConfigList extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::CONFIG_LIST;
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return array
     */
    protected function parseResponse()
    {
        $this->debugCommand('options_count');
        $numOptions = $this->readShort();

        $options = array();
        for ($i = 0; $i < $numOptions; $i++) {
            $this->debugCommand('option_name');
            $optionName = $this->readString();
            $this->debugCommand('option_value');
            $options[$optionName] = $this->readString();
        }

        return $options;
    }
}