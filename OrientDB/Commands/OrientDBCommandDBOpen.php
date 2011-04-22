<?php

class OrientDBCommandDBOpen extends OrientDBCommandAbstract
{

    /**
     * SessionID of current connection. Not used for now
     * @var unknown_type
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
        // Add DB name
        $this->addString($this->attribs[0]);
        // Add login
        $this->addString($this->attribs[1]);
        // Add password
        $this->addString($this->attribs[2]);
    }

    protected function parse()
    {
        $this->debugCommand('sessionID');
        $this->sessionID = $this->readInt();

        $this->debugCommand('clusters');
        $numClusters = $this->readInt();

        $clusters = array();
        for ($i = 0; $i < $numClusters; $i++) {
            $cluster = $clusters[] = new stdClass();
            $this->debugCommand('cluster_name');
            $cluster->name = $this->readString();
            $this->debugCommand('clusterID');
            $cluster->id = $this->readInt();
            $this->debugCommand('cluster_type');
            $cluster->type = $this->readString();
        }
        $this->debugCommand('config_bytes');
        $config = $this->readBytes();

        return array(
                        'clusters' => $clusters,
                        'config' => $config);
    }
}