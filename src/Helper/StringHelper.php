<?php

declare(strict_types=1);

namespace App\Helper;

class StringHelper
{
    private $normalizer;

    public function __construct(\Normalizer $normalizer)
    {
        $this->normalizer = $normalizer;
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
            throw new \Exception("L'extension 'intl' est requise pour la génération de slug.");
        }
        // Normalize the string (remove accents, diacritics, etc.).
        $string = $this->normalizer->normalize($string, \Normalizer::FORM_D);

        if (false === $string) {
            throw new \Exception("Une erreur s'est produite lors de la normalisation de la chaîne.");
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
