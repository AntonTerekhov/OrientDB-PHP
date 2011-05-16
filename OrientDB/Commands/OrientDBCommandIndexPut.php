<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * indexPut() command for OrientDB-PHP
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Command
 */
class OrientDBCommandIndexPut extends OrientDBCommandAbstract
{

    /**
     * Key in index
     * @var string
     */
    protected $key;

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

    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->opType = OrientDBCommandAbstract::INDEX_PUT;
    }

    public function prepare()
    {
        parent::prepare();
        if (count($this->attribs) > 3 || count($this->attribs) < 2) {
            throw new OrientDBWrongParamsException('This command requires key name, record ID and, optionally, record Type');
        }
        $this->key = $this->attribs[0];
        // Process recordID
        $arr = explode(':', $this->attribs[1]);
        if (count($arr) != 2) {
            throw new OrientDBWrongParamsException('Wrong format for record ID');
        }
        $this->clusterID = (int) $arr[0];
        $this->recordPos = (int) $arr[1];
        if ($this->clusterID === 0 || (string) $this->recordPos !== $arr[1]) {
            throw new OrientDBWrongParamsException('Wrong format for record ID');
        }
        // Process recordType
        $this->recordType = OrientDB::RECORD_TYPE_DOCUMENT;
        if (count($this->attribs) == 3) {
            if (in_array($this->attribs[2], OrientDB::$recordTypes)) {
                $this->recordType = $this->attribs[2];
            } else {
                throw new OrientDBWrongParamsException('Incorrect record Type: ' . $this->attribs[2] . '. Awaliable types is: ' . implode(', ', OrientDB::$recordTypes));
            }
        }

        // Add key
        $this->addString($this->key);
        // Add record-type
        $this->addByte($this->recordType);
        // Add clustedID
        $this->addShort($this->clusterID);
        // Add RecordPos
        $this->addLong($this->recordPos);
    }

    /**
     * (non-PHPdoc)
     * @see OrientDBCommandAbstract::parse()
     * @return bool|OrientDBTypeLink|OrientDBRecord
     */
    protected function parse()
    {
        $record = $this->readRecord();
        return $record;
    }
}