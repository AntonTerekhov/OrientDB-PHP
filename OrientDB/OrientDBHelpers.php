<?php

/**
 *
 * OrientDBHelpers cointains methods not needed in production use of this
 * library
 *
 */
class OrientDBHelpers
{
    /**
     *
     * Echoes bytes in $data to console
     * @param string $data
     * @param string $newline
     */
    static public function hexDump($data, $newline = PHP_EOL)
    {

        static $from = '';

        static $to = '';

        /**
         * number of bytes per line
         */
        static $width = 16;

        /**
         * padding for non-visible characters
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