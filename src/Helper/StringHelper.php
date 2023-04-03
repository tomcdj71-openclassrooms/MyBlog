<?php

declare(strict_types=1);

namespace App\Helper;

class StringHelper
{
    /**
     * Get a string between two strings.
     *
     * @param string $string
     * @param string $start
     * @param string $end
     */
    public function getStringBetween($string, $start, $end)
    {
        $string = ' '.$string;
        $ini = strpos($string, $start);
        if (0 == $ini) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    /**
     * Remove special characters and accents.
     *
     * @param string $string
     */
    public function removeSpecialAndAccent($string): string
    {
        $string = preg_replace('~[^\pL\d]+~u', '-', $string);
        $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
        $string = preg_replace('~[^-\w]+~', '', $string);
        $string = preg_replace('~-+~', '-', $string);

        return strtolower($string);
    }
}
