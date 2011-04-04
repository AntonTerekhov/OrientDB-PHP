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

    const STATE_COMMA = 4; // collecting of comma

    const STATE_LINK = 5; // collecting of link to other record

    const STATE_NUBMER = 6; // collecting of number



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

    // token types
    const TTYPE_NAME = 1;
    const TTYPE_CLASS = 2;
    const TTYPE_NULL = 3;
    const TTYPE_STRING = 4;

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

        for ($i = 0; $i < strlen($this->content); $i++) {
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
                    $this->buffer = $char;
                    $this->state = self::STATE_NAME;
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
                            // At found - this was class name
                            // start to collect name - no state change
                            // fill token with data
                            $tokenValue = $this->buffer;
                            // set token type to name
                            $tokenType = self::TTYPE_CLASS;
                            // emptying buffer
                            $this->buffer = '';
                        }
                    }
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
                    } elseif (0) {

                    } elseif (0) {

                    } else {
                        if ($cClass === self::CCLASS_NUMBER) {
                            // number found - switch to number collecting
                            $this->state = self::STATE_NUMBER;
                            $buffer = $char;
                        }
                    }
                break;

                case self::STATE_STRING:
                    if ($cCode === self::CCODE_ESCAPE) {
                        // @TODO escaping 1 symbol
                        // по идее, так делать неправильно, надо текущий чар игнорить, а следкющий -  вставлять в буфер
                        $i++;
                    } elseif ($cCode === self::CCODE_DOUBLE_QUOTE) {
                        // found end of string value - switch state to comma
                        $this->state = self::STATE_COMMA;
                        // fill token
                        $tokenValue = $this->buffer;
                        // token type is string
                        $tokenType = self::TTYPE_STRING;
                    } else {
                        // found next byte in string
                        $this->buffer .= $char;
                    }
                break;

                case self::STATE_COMMA:
                    if ($cCode === self::CCODE_COMMA) {
                        // Found a comma -  switch to guess state
                        $this->state = self::STATE_GUESS;
                    }
                break;

                case self::STATE_LINK:
                    return;
                break;

                case self::STATE_NUBMER:
                    return;
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
                case self::TTYPE_STRING:
                    $value = array_pop($stackTV);
                    array_pop($stackTT);
                    $name = array_pop($stackTV);
                    array_pop($stackTT);
                    $this->data->$name = $value;
                break;

                default:
                break;
            }
        }
    }
}