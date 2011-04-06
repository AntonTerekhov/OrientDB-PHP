<?php

class OrientDBRecord
{

    /**
     *
     * @var int
     */
    public $classID;

    /**
     *
     * @var string
     */
    public $className;

    /**
     *
     * @var string Document type
     */
    public $type;

    /**
     *
     * @var int ClusterID
     */
    public $clusterID;

    /**
     *
     * @var int Record position in cluster
     */
    public $recordPos;

    /**
     *
     * @var string full qualified record ID '1:1'
     */
    public $recordID;

    /**
     *
     * @var int Document version
     */
    public $version;

    /**
     *
     * @var string Document source
     */
    public $content;

    /**
     *
     * @var StdClass A placeholder for document data
     */
    public $data;

    /**
     *
     * @var int Parser state
     */
    protected $state;

    /**
     *
     * @var string Parser buffer
     */
    protected $buffer;

    // List of possble states

    const STATE_GUESS = 0; // what we're going to collect?

    const STATE_NAME = 1; // collecting name

    const STATE_VALUE = 2; // collectiong value

    const STATE_STRING = 3; // collecting double-quoted string

    const STATE_COMMA = 4; // collecting of comma between fileds

    const STATE_LINK = 5; // collecting of link to other record

    const STATE_NUMBER = 6; // collecting of number


    const CCLASS_WORD = 1; // char of word class

    const CCLASS_NUMBER = 2; // char of number class

    const CCLASS_OTHER = 0; // any other chars


    // Character codes:
    const CCODE_AT = 0x40;            // @
    const CCODE_COLON = 0x3A;         // :
    const CCODE_DOUBLE_QUOTE = 0x22;  // "
    const CCODE_ESCAPE = 0x5C;        // \
    const CCODE_COMMA = 0x2C;         // ,
    const CCODE_OPEN_BRACKET = 0x5B;  // [
    const CCODE_CLOSE_BRACKET = 0x5D; // ]
    const CCODE_OPEN_CURLY = 0x7B;    // {
    const CCODE_CLOSE_CURLY = 0x7D;   // }
    const CCODE_ASTERISK = 0x2D;      // *
    const CCODE_HASH = 0x23;          // #
    const CCODE_PERIOD = 0x2E;        // .
    const CCODE_NUM_BYTE = 0x62;      // b
    const CCODE_NUM_SHORT = 0x73;     // s
    const CCODE_NUM_LONG = 0x6C;      // l
    const CCODE_NUM_FLOAT = 0x66;     // f
    const CCODE_NUM_DOUBLE = 0x64;    // d


    // token types
    const TTYPE_NAME = 1;
    const TTYPE_CLASS = 2;
    const TTYPE_NULL = 3;
    const TTYPE_STRING = 4;
    const TTYPE_COLLECTION_START = 5;
    const TTYPE_COLLECTION_END = 6;
    const TTYPE_LINK = 7;
    const TTYPE_NUMBER = 8;

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

                case self::STATE_VALUE:
                    if ($cCode === self::CCODE_COMMA) {
                        // No value - switch state to guess
                        $this->state = self::STATE_GUESS;
                        // token is empty
                        $tokenValue = '';
                        // token type is null
                        $tokenType = self::TTYPE_NULL;
                    } elseif ($cCode === self::CCODE_DOUBLE_QUOTE) {
                        // switch state to string collecting
                        $this->state = self::STATE_STRING;
                    } elseif ($cCode === self::CCODE_HASH) {
                        // found hash - switch state to link
                        $this->state = self::STATE_LINK;
                        // add hash to value
                        $this->buffer = $char;
                    } elseif ($cCode === self::CCODE_OPEN_BRACKET) {
                        // [ found, state is still value
                        $this->state = self::STATE_VALUE;
                        $tokenValue = '';
                        $tokenType = self::TTYPE_COLLECTION_START;
                        $isCollection = true;
                    } elseif ($cCode === self::CCODE_CLOSE_BRACKET) {
                        // ] found,
                        $this->state = self::STATE_COMMA;
                        // token is empty
                        $tokenValue = '';
                        // token type is collection end
                        $tokenType = self::TTYPE_COLLECTION_END;
                        $isCollection = false;
                    } else {
                        if ($cClass === self::CCLASS_NUMBER) {
                            // number found - switch to number collecting
                            $this->state = self::STATE_NUMBER;
                            $this->buffer = $char;
                        }
                    }
                    $i++;
                break;

                case self::STATE_STRING:
                    if ($cCode === self::CCODE_ESCAPE) {
                        // escaping 1 symbol
                        if ($escape ===  true) {
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
                        if ($cCode === self::CCODE_CLOSE_BRACKET) {
                            $this->state = self::STATE_VALUE;
                        } else {
                            $this->state = self::STATE_COMMA;
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
                    if ($cClass === self::CCLASS_NUMBER || $cCode === self::CCODE_PERIOD) {
                        // found next byte in link
                        $this->buffer .= $char;
                        $i++;
                    } else {
                        // switch state to
                        if ($cCode === self::CCODE_COMMA) {
                            $this->state = self::STATE_COMMA;
                        } elseif ($cCode === self::CCODE_CLOSE_BRACKET) {
                            $this->state = self::STATE_VALUE;
                        }
                        // fill token
                        if ($cCode === self::CCODE_COMMA) {
                            // this is int
                            $tokenValue = (int) $this->buffer;
                        } elseif ($cCode === self::CCODE_NUM_BYTE || $cCode ===  self::CCODE_NUM_SHORT) {
                            $tokenValue = (int) $this->buffer;
                        } elseif ($cCode === self::CCODE_NUM_LONG || $cCode ===  self::CCODE_NUM_FLOAT || $cCode ===  self::CCODE_NUM_DOUBLE) {
                            $tokenValue = (float) $this->buffer;
                        }
                        // token type is link
                        $tokenType = self::TTYPE_NUMBER;
                        // emptying buffer
                        $this->buffer = '';
                        $i++;
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
                case self::TTYPE_CLASS:
                    $value = array_pop($stackTV);
                    array_pop($stackTT);
                    $this->className = $value;
                break;

                case self::TTYPE_STRING:
                    if (!$isCollection) {
                        $value = array_pop($stackTV);
                        array_pop($stackTT);
                        $name = array_pop($stackTV);
                        array_pop($stackTT);
                        $this->data->$name = $value;
                    }
                break;

                case self::TTYPE_LINK:
                    if (!$isCollection) {
                        $value = array_pop($stackTV);
                        array_pop($stackTT);
                        $name = array_pop($stackTV);
                        array_pop($stackTT);
                        $this->data->$name = $value;
                    }
                break;

                case self::TTYPE_NUMBER:
                    if (!$isCollection) {
                        $value = array_pop($stackTV);
                        array_pop($stackTT);
                        $name = array_pop($stackTV);
                        array_pop($stackTT);
                        $this->data->$name = $value;
                    }
                break;

                case self::TTYPE_NULL:
                    array_pop($stackTV);
                    array_pop($stackTT);
                    $name = array_pop($stackTV);
                    array_pop($stackTT);
                    $this->data->$name = null;
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

                default:
                break;
            }
        }
    }
}