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
 * @package    core_cns
 */

/**
 * Hook class.
 */
class Hook_content_meta_aware_group
{
    /**
     * Get content type details. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
     *
     * @param  ?ID_TEXT $zone The zone to link through to (null: autodetect).
     * @return ?array Map of award content-type info (null: disabled).
     */
    public function info($zone = null)
    {
        if (get_forum_type() != 'cns') {
            return null;
        }

        return array(
            'supports_custom_fields' => true,

            'content_type_label' => 'GROUP',

            'connection' => $GLOBALS['FORUM_DB'],
            'table' => 'f_groups',
            'id_field' => 'id',
            'id_field_numeric' => true,
            'parent_category_field' => null,
            'parent_category_meta_aware_type' => null,
            'is_category' => false,
            'is_entry' => true,
            'category_type' => null, // For category permissions
            'parent_spec__table_name' => null,
            'parent_spec__parent_name' => null,
            'parent_spec__field_name' => null,
            'category_field' => null, // For category permissions
            'category_is_string' => false,

            'title_field' => 'g_name',
            'title_field_dereference' => true,
            'description_field' => null,
            //'thumb_field' => 'g_rank_image',  Looks ugly, often missing and random sizes
            //'thumb_field_is_theme_image' => true,

            'view_page_link_pattern' => '_SEARCH:groups:view:_WILD',
            'edit_page_link_pattern' => 'adminzone:admin_cns_groups:_edit:_WILD',
            'view_category_page_link_pattern' => null,
            'add_url' => '',
            'archive_url' => ((!is_null($zone)) ? $zone : get_module_zone('groups')) . ':groups',

            'support_url_monikers' => true,

            'views_field' => null,
            'submitter_field' => 'g_group_leader',
            'add_time_field' => null,
            'edit_time_field' => null,
            'date_field' => null,
            'validated_field' => null,

            'seo_type_code' => null,

            'feedback_type_code' => null,

            'permissions_type_code' => null, // NULL if has no permissions

            'search_hook' => null,

            'addon_name' => 'core_cns',

            'cms_page' => 'groups',
            'module' => 'groups',

            'commandr_filesystem_hook' => 'groups',
            'commandr_filesystem__is_folder' => true,

            'rss_hook' => null,

            'actionlog_regexp' => '\w+_GROUP',
        );
    }

    /**
     * Run function for content hooks. Renders a content box for an award/randomisation.
     *
     * @param  array $row The database row for the content
     * @param  ID_TEXT $zone The zone to display in
     * @param  boolean $give_context Whether to include context (i.e. say WHAT this is, not just show the actual content)
     * @param  boolean $include_breadcrumbs Whether to include breadcrumbs (if there are any)
     * @param  ?ID_TEXT $root Virtual root to use (null: none)
     * @param  boolean $attach_to_url_filter Whether to copy through any filter parameters in the URL, under the basis that they are associated with what this box is browsing
     * @param  ID_TEXT $guid Overridden GUID to send to templates (blank: none)
     * @return tempcode Results
     */
    public function run($row, $zone, $give_context = true, $include_breadcrumbs = true, $root = null, $attach_to_url_filter = false, $guid = '')
    {
        require_code('cns_groups');

        return render_group_box($row, $zone, $give_context);
    }
}
