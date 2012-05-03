<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * Class represent OrientDB link to another document
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Datatypes
 */
class OrientDBTypeLink
{

    /**
     * Link text without hash sign
     * @var string
     */
    private $link;

    /**
     * Cluster ID in link
     * @var int
     */
    public $clusterID;

    /**
     * Record Position in link
     * @var int
     */
    public $recordPos;

    /**
     *
     * Create object from string or two integers. Using a kind of overloading
     * @example #10:1
     * @example 10:1
     * @example 10, 1
     * @param string|int $link
     * @param null|int|string $recordPos
     */
    public function __construct($link, $recordPos = null)
    {
        if (is_null($recordPos)) {
            // Construct from one string #10:1
            if (substr($link, 0, 1) === '#') {
                $link = substr($link, 1);
            }
            if (preg_match('/^([0-9]+):([0-9]+)$/', $link, $matches)) {
                $this->link = $link;
                $this->clusterID = (int) $matches[1];
                $this->recordPos = (int) $matches[2];
            }
        } else {
            // Construct from two integers
            $this->clusterID = (int) $link;
            $this->recordPos = $recordPos;
            if (((string) $this->clusterID !== (string) $link) || (!is_numeric($this->recordPos))) {
                $this->clusterID = null;
                $this->recordPos = null;
            }
            if (!is_null($this->clusterID) && !is_null($this->recordPos)) {
                $this->link = $this->clusterID . ':' . $this->recordPos;
            }
        }
    }

    /**
     * Magic method overloading
     * @return string
     */
    public function __toString()
    {
        if ($this->link) {
            return '#' . $this->link;
        }
        return '';
    }

    /**
     * Return link without hash sign
     * @return string
     * @example 10:1
     */
    public function get()
    {
        return $this->link;
    }

    /**
     *
     * Return link with hash sign
     * @return string
     * @example #10:1
     */
    public function getHash()
    {
        return '#' . $this->link;
    }
}

/**
 * Class represent OrientDB date
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Datatypes
 */
class OrientDBTypeDate
{

    /**
     * Timestamp
     * @var int
     */
    private $timestamp;

    /**
     * Create object from string or int
     * @param string|int $time
     */
    public function __construct($time)
    {
        if (substr($time, -1, 1) === 't') {
            $time = substr($time, 0, -1);
        }
        if ((string) (int) $time === (string) $time) {
            $this->timestamp = (int) $time;
        }
    }

    /**
     * Magic method overloading
     * @return string
     */
    public function __toString()
    {
        if ($this->timestamp) {
            return $this->timestamp . 't';
        }
        return '';
    }

    /**
     * Return time for timestamp
     * @return int
     */
    public function getTime()
    {
        return $this->timestamp;
    }

    /**
     * Return timestamp in OrientDB format
     * @return string
     */
    public function getValue()
    {
        if ($this->timestamp) {
            return $this->timestamp . 't';
        }
    }
}