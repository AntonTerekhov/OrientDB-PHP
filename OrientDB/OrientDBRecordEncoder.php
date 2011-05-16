<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * Class to encode OrientDB record
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Datatypes
 */
class OrientDBRecordEncoder
{

    /**
     * ClassName as parsed from document
     * @var string
     */
    protected $className;

    /**
     * A placeholder for document data
     * @var StdClass
     */
    protected $data;

    /**
     * Serialized record
     * @var string
     */
    public $buffer;

    /**
     * Construct new instance
     * @param StdClass $data Data from OrientDBRecord instance
     * @param string $className ClassName from OrientDBRecord instance
     * @return void
     */
    public function __construct($data, $className = null)
    {
        $this->data = $data;
        $this->className = $className;
        $this->encode();
    }

    /**
     * Parses $this->content and populates $this->data and $this->className
     * @return void
     */
    protected function encode()
    {
        if (!is_null($this->className)) {
            $this->buffer .= $this->className . '@';
        }
        $tokens = $this->process($this->data);

        $this->buffer .= implode(',', $tokens);
    }

    /**
     * Recursively encodes data to OrientDB representation
     * @param array|StdClass $data Data to be encoded
     * @param bool $isAssoc Is keys needs to be included
     * @param bool $isArray Is this array or class
     * @return array Array of tokens
     */
    protected function process($data, $isAssoc = true, $isArray = false)
    {
        $tokens = array();

        foreach ($data as $key => $value) {
            $buffer = '';
            if ($isAssoc) {
                if ($isArray) {
                    $buffer = self::encodeString($key) . ':';
                } else {
                    $buffer = $key . ':';
                }
            }
            switch (1) {
                case is_int($value):
                    $buffer .= $value;
                break;

                case is_float($value):
                    $buffer .= $value . chr(OrientDBRecordDecoder::CCODE_NUM_FLOAT);
                break;

                case is_string($value):
                    $buffer .= self::encodeString($value);
                break;

                case is_bool($value):
                    if ($value === true) {
                        $buffer .= 'true';
                    } else {
                        $buffer .= 'false';
                    }
                break;

                case is_array($value):
                    $arrayAssoc = self::isAssoc($value);
                    if ($arrayAssoc === true) {
                        $boundStart = chr(OrientDBRecordDecoder::CCODE_OPEN_CURLY);
                        $boundEnd = chr(OrientDBRecordDecoder::CCODE_CLOSE_CURLY);
                    } elseif ($arrayAssoc === false) {
                        $boundStart = chr(OrientDBRecordDecoder::CCODE_OPEN_SQUARE);
                        $boundEnd = chr(OrientDBRecordDecoder::CCODE_CLOSE_SQUARE);
                    }
                    $buffer .= $boundStart;
                    /**
                     * @TODO Fix possible PHP's fatal: Maximum function nesting level of '100' reached, aborting!
                     */
                    $buffer .= implode(',', $this->process($value, $arrayAssoc, true));
                    $buffer .= $boundEnd;
                break;

                case is_a($value, 'OrientDBTypeLink'):
                    $buffer .= $value->getHash();
                break;

                case is_a($value, 'OrientDBTypeDate'):
                    $buffer .= (string) $value;
                break;

                case is_a($value, 'OrientDBRecord'):
                    $buffer .= chr(OrientDBRecordDecoder::CCODE_OPEN_PARENTHESES);
                    $buffer .= $value->__toString();
                    $buffer .= chr(OrientDBRecordDecoder::CCODE_CLOSE_PARENTHESES);
                break;
            }
            $tokens[] = $buffer;
        }
        return $tokens;
    }

    /**
     * Returns escaped string, suitable for OrientDB
     * @param string $string
     * @return string
     */
    protected static function encodeString($string)
    {
        /**
         * @TODO Unittests
         */
        return '"' . addcslashes($string, '"\\') . '"';
    }

    /**
     * Check is array is associative or sequential
     * @param array $array
     * @return bool
     */
    protected static function isAssoc($array)
    {
        /**
         * @TODO Unittests
         */
        if (!is_array($array)) {
            return;
        }
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }
}