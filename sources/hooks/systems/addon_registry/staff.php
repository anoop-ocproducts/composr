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
 * @package    staff
 */

/**
 * Hook class.
 */
class Hook_addon_registry_staff
{
    /**
     * Get a list of file permissions to set
     *
     * @return array File permissions to set
     */
    public function get_chmod_array()
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the description of the addon
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'Choose and display a selection of staff from the super-administator/super-moderator usergroups. This is useful for multi-site networks, where members with privileges may not be considered staff on all websites on the network.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_staff',
            'tut_staff_advice',
            'tut_users',
        );
    }

    /**
     * Get a mapping of dependency types
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(),
            'recommends' => array(),
            'conflicts_with' => array(),
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/site_meta/staff.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/site_meta/staff.png',
            'themes/default/images/icons/48x48/menu/site_meta/staff.png',
            'sources/hooks/systems/config/is_on_staff_filter.php',
            'sources/hooks/systems/config/is_on_sync_staff.php',
            'sources/hooks/systems/config/staff_text.php',
            'sources/hooks/systems/addon_registry/staff.php',
            'sources/hooks/systems/page_groupings/staff.php',
            'themes/default/templates/STAFF_SCREEN.tpl',
            'themes/default/templates/STAFF_EDIT_WRAPPER.tpl',
            'themes/default/templates/STAFF_ADMIN_SCREEN.tpl',
            'adminzone/pages/modules/admin_staff.php',
            'site/pages/modules/staff.php',
            'lang/EN/staff.ini',
            'sources/hooks/systems/cns_cpf_filter/staff_filter.php',
        );
    }

    /**
     * Get mapping between template names and the method of this class that can render a preview of them
     *
     * @return array The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/STAFF_SCREEN.tpl' => 'staff_screen',
            'templates/STAFF_EDIT_WRAPPER.tpl' => 'administrative__staff_admin_screen',
            'templates/STAFF_ADMIN_SCREEN.tpl' => 'administrative__staff_admin_screen'
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__staff_admin_screen()
    {
        $available = new Tempcode();
        foreach (placeholder_array() as $k => $v) {
            $available->attach(do_lorem_template('STAFF_EDIT_WRAPPER', array(
                'FORM' => placeholder_form(),
                'USERNAME' => lorem_word(),
            )));
        }

        return array(
            lorem_globalise(do_lorem_template('STAFF_ADMIN_SCREEN', array(
                'TITLE' => lorem_title(),
                'TEXT' => lorem_sentence_html(),
                'FORUM_STAFF' => $available,
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__staff_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('STAFF_SCREEN', array(
                'TITLE' => lorem_title(),
                'REAL_NAME' => lorem_phrase(),
                'ROLE' => lorem_phrase(),
                'ADDRESS' => lorem_phrase(),
                'USERNAME' => lorem_word(),
                'MEMBER_ID' => placeholder_id(),
                'PROFILE_URL' => placeholder_url(),
                'ALL_STAFF_URL' => placeholder_url(),
            )), null, '', true)
        );
    }
}
