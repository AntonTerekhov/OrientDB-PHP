<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * recordUpdate() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandRecordUpdate extends OrientDBCommandAbstract
{

    /**
     * ClusterID
     * @var int
     */
    protected $clusterID;

    /**
     * Record position
     * @var int
     */
    protected $recordPos;

    /**
     * Record type
     * @var string
     * @see OrientDB::$recordTypes
     */
    protected $recordType;

    /**
     * record version
     * @var int
     */
    protected $version;

    /**
     * Record content param
     * @var string|OrientDBRecord
     */
    protected $recordContent;

    protected $mode = 0x00;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::RECORD_UPDATE;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) > 4 || count($this->attribs) < 2) {
            throw new OrientDBWrongParamsException('This command requires record ID, record content and, optionally, record version and, optionally, record type');
        }
        $arr = explode(':', $this->attribs[0]);
        if (count($arr) != 2) {
            throw new OrientDBWrongParamsException('Wrong format for record ID');
        }
        $this->clusterID = (int) $arr[0];
        $this->recordPos = (int) $arr[1];

        if ($this->clusterID === 0 || (string) $this->recordPos !== $arr[1]) {
            throw new OrientDBWrongParamsException('Wrong format for record ID');
        }
        // Add ClusterID
        $this->addShort($this->clusterID);
        // Add Record pos
        $this->addLong($this->recordPos);
        // Prepare record content
        $this->recordContent = $this->attribs[1];
        // Add record content
        $this->addBytes($this->recordContent);
        // Add version
        if (count($this->attribs) >= 3) {
            $this->version = (int) $this->attribs[2];
        } else {
            // Pessimistic way
            $this->version = -1;
        }
        $this->addInt($this->version);
        if (count($this->attribs) == 4) {
            if (in_array($this->attribs[3], OrientDB::$recordTypes)) {
                $this->recordType = $this->attribs[3];
            } else {
                throw new OrientDBWrongParamsException('Incorrect record Type: ' . $this->attribs[2] . '. Available types is: ' . implode(', ', OrientDB::$recordTypes));
            }
        } else {
            $this->recordType = OrientDB::RECORD_TYPE_DOCUMENT;
        }
        // Add recordType
        $this->addByte($this->recordType);
        $this->addByte(chr($this->mode));
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return int
     */
    protected function parseResponse()
    {
        $this->debugCommand('record_version');
        $version = $this->readInt();

        if ($this->recordContent instanceof OrientDBRecord) {
            $this->recordContent->recordPos = $this->recordPos;
            $this->recordContent->clusterID = $this->clusterID;
            $this->recordContent->version = $version;
        }
        return $version;
    }
}