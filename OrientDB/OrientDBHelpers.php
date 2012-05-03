<?php

/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * OrientDBHelpers contains methods not needed in production use of this
 * library
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Debug
 */
class OrientDBHelpers
{

    /**
     * Echoes bytes in $data to stdout
     * @param string $data
     * @param string $newline
     */
    static public function hexDump($data, $newline = PHP_EOL)
    {
        /**
         * @var string
         */
        static $from = '';

        /**
         * @var string
         */
        static $to = '';

        /**
         * number of bytes per line
         * @var int
         */
        static $width = 16;

        /**
         * padding for non-visible characters
         * @var string
         */
        static $pad = '.';

        if ($from === '') {
            for ($i = 0; $i <= 0xFF; $i++) {
                $from .= chr($i);
                $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
            }
        }

        $hex = str_split(bin2hex($data), $width * 2);
        $chars = str_split(strtr($data, $from, $to), $width);

        $offset = 0;
        foreach ($hex as $i => $line) {
            echo sprintf('%6X', $offset) . ' : ' . implode(' ', str_split(str_pad($line, $width * 2, ' '), 2)) . ' [' . $chars[$i] . ']' . $newline;
            $offset += $width;
        }
    }
}