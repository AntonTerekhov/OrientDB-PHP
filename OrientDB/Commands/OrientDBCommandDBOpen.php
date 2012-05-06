<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * DBOpen() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandDBOpen extends OrientDBCommandAbstract
{

    /**
     * SessionID of current connection
     * @var int
     */
    public $sessionID;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::DB_OPEN;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) != 3) {
            throw new OrientDBWrongParamsException('This command requires DB name, login and password');
        }
        // Add Driver name
        $this->addString(OrientDB::DRIVER_NAME);
        // Add Driver version
        $this->addString(OrientDB::DRIVER_VERSION);
        // Add protocol version
        $this->addShort($this->parent->getProtocolVersionClient());
        // Add client ID
        $this->addString('');
        // Add DB name
        $this->addString($this->attribs[0]);
        // Add DB type. Since rc9. Right now only document is supported
        $this->addString('document');
        // Add login
        $this->addString($this->attribs[1]);
        // Add password
        $this->addString($this->attribs[2]);
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return array
     */
    protected function parseResponse()
    {
        $this->debugCommand('sessionID');
        $this->sessionID = $this->readInt();

        $this->debugCommand('clusters');
        $numClusters = $this->readShort();

        $clusters = array();
        for ($i = 0; $i < $numClusters; $i++) {
            $cluster = new stdClass();
            $this->debugCommand('cluster_name');
            $cluster->name = $this->readString();
            $this->debugCommand('clusterID');
            $cluster->id = $this->readShort();
            $this->debugCommand('cluster_type');
            $cluster->type = $this->readString();
            $this->debugCommand('cluster_datasegment_id');
            $cluster->datasegmentid = $this->readShort();
            $clusters[] = $cluster;
        }
        $this->debugCommand('config_bytes');
        $config = $this->readBytes();

        return array(
            'clusters' => $clusters,
            'config' => $config);
    }
}