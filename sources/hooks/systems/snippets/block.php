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
class Hook_snippet_block
{
    /**
     * Run function for snippet hooks. Generates XHTML to insert into a page using AJAX.
     *
     * @return tempcode The snippet
     */
    public function run()
    {
        $sup = get_param_string('block_map_sup', '', true);
        $_map = get_param_string('block_map', false, true);
        if ($sup != '') {
            $_map .= ',' . $sup;
        }

        require_code('blocks');

        $map = block_params_str_to_arr($_map);

        if (!array_key_exists('block', $map)) {
            return new Tempcode();
        }

        $auth_key = get_param_integer('auth_key');

        // Check permissions
        $test = $GLOBALS['SITE_DB']->query_select_value_if_there('temp_block_permissions', 'p_block_constraints', array('p_session_id' => get_session_id(), 'id' => $auth_key));
        if ((is_null($test)) || (!block_signature_check(block_params_str_to_arr($test), $map))) {
            require_lang('permissions');
            return paragraph(do_lang_tempcode('ACCESS_DENIED__ACCESS_DENIED', escape_html($map['block'])));
        }

        // Cleanup
        if (mt_rand(0, 1000) == 123) {
            if (!$GLOBALS['SITE_DB']->table_is_locked('temp_block_permissions')) {
                $GLOBALS['SITE_DB']->query('DELETE FROM ' . get_table_prefix() . 'temp_block_permissions WHERE p_time<' . strval(time() - intval(60.0 * 60.0 * floatval(get_option('session_expiry_time')))));
            }
        }

        // We need to minimise the dependency stuff that comes out, we don't need any default values
        push_output_state(false, true);

        if (get_param_integer('raw', 0) == 1) {
            $map['raw'] = '1';
        }

        // Cleanup dependencies that will already have been handled
        global $CSSS, $JAVASCRIPTS;
        unset($CSSS['global']);
        unset($CSSS['no_cache']);
        unset($JAVASCRIPTS['global']);
        unset($JAVASCRIPTS['staff']);

        // And, go
        $out = new Tempcode();
        $_eval = do_block($map['block'], $map);
        $eval = $_eval->evaluate();
        $out->attach(symbol_tempcode('CSS_TEMPCODE'));
        $out->attach(symbol_tempcode('JS_TEMPCODE'));
        $out->attach($eval);
        $out->attach(symbol_tempcode('JS_TEMPCODE', array('footer')));
        return $out;
    }
}
