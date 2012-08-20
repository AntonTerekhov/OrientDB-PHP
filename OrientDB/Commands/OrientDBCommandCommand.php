<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * command() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandCommand extends OrientDBCommandAbstract
{

    /**
     * Query text
     * @var string
     */
    protected $query;

    /**
     * Command mode
     * @var string
     */
    protected $mode;

    /**
     * Fetchplan
     * @var string
     */
    protected $fetchPlan;

    const MODE_SYNC = 's';

    const MODE_ASYNC = 'a';

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::COMMAND;
    }

    public function prepare()
    {
        parent::prepare();
        if (!empty($this->mode)) {
            // Populate $this->attribs with mode from child classes
            array_unshift($this->attribs, $this->mode);
        }
        if (count($this->attribs) > 3 || count($this->attribs) < 2) {
            throw new OrientDBWrongParamsException('This command requires query and, optionally, fetchplan');
        }
        if (empty($this->mode)) {
            if ($this->attribs[0] == OrientDB::COMMAND_SELECT_SYNC || $this->attribs[0] == OrientDB::COMMAND_SELECT_ASYNC || $this->attribs[0] == OrientDB::COMMAND_QUERY) {
                $this->mode = $this->attribs[0];
            } else {
                throw new OrientDBWrongParamsException('Wrong command mode');
            }
        }
        if (($this->mode == OrientDB::COMMAND_QUERY || $this->mode == OrientDB::COMMAND_SELECT_SYNC) && count($this->attribs) == 3) {
            throw new OrientDBWrongParamsException('Fetch is useless with COMMAND_QUERY');
        }
        $this->query = trim($this->attribs[1]);
        $this->fetchPlan = '*:0';
        if (count($this->attribs) == 3) {
            $this->fetchPlan = $this->attribs[2];
        }

        // Add mode
        if ($this->mode == OrientDB::COMMAND_QUERY || $this->mode == OrientDB::COMMAND_SELECT_SYNC) {
            $this->addByte(self::MODE_SYNC);
        } else {
            $this->addByte(self::MODE_ASYNC);
        }
        if ($this->mode == OrientDB::COMMAND_SELECT_ASYNC) {
            $objName = 'com.orientechnologies.orient.core.sql.query.OSQLAsynchQuery';
        } elseif ($this->mode == OrientDB::COMMAND_SELECT_SYNC) {
            $objName = 'com.orientechnologies.orient.core.sql.query.OSQLSynchQuery';
        } else {
            $objName = 'com.orientechnologies.orient.core.sql.OCommandSQL';
        }
        // Java query object serialization
        $buff = '';
        // Java query object name serialization
        $buff .= pack('N', strlen($objName));
        $buff .= $objName;
        // Query text serialization in TEXT mode
        $buff .= pack('N', strlen($this->query));
        $buff .= $this->query;
        if ($this->mode == OrientDB::COMMAND_SELECT_ASYNC || $this->mode == OrientDB::COMMAND_SELECT_SYNC) {
            // Limit set to -1 to ignore and use TEXT MODE
            $buff .= pack('N', -1);
            // Add a fetchplan
            $buff .= pack('N', strlen($this->fetchPlan));
            $buff .= $this->fetchPlan;
        }
        // Params serialization, we have 0 params
        $buff .= pack('N', 0);
        // Now query object serialization complete, add it to command bytes
        $this->addString($buff);
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return bool|OrientDBTypeLink|OrientDBRecord|string|array
     */
    protected function parseResponse()
    {
        $this->debugCommand('status');
        $status = $this->readByte();
        if ($this->mode == OrientDB::COMMAND_SELECT_ASYNC) {
            if ($status != chr(0)) {
                $records = array();
                while ($status == chr(1)) {

                    $record = $this->readRecord();
                    $records[] = $record;

                    $this->debugCommand('record_status_next');
                    $status = $this->readByte();
                }
                // Cache records
                $cachedRecords = array();
                while ($status == chr(2)) {
                    $this->debugCommand('record_content');
                    $record = $this->readRecord();
                    $cachedRecords[$record->recordID] = $record;
                    $this->debugCommand('record_status_cache');
                    $status = $this->readByte();
                }
                // Invalidate cache
                $this->parent->cachedRecords = $cachedRecords;
                return $records;
            }
            return false;
        } else {
            if ($status == 'l') {
                // List of records
                $this->debugCommand('records_count');
                $recordsCount = $this->readInt();
                if ($recordsCount == 0) {
                    return false;
                }
                $records = array();
                for ($i = 0; $i < $recordsCount; $i++) {
                    $records[] = $this->readRecord();
                }
                return $records;
            } else if ($status == 'n') {
                // Null
                return null;
            } else if ($status == 'r') {
                // Single record
                return $this->readRecord();
            } else if ($status == 'a') {
                // Something other
                return $this->readString();
            }
        }
        return null;
    }
}