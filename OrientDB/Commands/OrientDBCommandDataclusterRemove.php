<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * dataclusterRemove() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandDataclusterRemove extends OrientDBCommandAbstract
{

    /**
     * ClusterID used in command
     * @var int
     */
    protected $clusterID;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::DATACLUSTER_REMOVE;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 1) {
            throw new OrientDBWrongParamsException('This command requires cluster ID');
        }
        if (is_int($this->attribs[0])) {
            $this->clusterID = $this->attribs[0];
        } else {
            throw new OrientDBWrongParamsException('Integer expected');
        }
        // Add clusterID
        $this->addShort($this->clusterID);
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return bool
     */
    protected function parseResponse()
    {
        $this->debugCommand('remove_result');
        $result = $this->readByte();
        if ($result == chr(1)) {
            return true;
        }
        return false;
    }
}