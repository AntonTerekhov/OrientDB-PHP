<?php

class OrientDBCommandConnect extends OrientDBCommandAbstract
{
	/**
     * SessionId of current connection. Not used for now
     * @var unknown_type
     */
    public $sessionId;

	public function __construct($socket, $protocolVersion)
	{
		parent::__construct($socket, $protocolVersion);
		$this->type = OrientDBCommandAbstract::CONNECT;
	}

	public function prepare()
	{
		parent::prepare();
        $this->addString($this->attribs[0]);
        $this->addString($this->attribs[1]);
	}

	protected function parse()
	{
        $this->sessionId = $this->readInt();

        return true;
	}

}