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
 * Get information about new versions of Composr (or more accurately, what's wrong with this version).
 *
 * @return tempcode Information about the installed Composr version
 */
function get_future_version_information()
{
    require_lang('version');

    $url = 'http://compo.sr/version.php?version=' . rawurlencode(get_version_dotted()) . '&lang=' . rawurlencode(user_lang());

    static $data = null; // Cache
    if (is_null($data)) {
        $data = http_download_file($url, null, false);
    }
    if (!is_null($data)) {
        $data = str_replace('"../upgrader.php"', '"' . get_base_url() . '/upgrader.php"', $data);

        if ($GLOBALS['XSS_DETECT']) {
            ocp_mark_as_escaped($data);
        }

        require_code('character_sets');

        $data = convert_to_internal_encoding($data);

        $table = make_string_tempcode($data);
    } else {
        $table = paragraph(do_lang_tempcode('CANNOT_CONNECT_HOME'), 'dfsdff32ffd');
    }

    require_code('xhtml');
    return make_string_tempcode(xhtmlise_html($table->evaluate()));
}

/**
 * Get branch version number for a Composr version.
 *
 * @param  ?float $general General version number (null: on disk version)
 * @return string Branch version number (null: on disk version)
 */
function get_version_branch($general = null)
{
    if (is_null($general)) {
        $general = cms_version_number();
    }

    return float_to_raw_string($general, 10, true) . '.x';
}

/**
 * Get dotted version from given Composr-version-registry (version.php) supplied components.
 *
 * @param  ?integer $main Main version number (null: on disk version)
 * @param  ?string $minor Minor version number (null: on disk version)
 * @return string Dotted version number
 */
function get_version_dotted($main = null, $minor = null)
{
    if (is_null($main)) {
        $main = cms_version();
    }
    if (is_null($minor)) {
        $minor = cms_version_minor();
    }

    return strval($main) . (($minor == '0') ? '' : ('.' . $minor));
}

/**
 * Gets any random way of writing a version number (in all of Composr's history) and makes it a dotted style like "3.2.beta2".
 * Note that the dotted format is compatible with PHP's version_compare function.
 *
 * @param  string $any_format Any reasonable input
 * @return string Pretty version number
 */
function get_version_dotted__from_anything($any_format)
{
    $pretty = $any_format;

    // Strip useless bits
    $pretty = preg_replace('#[-\s]*(final|gold)#i', '', $pretty);
    $pretty = preg_replace('#(Composr |version )*#i', '', $pretty);
    $pretty = trim($pretty);

    // Change dashes and spaces to dots
    $pretty = str_replace(array('-', ' '), array('.', '.'), $pretty);

    foreach (array('alpha', 'beta', 'RC') as $qualifier) {
        $pretty = preg_replace('#\.?' . preg_quote($qualifier, '#') . '\.?#i', '.' . $qualifier, $pretty);
    }

    // Canonical to not have extra .0's on end. Don't really care about what Composr stores as we clean this up in our server's version.php - it is crucial that news post and download names are canonical though so version.php works. NB: Latest recommended versions are done via download name and description labelling.
    $pretty = preg_replace('#(\.0)+($|\.alpha|\.beta|\.RC)#', '', $pretty);

    return $pretty;
}

/**
 * Analyse a dotted version number into components.
 *
 * @param  string $dotted Dotted version number
 * @return array Tuple of components: dotted basis version (i.e. with no alpha/beta/RC component and no trailing zeros), qualifier (blank, or alpha, or beta, or RC), qualifier number (NULL if not an alpha/beta/RC), dotted version number with trailing zeros to always cover 3 components
 */
function get_version_components__from_dotted($dotted)
{
    // Now split it up version number
    $qualifier = mixed();
    $qualifier_number = mixed();
    $basis_dotted_number = mixed();
    foreach (array('RC', 'beta', 'alpha') as $type) {
        if (strpos($dotted, '.' . $type) !== false) {
            $qualifier = $type;
            $qualifier_number = intval(substr($dotted, strrpos($dotted, '.' . $type) + strlen('.' . $type)));
            $basis_dotted_number = substr($dotted, 0, strrpos($dotted, '.' . $type));
            break;
        }
    }
    if (is_null($basis_dotted_number)) {
        $basis_dotted_number = $dotted;
    }

    $long_dotted_number = $basis_dotted_number . str_repeat('.0', max(0, 2 - substr_count($basis_dotted_number, '.')));

    return array($basis_dotted_number, $qualifier, $qualifier_number, $long_dotted_number);
}

/**
 * Get a pretty version number for a Composr version.
 * This pretty style is not used in Composr code per se, but is shown to users and hence Composr may need to recognise it when searching news posts, download databases, etc.
 *
 * @param  string $pretty Pretty version number
 * @return string Dotted version number
 */
function get_version_pretty__from_dotted($pretty)
{
    return preg_replace('#\.(alpha|beta|RC)#', ' ${1}', $pretty);
}
