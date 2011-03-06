<?php

class OrientDBCommandCommand extends OrientDBCommandAbstract
{

    protected $query;

    protected $mode;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->type = OrientDBCommandAbstract::COMMAND;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) > 2 || count($this->attribs) < 1) {
            throw new OrientDBWrongParamsException('This command requires query and, optionally, mode');
        }
        $this->query = $this->attribs[0];
        $this->mode = OrientDB::COMMAND_MODE_ASYNC;
        if (count($this->attribs) == 2) {
            if ($this->attribs[1] == OrientDB::COMMAND_MODE_SYNC || $this->attribs[1] == OrientDB::COMMAND_MODE_ASYNC) {
                $this->mode = $this->attribs[1];
                if ($this->mode == OrientDB::COMMAND_MODE_SYNC) {
                    throw new OrientDBWrongParamsException('Not implemented');
                    // @TODO fix modes
                }
            } else {
                throw new OrientDBWrongParamsException('Wrong command mode');
            }
        }

        // Add mode
        $this->addByte($this->mode);
        // @TODO check it, this format was captured by Wireshark
        // trunk/client/src/main/java/com/orientechnologies/orient/client/remote/OStorageRemote.java:552
        // trunk/core/src/main/java/com/orientechnologies/orient/core/command/OCommandRequestTextAbstract.java:97
        $objName = 'com.orientechnologies.orient.core.sql.query.OSQLAsynchQuery';
        $bytesFF = 20;

        $buff = pack('N', strlen($objName));
        $buff .= $objName;
        $buff .= pack('N', strlen($this->query));
        $buff .= $this->query;
        // Params
        $buff .= pack('N', $bytesFF);
        $buff .= str_repeat(chr(0xFF), $bytesFF);
        // 0.9.2.5 - added a fetchplan
        $fetchPlan = '*:1';
        $buff .= pack('N', strlen($fetchPlan));
        $buff .= $fetchPlan;
        // @TODO wtf?
        $buff .= pack('N', 0);

        $this->addString($buff);

    }

    protected function parse()
    {
        $this->debugCommand('status');
        $status = $this->readByte();
        if ($status != chr(0)) {
            $records = array();
            while ($status == chr(1)) {

                $records[] = $this->readRecord();

                $this->debugCommand('status');
                $status = $this->readByte();
            }
            // Cache records
            $cachedRecords = array();
            while ($status == chr(2)) {
                $cachedRecords = $this->readRecord();

                $this->debugCommand('status');
                $status = $this->readByte();
            }

            return $records;
        }
        return false;
    }
}