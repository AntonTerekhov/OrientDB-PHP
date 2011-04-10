<?php

class OrientDBRecord
{

    /**
     * ClassID
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
     * Parser state
     * @example self::STATE_GUESS
     * @var int
     */
    protected $state;

    /**
     * Parser buffer
     * @var string
     */
    protected $buffer;

    /**
     * List of possble states
     */

    /**
     * what we're going to collect?
     */
    const STATE_GUESS = 0;

    /**
     * collecting field name
     */
    const STATE_NAME = 1;

    /**
     * collectiong field value
     */
    const STATE_VALUE = 2;

    /**
     * collecting double-quoted string
     */
    const STATE_STRING = 3;

    /**
     * collecting of comma between fields
     */
    const STATE_COMMA = 4;

    /**
     * collecting of link to other record
     */
    const STATE_LINK = 5;

    /**
     * collecting of number
     */
    const STATE_NUMBER = 6;

    /**
     * collecting of map key
     */
    const STATE_KEY = 7;

    /**
     * collecting boolean
     */
    const STATE_BOOLEAN = 8;

    /**
     * character classes
     */

    /**
     * char of word class [a-z-]
     */
    const CCLASS_WORD = 1;

    /**
     * char of number class [0-9]
     */
    const CCLASS_NUMBER = 2;

    /**
     * any other chars
     */
    const CCLASS_OTHER = 0;

    /**
     * Character codes
     */

    /**
     * @
     */
    const CCODE_AT = 0x40;

    /**
     * :
     */
    const CCODE_COLON = 0x3A;

    /**
     * "
     */
    const CCODE_DOUBLE_QUOTE = 0x22;

    /**
     * \
     */
    const CCODE_ESCAPE = 0x5C;

    /**
     * ,
     */
    const CCODE_COMMA = 0x2C;

    /**
     * (
     */
    const CCODE_OPEN_PARENTHESES = 0x28;

    /**
     * )
     */
    const CCODE_CLOSE_PARENTHESES = 0x29;

    /**
     * [
     */
    const CCODE_OPEN_SQUARE = 0x5B;

    /**
     * ]
     */
    const CCODE_CLOSE_SQUARE = 0x5D;

    /**
     * {
     */
    const CCODE_OPEN_CURLY = 0x7B;

    /**
     * }
     */
    const CCODE_CLOSE_CURLY = 0x7D;

    /**
     * *
     */
    const CCODE_ASTERISK = 0x2D;

    /**
     * #
     */
    const CCODE_HASH = 0x23;

    /**
     * .
     */
    const CCODE_PERIOD = 0x2E;

    /**
     * b
     */
    const CCODE_NUM_BYTE = 0x62;

    /**
     * s
     */
    const CCODE_NUM_SHORT = 0x73;

    /**
     * l
     */
    const CCODE_NUM_LONG = 0x6C;

    /**
     * f
     */
    const CCODE_NUM_FLOAT = 0x66;

    /**
     * d
     */
    const CCODE_NUM_DOUBLE = 0x64;

    /**
     * f
     */
    const CCODE_BOOL_FALSE = 0x66;

    /**
     * t
     */
    const CCODE_BOOL_TRUE = 0x74;

    /**
     * -
     */
    const CCODE_MINUS = 0x2D;

    /**
     * e
     */
    const CCODE_EXP_LOWER = 0x65;

    /**
     * E
     */
    const CCODE_EXP_UPPER = 0x45;

    /**
     * token types
     */

    /**
     * Name of field
     */
    const TTYPE_NAME = 1;

    /**
     * ClassName
     */
    const TTYPE_CLASS = 2;

    /**
     * Null value
     */
    const TTYPE_NULL = 3;

    /**
     * String value
     */
    const TTYPE_STRING = 4;

    /**
     * Start of collection
     */
    const TTYPE_COLLECTION_START = 5;

    /**
     * End of collection
     */
    const TTYPE_COLLECTION_END = 6;

    /**
     * Link to recordID
     */
    const TTYPE_LINK = 7;

    /**
     * Numeric value
     */
    const TTYPE_NUMBER = 8;

    /**
     * Start of map
     */
    const TTYPE_MAP_START = 9;

    /**
     * End of map
     */
    const TTYPE_MAP_END = 10;

    /**
     * Boolean value
     */
    const TTYPE_BOOLEAN = 11;

    /**
     * Name of map key
     */
    const TTYPE_KEY = 12;

    /**
     * Parses $this->content and populates $this->data
     * @return void
     */
    public function parse()
    {
        // Form recordID
        $this->recordID = $this->clusterID . ':' . $this->recordPos;
        // Parse record content
        $this->data = new StdClass();
        // initial state
        $this->state = self::STATE_GUESS;
        // stacks
        $stackTT = array();
        $stackTV = array();
        // current
        $tokenValue = null;
        $tokenType = null;
        // is parsing collection
        $isCollection = false;
        // is parsing a map
        $isMap = false;
        // is escape symbol
        $escape = false;

        $contentLength = strlen($this->content);
        $i = 0;
        while ($i <= $contentLength) {
            $char = substr($this->content, $i, 1);
            $cCode = ord($char);
            if (($cCode >= 0x41 && $cCode <= 0x5A) || ($cCode >= 0x61 && $cCode <= 0x7A) || $cCode === 0x5F) {
                $cClass = self::CCLASS_WORD;
            } elseif ($cCode >= 0x30 && $cCode <= 0x39) {
                $cClass = self::CCLASS_NUMBER;
            } else {
                $cClass = self::CCLASS_OTHER;
            }

            switch ($this->state) {
                case self::STATE_GUESS:
                    $this->state = self::STATE_NAME;
                    $this->buffer = $char;
                    $i++;
                break;

                case self::STATE_NAME:
                    if ($cClass === self::CCLASS_WORD) {
                        // Still collecting name
                        $this->buffer .= $char;
                    } else {
                        if ($cCode === self::CCODE_COLON) {
                            // Colon found - swith state to value collecting
                            $this->state = self::STATE_VALUE;
                            // fill token with data
                            $tokenValue = $this->buffer;
                            // set token type to name
                            $tokenType = self::TTYPE_NAME;
                            // emptying buffer
                            $this->buffer = '';
                        } elseif ($cCode === self::CCODE_AT) {
                            // @ found - this was class name
                            // start to collect name - no state change
                            // fill token with data
                            $tokenValue = $this->buffer;
                            // set token type to name
                            $tokenType = self::TTYPE_CLASS;
                            // emptying buffer
                            $this->buffer = '';
                        }
                    }
                    $i++;
                break;

                case self::STATE_KEY:
                    if ($cCode === self::CCODE_COLON) {
                        // Colon found - swith state to value collecting
                        $this->state = self::STATE_VALUE;
                        // fill token with data
                        $tokenValue = $this->buffer;
                        // set token type to name
                        $tokenType = self::TTYPE_KEY;
                        // emptying buffer
                        $this->buffer = '';
                    } elseif ($cCode !== self::CCODE_DOUBLE_QUOTE) {
                        $this->buffer .= $char;
                    }
                    $i++;
                break;

                case self::STATE_VALUE:
                    if ($cCode === self::CCODE_COMMA) {
                        // No value - switch state to comma
                        $this->state = self::STATE_COMMA;
                        // token is empty
                        $tokenValue = '';
                        // token type is null
                        $tokenType = self::TTYPE_NULL;
                    } elseif ($cCode === self::CCODE_DOUBLE_QUOTE) {
                        // switch state to string collecting
                        $this->state = self::STATE_STRING;
                        $i++;
                    } elseif ($cCode === self::CCODE_HASH) {
                        // found hash - switch state to link
                        $this->state = self::STATE_LINK;
                        // add hash to value
                        $this->buffer = $char;
                        $i++;
                    } elseif ($cCode === self::CCODE_OPEN_SQUARE) {
                        // [ found, state is still value
                        $this->state = self::STATE_VALUE;
                        // token is empty
                        $tokenValue = '';
                        $tokenType = self::TTYPE_COLLECTION_START;
                        // started collection
                        $isCollection = true;
                        $i++;
                    } elseif ($cCode === self::CCODE_CLOSE_SQUARE) {
                        // ] found,
                        $this->state = self::STATE_COMMA;
                        // token is empty
                        $tokenValue = '';
                        // token type is collection end
                        $tokenType = self::TTYPE_COLLECTION_END;
                        // stopped collection
                        $isCollection = false;
                        $i++;
                    } elseif ($cCode === self::CCODE_OPEN_CURLY) {
                        // found { switch state to name
                        $this->state = self::STATE_KEY;
                        // token is empty
                        $tokenValue = '';

                        $tokenType = self::TTYPE_MAP_START;
                        // started map
                        $isMap = true;
                        $i++;
                    } elseif ($cCode === self::CCODE_CLOSE_CURLY) {
                        // } found
                        // check if null value in the end of the map
                        if (end($stackTT) === self::TTYPE_KEY) {
                            // token is empty
                            $tokenValue = '';
                            // token type is map end
                            $tokenType = self::TTYPE_NULL;
                            break;
                        }
                        $this->state = self::STATE_COMMA;
                        // token is empty
                        $tokenValue = '';
                        // token type is map end
                        $tokenType = self::TTYPE_MAP_END;
                        // stopped map
                        $isMap = false;
                        $i++;
                    } elseif ($cCode === self::CCODE_BOOL_FALSE || $cCode === self::CCODE_BOOL_TRUE) {
                        // boolean found - switch state to boolean
                        $this->state = self::STATE_BOOLEAN;
                        $this->buffer = $char;
                        $i++;
                    } else {
                        if ($cClass === self::CCLASS_NUMBER || $cCode === self::CCODE_MINUS) {
                            // number found - switch to number collecting
                            $this->state = self::STATE_NUMBER;
                            $this->buffer = $char;
                            $i++;
                        } elseif ($char === false) {
                            $i++;
                        }
                    }
                break;

                case self::STATE_STRING:
                    if ($cCode === self::CCODE_ESCAPE) {
                        // escaping 1 symbol
                        if ($escape === true) {
                            $this->buffer .= $char;
                            $escape = false;
                        } else {
                            $escape = true;
                        }
                    } elseif ($cCode === self::CCODE_DOUBLE_QUOTE) {
                        if ($escape === true) {
                            $this->buffer .= $char;
                            $escape = false;
                        } else {
                            // found end of string value - switch state to comma
                            $this->state = self::STATE_COMMA;
                            // fill token
                            $tokenValue = $this->buffer;
                            // token type is string
                            $tokenType = self::TTYPE_STRING;
                        }
                    } else {
                        // found next byte in string
                        $this->buffer .= $char;
                    }
                    $i++;
                break;

                case self::STATE_COMMA:
                    if ($cCode === self::CCODE_COMMA) {
                        // Found a comma -  switch to
                        if ($isCollection) {
                            $this->state = self::STATE_VALUE;
                        } elseif ($isMap) {
                            $this->state = self::STATE_KEY;
                        } else {
                            $this->state = self::STATE_GUESS;
                        }
                    }
                    $i++;
                break;

                case self::STATE_LINK:
                    if ($cClass === self::CCLASS_NUMBER || $cCode === self::CCODE_COLON) {
                        // found next byte in link
                        $this->buffer .= $char;
                        $i++;
                    } else {
                        // switch state to
                        if ($cCode === self::CCODE_COMMA) {
                            $this->state = self::STATE_COMMA;
                        } else {
                            $this->state = self::STATE_VALUE;
                        }
                        // fill token
                        $tokenValue = $this->buffer;
                        // token type is link
                        $tokenType = self::TTYPE_LINK;
                        // emptying buffer
                        $this->buffer = '';
                    }
                break;

                case self::STATE_NUMBER:
                    if ($cClass === self::CCLASS_NUMBER || $cCode === self::CCODE_PERIOD || $cCode === self::CCODE_MINUS || $cCode === self::CCODE_EXP_LOWER || $cCode === self::CCODE_EXP_UPPER) {
                        // found next byte in link
                        $this->buffer .= $char;
                        $i++;
                    } else {
                        // switch state to
                        if ($cCode === self::CCODE_COMMA) {
                            $this->state = self::STATE_COMMA;
                        } elseif ($cClass === self::CCLASS_WORD) {
                            $this->state = self::STATE_COMMA;
                        } else {
                            $this->state = self::STATE_VALUE;
                        }
                        // fill token
                        if ($cCode === self::CCODE_NUM_BYTE || $cCode === self::CCODE_NUM_SHORT) {
                            $tokenValue = (int) $this->buffer;
                            $i++;
                        } elseif ($cCode === self::CCODE_NUM_LONG || $cCode === self::CCODE_NUM_FLOAT || $cCode === self::CCODE_NUM_DOUBLE) {
                            $tokenValue = (float) $this->buffer;
                            $i++;
                        } else {
                            // this is int
                            $tokenValue = (int) $this->buffer;
                        }
                        // token type is link
                        $tokenType = self::TTYPE_NUMBER;
                        // emptying buffer
                        $this->buffer = '';

                    }
                break;

                case self::STATE_BOOLEAN:
                    if ($cClass === self::CCLASS_WORD) {
                        // found next byte in link
                        $this->buffer .= $char;
                        $i++;
                    } else {
                        // found end of boolean value - switch state to comma
                        $this->state = self::STATE_COMMA;
                        // fill token
                        if ($this->buffer === 'true') {
                            $tokenValue = true;
                        } else {
                            $tokenValue = false;
                        }
                        // token type is string
                        $tokenType = self::TTYPE_BOOLEAN;
                    }
                break;

                default:
                    return;
                break;
            }
            // push all found data to stack
            if ($tokenValue !== null) {
                array_push($stackTV, $tokenValue);
                array_push($stackTT, $tokenType);
                $tokenValue = null;
                $tokenType = null;
            }

            $tokenType = end($stackTT);
            switch ($tokenType) {
                case false:
                case self::TTYPE_NAME:
                case self::TTYPE_KEY:
                case self::TTYPE_COLLECTION_START:
                case self::TTYPE_MAP_START:
                // some speed up
                break;

                case self::TTYPE_CLASS:
                    $value = array_pop($stackTV);
                    array_pop($stackTT);
                    $this->className = $value;
                break;

                case self::TTYPE_STRING:
                case self::TTYPE_LINK:
                case self::TTYPE_NUMBER:
                case self::TTYPE_BOOLEAN:
                    if (!$isCollection && !$isMap) {
                        $value = array_pop($stackTV);
                        array_pop($stackTT);
                        $name = array_pop($stackTV);
                        array_pop($stackTT);
                        $this->data->$name = $value;
                    }
                break;

                case self::TTYPE_NULL:
                    if (!$isCollection && !$isMap) {
                        array_pop($stackTV);
                        array_pop($stackTT);
                        $name = array_pop($stackTV);
                        array_pop($stackTT);
                        $this->data->$name = null;
                    }
                break;

                case self::TTYPE_COLLECTION_END:
                    $values = array();
                    do {
                        $searchToken = array_pop($stackTT);
                        $value = array_pop($stackTV);

                        if ($searchToken !== self::TTYPE_COLLECTION_START && $searchToken !== self::TTYPE_COLLECTION_END) {
                            $values[] = $value;
                        }
                    } while ($searchToken !== self::TTYPE_COLLECTION_START);
                    $name = array_pop($stackTV);
                    array_pop($stackTT);
                    $values = array_reverse($values);
                    $this->data->$name = $values;
                break;

                case self::TTYPE_MAP_END:
                    $values = array();
                    do {
                        $searchToken = array_pop($stackTT);
                        $value = array_pop($stackTV);
                        // check for null value
                        if ($searchToken === self::TTYPE_NULL) {
                            $value = null;
                        }
                        if ($searchToken !== self::TTYPE_MAP_START && $searchToken !== self::TTYPE_MAP_END) {
                            $name = array_pop($stackTV);
                            array_pop($stackTT);
                            $values[$name] = $value;
                        }
                    } while ($searchToken !== self::TTYPE_MAP_START);
                    $name = array_pop($stackTV);
                    array_pop($stackTT);
                    $values = array_reverse($values);
                    $this->data->$name = $values;
                break;

                default:
                break;
            }
        }
    }
}