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
 * @property int clusterID ClusterID of record
 * @property int|string recordPos Record position in cluster
 * @property string className Class name of record
 * @property string content Document source as delivered from OrientDB
 * @property-read string recordID Fully-qualified recordID
 *
 */
class OrientDBRecord
{
    /**
     * Flag, if current $this->content was already decoded to $this->data
     * @var bool
     */
    private $isParsed = false;

    /**
     * ClassName as parsed from document
     * @var string
     */
    private $className;

    /**
     * Document type
     * @example OrientDB::RECORD_TYPE_DOCUMENT
     * @see OrientDB::$recordTypes
     * @var string
     */
    public $type = OrientDB::RECORD_TYPE_DOCUMENT;

    /**
     * ClusterID
     * @var int
     */
    private $clusterID;

    /**
     * Record position in cluster
     * @var int
     */
    private $recordPos;

    /**
     * Full qualified record ID
     * @example 1:1
     * @var string
     */
    private $recordID;

    /**
     * Document version
     * @var int
     */
    public $version;

    /**
     * Document source as delivered from OrientDB
     * @var string
     */
    private $content;

    /**
     * A placeholder for document data
     * @var OrientDBData|string
     */
    public $data;

    /**
     * Construct new instance
     */
    public function __construct()
    {
        $this->data = new OrientDBData($this);
        $this->isParsed = true;
    }

    /**
     * Parses $this->content and populates $this->data
     * @return void
     */
    public function parse()
    {
        // Check, if we already decoded current $this->content
        if (!$this->isParsed) {
            // Parse record content
            if ($this->type == OrientDB::RECORD_TYPE_DOCUMENT) {
                $parser = new OrientDBRecordDecoder(rtrim($this->content));

                $this->className = $parser->className;
                foreach ($parser->data as $key => $value) {
                    $this->data->$key = $value;
                }
            } else {
                $this->data = $this->content;
            }
            $this->isParsed = true;
        }
    }

    /**
     * Forces that record was already parsed
     * @return void
     */
    public function setParsed()
    {
        $this->isParsed = true;
    }

    /**
     * Fully resets class, equals to new()
     * @return void
     */
    public function reset()
    {
        $this->data = new OrientDBData($this);
        $this->className = null;
        $this->content = null;
        $this->isParsed = true;
        $this->clusterID = null;
        $this->recordPos = null;
        $this->recordID = null;
        $this->version = null;
    }

    /**
     * Resets Record data, keeping className and clusterID intact
     * @return void
     */
    public function resetData()
    {
        $this->data = new OrientDBData($this);
        $this->content = null;
        $this->isParsed = true;
        $this->recordPos = null;
        $this->recordID = null;
        $this->version = null;
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
     * Parses recordID from $this->clusterID and $this->recordPos. Populates $this->recordID
     * @return void
     */
    private function parseRecordID()
    {
        if ((int) $this->clusterID !== $this->clusterID) {
            return;
        }
        if ($this->clusterID >= 0 && is_numeric($this->recordPos)) {
            if ($this->recordPos >= 0) {
                $this->recordID = $this->clusterID . ':' . $this->recordPos;
            }
        }
    }

    public function __get($name)
    {
        if ($name === 'className') {
            $this->parse();
        }
        if ($name === 'recordPos' || $name === 'clusterID' || $name === 'recordID' || $name === 'className' || $name == 'content') {
            return $this->$name;
        }
        $trace = debug_backtrace();
        trigger_error('Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
        return null;
    }

    public function __set($name, $value)
    {
        if ($name === 'recordPos' || $name === 'clusterID') {
            if (is_numeric($value) || is_null($value)) {
                $this->$name = $value;
            }
            $this->parseRecordID();
        } elseif ($name === 'content') {
            $this->content = $value;
            $this->isParsed = false;
            $this->data = new OrientDBData($this);
        } elseif ($name === 'className') {
            $this->className = $value;
        } elseif ($name === 'recordID') {
            $trace = debug_backtrace();
            trigger_error('Can\'t directly set property ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
        } else {
            $trace = debug_backtrace();
            trigger_error('Can\'t set property ' . $name . ' via __set() in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
        }
    }
}

/**
 * Class representing OrientDB Record Data. Mainly used for parsing record "on demand" while any function for getting data is called
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Datatypes
 *
 */
class OrientDBData implements Countable, Iterator
{
    /**
     * Data holder
     * @var array
     */
    private $data = array();

    /**
     * Link to parent record, for calling it's ->parse()
     * @var OrientDBRecord|null
     */
    private $record;


    /**
     * Link to parent record
     * @param $record OrientDBRecord|null
     * @throws OrientDBException
     */
    public function __construct($record = null)
    {
        if (!is_null($record)) {
            if ($record instanceof OrientDBRecord) {
                $this->record = $record;
            } else {
                throw new OrientDBException('Only OrientDBRecord instance can be used in __construct()');
            }
        }
    }

    public function &__get($name)
    {
        $this->isParsed();
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        trigger_error('Undefined index: ' . $name, E_USER_NOTICE);
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }


    /**
     * Magic function for isset() call
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        $this->isParsed();
        return isset($this->data[$name]);
    }

    /**
     * Magic function for unset() calls
     * @param $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * Count elements of an object. The return value is cast to an integer.
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     */
    public function count()
    {
        $this->isParsed();
        return count($this->data);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $this->isParsed();
        return current($this->data);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->isParsed();
        next($this->data);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return integer scalar on success
     * 0 on failure.
     */
    public function key()
    {
        $this->isParsed();
        return key($this->data);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        $this->isParsed();
        return key($this->data) !== null;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->isParsed();
        reset($this->data);
    }

    /**
     * Check, if current dataset was already de-serialized
     * @return void
     */
    private function isParsed()
    {
        if (!is_null($this->record)) {
            // If we have link to parent record
            $this->record->parse();
        }
    }

    /**
     * Get all keys for data
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->data);
    }
}