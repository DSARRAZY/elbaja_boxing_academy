<?php
/**
 * Auteur: Khaled Benharrat, Damien Sarrazy, Kevin Chalumeau
 * Date: 30/06/2020
 */

//src/Service/Slugify.php
namespace App\Service;

class Slugify
{
    /**
    * Generates a slug with a string
    * @param string $input
    */
    public function generate(string $input) :string
    {
        // conversion of special characters to unicode characters
        // return a slug without punctuation, without spaces at the beginning and end of the chain,
        // without successive "-" and in lowercase
        if (!empty($input)) {
            $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $input);
        }
        if (!empty($slug)) {
            $slug = trim($slug);
            $slug = preg_replace("/[[:punct:]]/", "", $slug);
        }
        if (!empty($slug)) {
            $slug = str_replace(" ", "-", $slug);
            $slug = strtolower($slug);
            return $slug;
        }
    }
}
