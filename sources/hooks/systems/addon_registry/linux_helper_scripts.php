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
 * @package    linux_helper_scripts
 */

/**
 * Hook class.
 */
class Hook_addon_registry_linux_helper_scripts
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
        return 'Bash shell scripts to help configure permissions on Linux/Unix servers.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_adv_installation',
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
        return 'themes/default/images/icons/48x48/menu/_generic_admin/component.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'sources/hooks/systems/addon_registry/linux_helper_scripts.php',

            // The following are sh shell scripts
            'decache.sh',
            'fixperms.sh',
            'themechanges.sh',
            'recentchanges.sh',
            'db_init.sh',
            'db_export.sh',
            'db_import.sh',

            // The following are PHP scripts designed to be callable directly (not all bundled with the main Composr)
            // sources/critical_errors.php (works to monitor for logged critical errors, and email them)
            // data/commandr.php (command line tunnel into Commandr, VERY useful)
            // _tests/codechecker/code_quality.php (Code Quality Checker)
            // _tests/codechecker/phpdoc_parser.php (Code Quality Checker function signature parsing)
            // data_custom/compile_in_includes.php (Compile Composr overrides against originals, for slight performance improvement)

            // Various tools are also built into the Admin Zone menus

            // Various tools are built into the upgrader
            //  E.g. detecting alien files
            //  E.g. detecting missing files
            //  E.g. detecting corrupt files
            //  E.g. clearing the caches (without having to run the full Composr)
            //  E.g. detecting missing permissions
            //  E.g. deleting files accidentally uploaded from non-installed addons
        );
    }
}
