<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/**
 * Test password strength.
 *
 * @param  string $password The password to check
 * @param  string $username The username that will go with the password
 * @return integer Password strength (1-10)
 */
function test_password($password, $username = '')
{
    if (strlen($password) == 0) {
        return 1;
    }

    if (($username != '') && ($username == $password)) {
        return 1;
    }

    $strength = 0;

    // Get the length of the password
    $length = strlen($password);

    // Check if password is not all lower case
    if (strtolower($password) != $password) {
        $strength += 1;
    }

    // Check if password is not all upper case
    if (strtoupper($password) == $password) {
        $strength += 1;
    }

    // Check string length is 8 -15 chars
    if ($length >= 8 && $length <= 15) {
        $strength += 1;
    }

    // Check if length is 16 - 35 chars
    if ($length >= 16 && $length <= 35) {
        $strength += 2; // Check if length greater than 35 chars
    } elseif ($length > 35) {
        $strength += 3;
    }

    // Get the numbers in the password
    $numbers = array();
    preg_match_all('/[0-9]/', $password, $numbers);
    $strength += count($numbers[0]);

    // Check for special chars
    $specialchars = array();
    preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^]/', $password, $specialchars);
    $strength += count($specialchars[0]);

    // Get the number of unique chars
    $chars = preg_split('#(.)#', $password, null, PREG_SPLIT_DELIM_CAPTURE);
    $num_unique_chars = count(array_unique($chars)) - 1;
    $strength += $num_unique_chars * 2;

    // Strength is a number 1-10
    $strength = $strength > 99 ? 99 : $strength;
    $strength = intval(ceil(floatval($strength) / 10.0 + 1.0));

    return $strength;
}
