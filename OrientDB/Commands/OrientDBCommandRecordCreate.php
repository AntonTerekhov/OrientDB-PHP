<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * recordCreate() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandRecordCreate extends OrientDBCommandAbstract
{

    /**
     * ClusterID
     * @var int
     */
    protected $clusterID;

    /**
     * Record type
     * @var string
     * @see OrientDB::$recordTypes
     */
    protected $recordType;

    /**
     * Record content
     * @var string|OrientDBRecord
     */
    protected $recordContent;

    /**
     * Mode. 0 = synchronous (default mode waits for the answer) 1 = asynchronous (don't need an answer)
     * @var int
     */
    protected $mode = 0x00;

    /**
     * @var int
     */
    protected $datasegmentID = 0;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::RECORD_CREATE;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) > 3 || count($this->attribs) < 2) {
            throw new OrientDBWrongParamsException('This command requires cluster ID, record content and, optionally, record Type');
        }
        // Add datasegment ID
        $this->addInt($this->datasegmentID);
        // Process clusterID
        $this->clusterID = (int) $this->attribs[0];
        // Add ClusterID
        $this->addShort($this->clusterID);
        // Process recordContent
        $this->recordContent = $this->attribs[1];
        // Add RecordContent
        $this->addBytes($this->recordContent);
        // recordType
        $this->recordType = OrientDB::RECORD_TYPE_DOCUMENT;
        if (count($this->attribs) == 3) {
            if (in_array($this->attribs[2], OrientDB::$recordTypes)) {
                $this->recordType = $this->attribs[2];
            } else {
                throw new OrientDBWrongParamsException('Incorrect record Type: ' . $this->attribs[2] . '. Available types is: ' . implode(', ', OrientDB::$recordTypes));
            }
        }
        $this->addByte($this->recordType);
        // Synchronous mode
        $this->addByte(chr($this->mode));
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return int
     */
    protected function parseResponse()
    {
        $this->debugCommand('record_pos');
        $position = $this->readLong();
        $this->debugCommand('record_version');
        $version = $this->readInt();

        if ($this->recordContent instanceof OrientDBRecord) {
            $this->recordContent->recordPos = $position;
            $this->recordContent->clusterID = $this->clusterID;
            $this->recordContent->version = $version;
        }

        return $position;
    }
}