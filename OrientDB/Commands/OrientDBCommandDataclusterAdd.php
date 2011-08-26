<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * dataclusterAdd() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandDataclusterAdd extends OrientDBCommandAbstract
{

    /**
     * Name of new cluster
     * @var string
     */
    protected $clusterName;

    /**
     * Cluster type
     * @var string
     * @see OrientDB::$recordTypes
     */
    protected $clusterType;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::DATACLUSTER_ADD;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 2) {
            throw new OrientDBWrongParamsException('This command requires cluster name and cluster type');
        }
        $this->clusterName = $this->attribs[0];
        if (in_array($this->attribs[1], OrientDB::$clusterTypes)) {
            $this->clusterType = $this->attribs[1];
        } else {
            throw new OrientDBWrongParamsException('Incorrect cluster Type: ' . $this->attribs[1] . '. Awaliable types is: ' . implode(', ', OrientDB::$clusterTypes));
        }
        // Add clusterType
        $this->addString($this->clusterType);
        // Add clusterName
        $this->addString($this->clusterName);
        // @TODO find out why we need to send name one more time and why this strange int. Captured by Wireshark
        switch ($this->clusterType) {
            case OrientDB::DATACLUSTER_TYPE_LOGICAL:
                $this->addInt(0);
            break;
            case OrientDB::DATACLUSTER_TYPE_PHYSICAL:
                $this->addString($this->clusterName);
                $this->addInt(0); // @FIXME On wireshark FF FF FF FF captured here, is it a -0 ?
            break;
        }
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return int
     */
    protected function parseResponse()
    {
        $this->debugCommand('clusterID');
        $clusterID = $this->readShort();
        return $clusterID;
    }
}