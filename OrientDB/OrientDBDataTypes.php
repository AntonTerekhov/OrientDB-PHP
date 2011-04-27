<?php

/**
 *
 * Class represent OrientDB link to other document
 *
 */
class OrientDBTypeLink
{

    private $link;

    public $clusterID;

    public $recordPos;

    /**
     *
     * Create object from string. Using a kind of overloading
     * @example #10:1
     * @example 10:1
     * @param string|int $link
     * @param int $recordPos
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
            // Construct from two ints
            $this->clusterID = (int) $link;
            $this->recordPos = (int) $recordPos;
            if (((string) $this->clusterID !== (string) $link) || ((string) $this->recordPos !== (string) $recordPos)) {
                $this->clusterID = null;
                $this->recordPos = null;
            }
            if (!is_null($this->clusterID) && !is_null($this->recordPos)) {
                $this->link = $this->clusterID . ':' . $this->recordPos;
            }
        }
    }

    public function __toString()
    {
        if ($this->link) {
            return '#' . $this->link;
        }
        return '';
    }

    /**
     *
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
 *
 * Class represent OrientDB date
 *
 */
class OrientDBTypeDate
{

    private $timestamp;

    public function __construct($time)
    {
        if (substr($time, -1, 1) === 't') {
            $time = substr($time, 0, -1);
        }
        if ((string) (int) $time === (string) $time) {
            $this->timestamp = (int) $time;
        }
    }

    public function __toString()
    {
        if ($this->timestamp) {
            return $this->timestamp . 't';
        }
        return '';
    }

    public function getTime()
    {
        return $this->timestamp;
    }

    public function getValue()
    {
        if ($this->timestamp) {
            return $this->timestamp . 't';
        }
    }
}