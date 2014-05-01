<?php
namespace Cloud\Core\Helper;

Class Data extends AbstractHelper
{
    /*
     * For random number generation
    */
    const CHARSET_ALPHANUM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    const CHARSET_ALPHANUM_CASE = 'abcdefghijklnmopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    public function getRandomString($valid_chars = self::CHARSET_ALPHANUM, $length = 8)
    {
        // start with an empty random string
        $random_string = "";

        // count the number of chars in the valid chars string so we know how many choices we have
        $num_valid_chars = strlen($valid_chars);

        // repeat the steps until we've created a string of the right length
        for ($i = 0; $i < $length; $i++) {
            // pick a random number from 1 up to the number of valid chars
            $random_pick = mt_rand(1, $num_valid_chars);

            // take the random character out of the string of valid chars
            // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
            $random_char = $valid_chars[$random_pick - 1];

            // add the randomly-chosen char onto the end of our string so far
            $random_string .= $random_char;
        }

        // return our finished random string
        return $random_string;
    }
}