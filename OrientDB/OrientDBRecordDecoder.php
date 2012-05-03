<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * Class to decode OrientDB records
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Datatypes
 */
class OrientDBRecordDecoder
{

    /**
     * ClassName as parsed from document
     * @var string
     */
    public $className;

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
     * Used to interrupt in case of embedded document
     * @var bool
     */
    protected $continue = true;

    /**
     * Current position in content
     * @var int
     */
    protected $i = 0;

    /**
     * Stack for token values
     * @var array
     */
    protected $stackTV = array();

    /**
     *
     * Stack fo token types
     * @var array
     */
    protected $stackTT = array();

    /**
     * List of possible states
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
     * collecting field value
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
     * c
     */
    const CCODE_NUM_DECIMAL = 0x63;

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
     * t
     */
    const CCODE_DATE = 0x74;

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
     * Embedded document
     */
    const TTYPE_EMBEDDED = 13;

    public function __construct($content)
    {
        $this->content = $content;
        $this->decode();
    }

    /**
     * Parses $this->content and populates $this->data and $this->className
     * @throws OrientDBDeSerializeException
     * @return void
     */
    protected function decode()
    {
        // Parse record content
        // There is no need to use OrientDBRecordData here, as data will be copied for root record, and no parsing on demand for embedded records is made
        $this->data = new StdClass();
        // initial state
        $this->state = self::STATE_GUESS;
        // is parsing collection
        $isCollection = false;
        // is parsing a map
        $isMap = false;
        // is escape symbol
        $escape = false;

        $contentLength = strlen($this->content);

        while ($this->i <= $contentLength && $this->continue) {
            $char = substr($this->content, $this->i, 1);
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
                    $this->i++;
                break;

                case self::STATE_NAME:
                    if ($cCode === self::CCODE_COLON) {
                        // Colon found - switch state to value collecting
                        $this->state = self::STATE_VALUE;
                        // fill token with data
                        $this->stackPush(self::TTYPE_NAME);
                    } elseif ($cCode === self::CCODE_AT) {
                        // @ found - this was class name
                        // start to collect name - no state change
                        // fill token with data
                        $this->stackPush(self::TTYPE_CLASS);
                    } else {
                        // Trying to fast-forward name collecting
                        if ($this->i < strlen($this->content)) {
                            // This can be field name or class name
                            $pos_colon = strpos($this->content, ':', $this->i);
                            $pos_at = strpos($this->content, '@', $this->i);
                            // Check, which one is closest
                            if ($pos_at !== false) {
                                $pos = min($pos_at, $pos_colon);
                            } else {
                                $pos = $pos_colon;
                            }
                        } else {
                            $pos = false;
                        }

                        if ($pos !== false && $pos > $this->i) {
                            // Position is found and we had enough length to perform fast-forward
                            $this->buffer .= substr($this->content, $this->i, ($pos - $this->i));
                            $this->i = $pos;
                            break;
                        }
                        // Still collecting name
                        $this->buffer .= $char;
                    }
                    $this->i++;
                break;

                case self::STATE_KEY:
                    /**
                     * @TODO If map keys can contain escaping characters
                     */
                    if ($cCode === self::CCODE_COLON) {
                        // Colon found - switch state to value collecting
                        $this->state = self::STATE_VALUE;
                        // fill token with data
                        $this->stackPush(self::TTYPE_KEY);
                    } else {
                        // Fast-forwarding to " symbol
                        if ($this->i < strlen($this->content)) {
                            $pos = strpos($this->content, '"', $this->i);
                        } else {
                            $pos = false;
                        }
                        if ($pos !== false && $pos > $this->i) {
                            // Before " symbol
                            $this->buffer = substr($this->content, $this->i, ($pos - $this->i));
                            $this->i = $pos;
                        }
                    }
                    $this->i++;
                break;

                case self::STATE_VALUE:
                    if ($cCode === self::CCODE_COMMA) {
                        // No value - switch state to comma
                        $this->state = self::STATE_COMMA;
                        // token type is null
                        $this->stackPush(self::TTYPE_NULL);
                    } elseif ($cCode === self::CCODE_DOUBLE_QUOTE) {
                        // switch state to string collecting
                        $this->state = self::STATE_STRING;
                        $this->i++;
                    } elseif ($cCode === self::CCODE_HASH) {
                        // found hash - switch state to link
                        $this->state = self::STATE_LINK;
                        // add hash to value
                        $this->buffer = $char;
                        $this->i++;
                    } elseif ($cCode === self::CCODE_OPEN_SQUARE) {
                        // [ found, state is still value
                        $this->state = self::STATE_VALUE;
                        // token type is collection start
                        $this->stackPush(self::TTYPE_COLLECTION_START);
                        // started collection
                        $isCollection = true;
                        $this->i++;
                    } elseif ($cCode === self::CCODE_CLOSE_SQUARE) {
                        // ] found,
                        $this->state = self::STATE_COMMA;
                        // token type is collection end
                        $this->stackPush(self::TTYPE_COLLECTION_END);
                        // stopped collection
                        $isCollection = false;
                        $this->i++;
                    } elseif ($cCode === self::CCODE_OPEN_CURLY) {
                        // found { switch state to name
                        $this->state = self::STATE_KEY;
                        // token type is map start
                        $this->stackPush(self::TTYPE_MAP_START);
                        // started map
                        $isMap = true;
                        $this->i++;
                    } elseif ($cCode === self::CCODE_CLOSE_CURLY) {
                        // } found
                        // check if null value in the end of the map
                        if ($this->stackGetLastType() === self::TTYPE_KEY) {
                            // token type is map end
                            $this->stackPush(self::TTYPE_NULL);
                            break;
                        }
                        $this->state = self::STATE_COMMA;
                        // token type is map end
                        $this->stackPush(self::TTYPE_MAP_END);
                        // stopped map
                        $isMap = false;
                        $this->i++;
                    } elseif ($cCode === self::CCODE_OPEN_PARENTHESES) {
                        // ( found, state is COMMA
                        $this->state = self::STATE_COMMA;
                        // increment position so we can transfer clean document
                        $this->i++;
                        // create new parser
                        $parser = new OrientDBRecordDecoder(substr($this->content, $this->i));
                        // create new embedded document and populate its values
                        $tokenValue = new OrientDBRecord();
                        $tokenValue->data = $parser->data;
                        $tokenValue->className = $parser->className;
                        $tokenValue->setParsed();
                        // token type is embedded
                        $this->stackPush(self::TTYPE_EMBEDDED, $tokenValue);
                        // fast forward to embedded position
                        $this->i += $parser->i;
                        // increment counter so we can continue on clean document
                        $this->i++;
                        break;
                    } elseif ($cCode === self::CCODE_CLOSE_PARENTHESES) {
                        // end of current document reached
                        $this->continue = false;
                        break;
                    } elseif ($cCode === self::CCODE_BOOL_FALSE || $cCode === self::CCODE_BOOL_TRUE) {
                        // boolean found - switch state to boolean
                        $this->state = self::STATE_BOOLEAN;
                        $this->buffer = $char;
                        $this->i++;
                    } else {
                        if ($cClass === self::CCLASS_NUMBER || $cCode === self::CCODE_MINUS) {
                            // number found - switch to number collecting
                            $this->state = self::STATE_NUMBER;
                            $this->buffer = $char;
                            $this->i++;
                        } elseif ($char === false) {
                            $this->i++;
                        }
                    }
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
                        $this->i++;
                    } else {
                        $this->state = self::STATE_VALUE;
                    }
                break;

                case self::STATE_STRING:
                    // Check, if we can fast-forward to next " or \ symbol
                    if ($this->i < strlen($this->content)) {
                        // Separate search for symbols
                        $pos_quote = strpos($this->content, '"', $this->i);
                        $pos_escape = strpos($this->content, '\\', $this->i);
                        // Get first position
                        if ($pos_escape !== false) {
                            $pos = min($pos_quote, $pos_escape);
                        } else {
                            $pos = $pos_quote;
                        }
                    } else {
                        $pos = false;
                    }
                    if ($pos !== false) {
                        // If position is found
                        if ($pos > $this->i + 1) {
                            // And position is before any possible escape symbol
                            // Add to buffer
                            $this->buffer .= substr($this->content, $this->i, ($pos - $this->i - 1));
                            // Fast-forwarding
                            $this->i = $pos - 1;
                            break;
                        }
                    }

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
                            // token type is string
                            $this->stackPush(self::TTYPE_STRING);
                        }
                    } else {
                        // found next byte in string
                        $this->buffer .= $char;
                    }
                    $this->i++;
                break;

                case self::STATE_LINK:
                    // Fast-forward
                    $result = preg_match('/\d+:\d+/', $this->content, $matches, PREG_OFFSET_CAPTURE, $this->i);
                    // And matches from current position
                    if ($result && $matches[0][1] === $this->i) {
                        $this->buffer = $matches[0][0];
                        $this->i += strlen($this->buffer);
                    } else {
                        // switch state to
                        if ($cCode === self::CCODE_COMMA) {
                            $this->state = self::STATE_COMMA;
                        } else {
                            $this->state = self::STATE_VALUE;
                        }
                        // token type is link
                        $this->stackPush(self::TTYPE_LINK, new OrientDBTypeLink($this->buffer));
                    }
                break;

                case self::STATE_NUMBER:
                    // Fast-forward
                    $result = preg_match('/[\d\.e-]+/i', $this->content, $matches, PREG_OFFSET_CAPTURE, $this->i);
                    // And matches from current position
                    if ($result && $matches[0][1] === $this->i) {
                        $this->buffer .= $matches[0][0];
                        $this->i += strlen($matches[0][0]);
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
                            $this->i++;
                        } elseif ($cCode === self::CCODE_NUM_LONG || $cCode === self::CCODE_NUM_FLOAT || $cCode === self::CCODE_NUM_DOUBLE || $cCode === self::CCODE_NUM_DECIMAL) {
                            $tokenValue = (float) $this->buffer;
                            $this->i++;
                        } elseif ($cCode === self::CCODE_DATE) {
                            // This is datetime
                            $tokenValue = new OrientDBTypeDate($this->buffer);
                            $this->i++;
                        } else {
                            // this is int
                            $tokenValue = (int) $this->buffer;
                        }
                        // token type is number
                        $this->stackPush(self::TTYPE_NUMBER, $tokenValue);
                    }
                break;

                case self::STATE_BOOLEAN:
                    // Fast-forward
                    // @TODO It's possible to gain more speed by checking not entire literal, but only first (really, second) character
                    if (strpos($this->content, 'rue', $this->i) === $this->i) {
                        $tokenValue = true;
                        $this->i += 3;
                    } elseif (strpos($this->content, 'alse', $this->i) === $this->i) {
                        $tokenValue = false;
                        $this->i += 4;
                    } else {
                        throw new OrientDBDeSerializeException('Can\'t de-serialize boolean value on key "' . $this->stackGetLastKey() . '"');
                    }
                    // found end of boolean value - switch state to comma
                    $this->state = self::STATE_COMMA;
                    // token value is boolean
                    $this->stackPush(self::TTYPE_BOOLEAN, $tokenValue);
                break;

                default:
                    return;
                break;
            }

            switch ($this->stackGetLastType()) {
                case false:
                case self::TTYPE_NAME:
                case self::TTYPE_KEY:
                case self::TTYPE_COLLECTION_START:
                case self::TTYPE_MAP_START:
                // some speed up
                break;

                case self::TTYPE_CLASS:
                    list (, $value) = $this->stackPop();
                    $this->className = $value;
                break;

                case self::TTYPE_STRING:
                case self::TTYPE_LINK:
                case self::TTYPE_NUMBER:
                case self::TTYPE_BOOLEAN:
                case self::TTYPE_EMBEDDED:
                    if (!$isCollection && !$isMap) {
                        list (, $value) = $this->stackPop();
                        list (, $name) = $this->stackPop();
                        $this->data->$name = $value;
                    }
                break;

                case self::TTYPE_NULL:
                    if (!$isCollection && !$isMap) {
                        $this->stackPop();
                        list (, $name) = $this->stackPop();
                        $this->data->$name = null;
                    }
                break;

                case self::TTYPE_COLLECTION_END:
                    $values = array();
                    do {
                        list ($searchToken, $value) = $this->stackPop();

                        if ($searchToken !== self::TTYPE_COLLECTION_START && $searchToken !== self::TTYPE_COLLECTION_END) {
                            $values[] = $value;
                        }
                    } while ($searchToken !== self::TTYPE_COLLECTION_START);
                    list (, $name) = $this->stackPop();
                    $values = array_reverse($values);
                    $this->data->$name = $values;
                break;

                case self::TTYPE_MAP_END:
                    $values = array();
                    do {
                        list ($searchToken, $value) = $this->stackPop();
                        // check for null value
                        if ($searchToken === self::TTYPE_NULL) {
                            $value = null;
                        }
                        if ($searchToken !== self::TTYPE_MAP_START && $searchToken !== self::TTYPE_MAP_END) {
                            list (, $key) = $this->stackPop();
                            $values[$key] = $value;
                        }
                    } while ($searchToken !== self::TTYPE_MAP_START);
                    list (, $name) = $this->stackPop();
                    $values = array_reverse($values);
                    $this->data->$name = $values;
                break;

                default:
                break;
            }
        }
    }

    /**
     * Pushes found value to internal stack and flushes $this->buffer. If no
     * $tokenValue is given, uses $this->buffer
     * @param int $tokenType
     * @param mixed $tokenValue
     */
    protected function stackPush($tokenType, $tokenValue = null)
    {
        array_push($this->stackTT, $tokenType);
        if ($tokenValue === null) {
            $tokenValue = $this->buffer;
        }
        array_push($this->stackTV, $tokenValue);
        $this->buffer = '';
    }

    /**
     * Pop value from internal stack
     * @return array
     */
    protected function stackPop()
    {
        return array(
            array_pop($this->stackTT),
            array_pop($this->stackTV));
    }

    /**
     * Return last token type
     * @return int
     * @example TTYPE_NAME
     */
    protected function stackGetLastType()
    {
        return end($this->stackTT);
    }

    /**
     * Return string of last found key
     * @return string
     */
    protected function stackGetLastKey()
    {
        $depth = false;
        for ($i = count($this->stackTT) - 1; $i >= 0; $i--) {
            if ($this->stackTT[$i] === self::TTYPE_NAME) {
                $depth = $i;
                break;
            }
        }
        if ($depth !== false) {
            return $this->stackTV[$depth];
        }
    }
}