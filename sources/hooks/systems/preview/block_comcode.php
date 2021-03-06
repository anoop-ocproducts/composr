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
 * Hook class.
 */
class Hook_preview_block_comcode
{
    /**
     * Find whether this preview hook applies.
     *
     * @return array Triplet: Whether it applies, the attachment ID type, whether the forum DB is used [optional]
     */
    public function applies()
    {
        if (!has_privilege(get_member(), 'comcode_dangerous')) {
            return array(false, null, false);
        }

        $applies = !is_null(post_param_string('block', null));
        return array($applies, null, false);
    }

    /**
     * Run function for preview hooks.
     *
     * @return array A pair: The preview, the updated post Comcode
     */
    public function run()
    {
        if (!has_privilege(get_member(), 'comcode_dangerous')) {
            access_denied('I_ERROR');
        }

        require_code('zones2');
        require_code('zones3');

        $bparameters = '';
        $block = post_param_string('block');
        $parameters = get_block_parameters($block);
        foreach ($parameters as $parameter) {
            if (($parameter == 'select') && (in_array($block, array('bottom_news', 'main_news', 'side_news', 'side_news_archive')))) {
                $value = post_param_string($parameter, '');
            } else {
                $value = post_param_string($parameter, '0');
            }
            if ($value != '') {
                $bparameters .= ' ' . $parameter . '="' . str_replace('"', '\"', $value) . '"';
            }
        }

        $comcode = '[block' . $bparameters . ']' . $block . '[/block]';

        $preview = comcode_to_tempcode($comcode);

        return array($preview, null);
    }
}
