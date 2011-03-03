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
        $buff .= pack('N', $bytesFF);
        $buff .= str_repeat(chr(0xFF), $bytesFF);

        $this->addString($buff);

    }

    protected function parse()
    {
        $this->debugCommand('status');
        $status = $this->readByte();
        if ($status != chr(0)) {
            $records = array();
            while ($status == chr(1)) {
                $this->debugCommand('classID');
                $classID = $this->readShort();
                $this->debugCommand('record_type');
                $recordType = $this->readByte();
                $this->debugCommand('clusterID');
                $clusterID = $this->readShort();
                $this->debugCommand('record_pos');
                $recordPos = $this->readLong();
                $this->debugCommand('record_version');
                $recordVersion = $this->readInt();
                $this->debugCommand('record_content');
                $content = $this->readString();

                // Form a record
                $record = new OrientDBRecord();
                $record->classID = $classID;
                $record->type = $recordType;
                $record->clusterID = $clusterID;
                $record->recordPos = $recordPos;
                $record->version = $recordVersion;
                $record->content = $content;
                $record->parse();
                $records[] = $record;

                $this->debugCommand('status');
                $status = $this->readByte();
            }

            return $records;
        }
        return false;
    }
}