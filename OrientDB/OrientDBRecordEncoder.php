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
     * @param OrientDBData $data Data from OrientDBRecord instance
     * @param string $className ClassName from OrientDBRecord instance
     * @return \OrientDBRecordEncoder
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
     * @param array|OrientDBData $data Data to be encoded
     * @param bool $isAssoc Is keys needs to be included
     * @param bool $isArray Is this array or class
     * @throws OrientDBException
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
            /**
             *
             * PHP manual says what gettype() is slow and we should use is_* functions instead. But tests says that its approx. 5-10% faster encoding using single gettype() call instead of separate calls to is_int(), is_string(), etc.
             * @var string
             */
            $valueType = gettype($value);
            switch ($valueType) {
                case 'integer':
                    $buffer .= $value;
                break;

                case 'double':
                    $buffer .= $value . chr(OrientDBRecordDecoder::CCODE_NUM_FLOAT);
                break;

                case 'string':
                    $buffer .= self::encodeString($value);
                break;

                case 'boolean':
                    if ($value === true) {
                        $buffer .= 'true';
                    } else {
                        $buffer .= 'false';
                    }
                break;

                case 'array':
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
                     * @TODO Fix possible PHP fatal: Maximum function nesting level of '100' reached, aborting!
                     */
                    $buffer .= implode(',', $this->process($value, $arrayAssoc, true));
                    $buffer .= $boundEnd;
                break;

                case 'NULL':

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

                default:
                    throw new OrientDBException('Can\'t serialize: ' . $valueType);
            }
            $tokens[] = $buffer;
        }
        return $tokens;
    }

    /**
     * Returns escaped string, suitable for OrientDBRecord format
     * @param string $string
     * @return string
     */
    public static function encodeString($string)
    {
        /**
         * @TODO Unit tests
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
        if (!is_array($array)) {
            return;
        }
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }
}