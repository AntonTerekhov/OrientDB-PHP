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
 * @property int recordPos Record position in cluster
 * @property string className Class name of record
 * @property-read string recordID Fully-qualified recordID
 *
 */
class OrientDBRecord
{

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
    public $content;

    /**
     * A placeholder for document data
     * @var OrientDBData
     */
    public $data;

    /**
     * Construct new instance
     */
    public function __construct()
    {
        $this->data = new OrientDBData();
    }

    /**
     * Parses $this->content and populates $this->data
     * @return void
     */
    public function parse()
    {
        // Parse record content
        if ($this->type == OrientDB::RECORD_TYPE_DOCUMENT) {
            $parser = new OrientDBRecordDecoder(rtrim($this->content));

            $this->className = $parser->className;
            $this->data = $parser->data;
        } else {
            $this->data = $this->content;
        }
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
        if ((int) $this->clusterID !== $this->clusterID || (int) $this->recordPos !== $this->recordPos) {
            return;
        }
        if ($this->clusterID >= 0 && $this->recordPos >= 0) {
            $this->recordID = $this->clusterID . ':' . $this->recordPos;
        }
    }

    public function __get($name)
    {
        if ($name === 'recordPos' || $name === 'clusterID' || $name === 'recordID' || $name === 'className') {
            return $this->$name;
        }
        $trace = debug_backtrace();
        trigger_error('Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
        return null;
    }

    public function __set($name, $value)
    {
        if ($name === 'recordPos' || $name === 'clusterID') {
            $this->$name = $value;
            $this->parseRecordID();
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
 * Class representing OrientDB Record Data
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

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Count elements of an object. The return value is cast to an integer.
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return scalar scalar on success, integer
     * 0 on failure.
     */
    public function key()
    {
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
        return key($this->data) !== null;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->data);
    }
}