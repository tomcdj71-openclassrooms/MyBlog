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
     * This function transform a string to a slug.
     *
     * @param string $string
     */
    public function slugify($string): string
    {
        // Ensure that the 'intl' extension is loaded.
        if (!extension_loaded('intl')) {
            throw new \Exception("The 'intl' extension is required for slug generation.");
        }
        // Normalize the string (remove accents, diacritics, etc.).
        $string = \Normalizer::normalize($string, \Normalizer::FORM_D);
        if (false === $string) {
            throw new \Exception('An error occurred while normalizing the string.');
        }

        // Remove non-letter or non-digit characters, except for spaces.
        $string = preg_replace('~[^\\pL\d\s]+~u', '', $string);
        // Replace any spaces with a hyphen.
        $string = preg_replace('~[\s]+~u', '-', $string);
        // Convert the string to ASCII, if possible.
        $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
        // Remove any remaining unwanted characters.
        $string = preg_replace('~[^-\w]+~', '', $string);
        // Remove consecutive hyphens.
        $string = preg_replace('~-+~', '-', $string);
        // Trim hyphens from the beginning and end of the string.
        $string = trim($string, '-');
        // Convert the string to lowercase.
        return strtolower($string);
    }
}
