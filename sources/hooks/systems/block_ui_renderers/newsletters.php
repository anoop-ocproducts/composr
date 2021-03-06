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
 * @package    newsletter
 */

/**
 * Hook class.
 */
class Hook_block_ui_renderers_newsletters
{
    /**
     * See if a particular block parameter's UI input can be rendered by this.
     *
     * @param  ID_TEXT $block The block
     * @param  ID_TEXT $parameter The parameter of the block
     * @param  boolean $has_default Whether there is a default value for the field, due to this being an edit
     * @param  string $default Default value for field
     * @param  tempcode $description Field description
     * @return ?tempcode Rendered field (null: not handled).
     */
    public function render_block_ui($block, $parameter, $has_default, $default, $description)
    {
        if (($parameter == 'param') && (in_array($block, array('main_newsletter_signup')))) { // newsletter list
            $list = new Tempcode();
            $rows = $GLOBALS['SITE_DB']->query_select('newsletters', array('id', 'title'));
            foreach ($rows as $newsletter) {
                $list->attach(form_input_list_entry(strval($newsletter['id']), $has_default && strval($newsletter['id']) == $default, get_translated_text($newsletter['title'])));
            }
            return form_input_list(titleify($parameter), escape_html($description), $parameter, $list, null, false, false);
        }
        return null;
    }
}
