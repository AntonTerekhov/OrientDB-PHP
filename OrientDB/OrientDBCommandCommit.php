<?php

class OrientDBCommandCommit extends OrientDBCommandAbstract
{

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::TX_COMMIT;
    }

    public function prepare()
    {
        parent::prepare();
        throw new OrientDBException('Not implemented');
    }

    protected function parse()
    {
        $count = $this->readInt();
        $records = array();
        for ($i = 0; $i < $count; $i++) {
            $clusterID = $this->readShort();
            $recordPos = $this->readLong();
            $recordVersion = $this->readInt();

            $record = new StdClass();
            $record->clusterID = $clusterID;
            $record->recordPos = $recordPos;
            $record->version = $recordVersion;
            $record->recordID = $clusterID . ':' . $recordPos;

            $records[] = $record;
        }
        return $records;
    }
}