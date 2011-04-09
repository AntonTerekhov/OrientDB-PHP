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
        $this->type = OrientDBCommandAbstract::DB_OPEN;
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
        $this->sessionID = $this->readInt();

        $numClusters = $this->readInt();

        $clusters = array();
        for ($i = 0; $i < $numClusters; $i++) {
            $cluster = $clusters[] = new stdClass();
            $cluster->name = $this->readString();
            $cluster->id = $this->readInt();
            $cluster->type = $this->readString();
        }
        $config = $this->readBytes();

        return array(
                        'clusters' => $clusters,
                        'config' => $config);
    }
}