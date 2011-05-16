<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * Class representing OrientDB Record
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Datatypes
 *
 */
class OrientDBRecord
{

    /**
     * ClassID in OrientDB
     * @var int
     */
    public $classID;

    /**
     * ClassName as parsed from document
     * @var string
     */
    public $className;

    /**
     * Document type
     * @example OrientDB::RECORD_TYPE_DOCUMENT
     * @see OrientDB::$recordTypes
     * @var string
     */
    public $type;

    /**
     * ClusterID
     * @var int
     */
    public $clusterID;

    /**
     * Record position in cluster
     * @var int
     */
    public $recordPos;

    /**
     * Full qualified record ID
     * @example 1:1
     * @var string
     */
    public $recordID;

    /**
     * Document version
     * @var int
     */
    public $version;

    /**
     * Document source as delivered from OrientDB
     * @var string
     */
    public $content;

    /**
     * A placeholder for document data
     * @var StdClass
     */
    public $data;

    /**
     * Construct new instance
     */
    public function __construct()
    {
        $this->data = new stdClass();
    }

    /**
     * Parses $this->content and populates $this->data
     * @return void
     */
    public function parse()
    {
        // Form recordID
        $this->parseRecordID();
        // Parse record content
        $parser = new OrientDBRecordDecoder(rtrim($this->content));

        $this->className = $parser->className;
        $this->data = $parser->data;
    }

    /**
     * Magic method
     * @return string
     */
    public function __toString()
    {
        $encoder = new OrientDBRecordEncoder($this->data, $this->className);
        return $encoder->buffer;
    }

    /**
     * Parses recordID from $this->clusterID and $this->recordPos. Populated $this->recordID
     * @return void
     */
    private function parseRecordID()
    {
        if ((int) $this->clusterID !== $this->clusterID || (int) $this->recordPos !== $this->recordPos) {
            return;
        }
        if ($this->clusterID > 0 && $this->recordPos >= 0) {
            $this->recordID = $this->clusterID . ':' . $this->recordPos;
        }
    }
}