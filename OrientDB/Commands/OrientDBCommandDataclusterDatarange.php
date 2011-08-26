<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * dataclusterDatarange() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandDataclusterDatarange extends OrientDBCommandAbstract
{

    /**
     * ClusterID used in command
     * @var int
     */
    protected $clusterID;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::DATACLUSTER_DATARANGE;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 1) {
            throw new OrientDBWrongParamsException('This command requires cluster ID');
        }
        $this->clusterID = $this->attribs[0];
        if (!is_int($this->clusterID)) {
            throw new OrientDBWrongParamsException('Integer expected');
        }
        // Add clusterID
        $this->addShort($this->clusterID);
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return array
     */
    protected function parseResponse()
    {
        $this->debugCommand('start_pos');
        $startPos = $this->readLong();
        $this->debugCommand('end_pos');
        $endPos = $this->readLong();
        return array(
            'start' => $startPos,
            'end' => $endPos);
    }
}