<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * recordDelete() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandRecordDelete extends OrientDBCommandAbstract
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
     * Record version
     * @var int
     */
    protected $version;

    /**
     * Mode. 0 = synchronous (default mode waits for the answer) 1 = asynchronous (don't need an answer)
     * @var int
     */
    protected $mode = 0x00;

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::RECORD_DELETE;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) > 2 || count($this->attribs) < 1) {
            throw new OrientDBWrongParamsException('This command requires record ID and, optionally, record version');
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
        if (count($this->attribs) == 2) {
            $this->version = (int) $this->attribs[1];
        } else {
            // Pessimistic way
            $this->version = -1;
        }
        // Add ClusterID
        $this->addShort($this->clusterID);
        // Add RecordID
        $this->addLong($this->recordPos);
        // Add version
        $this->addInt($this->version);
        // Synchronous mode
        $this->addByte(chr($this->mode));
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parseResponse()
     * @return bool
     */
    protected function parseResponse()
    {
        $this->debugCommand('delete_result');
        $result = $this->readByte();
        if ($result == chr(1)) {
            return true;
        } else {
            return false;
        }
    }
}