<?php

class OrientDBCommandOpenDB extends OrientDBCommandAbstract
{
	/**
     * SessionId of current connection. Not used for now
     * @var unknown_type
     */
    public $sessionId;

	public function __construct($socket, $protocolVersion)
	{
		parent::__construct($socket, $protocolVersion);
		$this->type = OrientDBCommandAbstract::DB_OPEN;
	}

	public function prepare()
	{
		parent::prepare();
        $this->addString($this->attribs[0]);
        $this->addString($this->attribs[1]);
        $this->addString($this->attribs[2]);
	}

	protected function parse()
	{
        $this->sessionId = $this->readInt();

        $numClusters = $this->readInt();

        $clusters = array();
        for ($i = 0; $i < $numClusters; $i++)
        {
            $cluster = $clusters[] = new stdClass();
            $cluster->name = $this->readString();
            $cluster->id = $this->readInt();
            $cluster->type = $this->readString();
        }
        $config = $this->readBytes();

        return array('clusters' => $clusters, 'config' => $config);
	}

}