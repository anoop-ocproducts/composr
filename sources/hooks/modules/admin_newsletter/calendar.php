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
 * @package    calendar
 */

/**
 * Hook class.
 */
class Hook_whatsnew_calendar
{
    /**
     * Find selectable (filterable) categories.
     *
     * @param  TIME $updated_since The time that there must be entries found newer than
     * @return ?array Tuple of result details: HTML list of all types that can be choosed, title for selection list (null: disabled)
     */
    public function choose_categories($updated_since)
    {
        if (!addon_installed('calendar')) {
            return null;
        }

        require_lang('calendar');

        require_code('calendar');
        $cats = create_selection_list_event_types(null, $updated_since);
        return array($cats, do_lang('CALENDAR'));
    }

    /**
     * Run function for newsletter hooks.
     *
     * @param  TIME $cutoff_time The time that the entries found must be newer than
     * @param  LANGUAGE_NAME $lang The language the entries found must be in
     * @param  string $filter Category filter to apply
     * @return array Tuple of result details
     */
    public function run($cutoff_time, $lang, $filter)
    {
        if (!addon_installed('calendar')) {
            return array();
        }

        require_lang('calendar');

        $max = intval(get_option('max_newsletter_whatsnew'));

        $new = new Tempcode();

        require_code('selectcode');
        $or_list = selectcode_to_sqlfragment($filter, 'e_type');

        $privacy_join = '';
        $privacy_where = '';
        if (addon_installed('content_privacy')) {
            require_code('content_privacy');
            list($privacy_join, $privacy_where) = get_privacy_where_clause('event', 'e', $GLOBALS['FORUM_DRIVER']->get_guest_id());
        }

        $rows = $GLOBALS['SITE_DB']->query('SELECT * FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'calendar_events e ' . $privacy_join . ' WHERE e_add_date>' . strval($cutoff_time) . ' AND e_member_calendar IS NULL AND (' . $or_list . ')' . $privacy_where . ' ORDER BY e_add_date DESC', $max);
        if (count($rows) == $max) {
            return array();
        }

        foreach ($rows as $row) {
            $id = $row['id'];
            $_url = build_url(array('page' => 'calendar', 'type' => 'view', 'id' => $row['id']), get_module_zone('calendar'), null, false, false, true);
            $url = $_url->evaluate();
            $name = get_translated_text($row['e_title'], null, $lang);
            $description = get_translated_text($row['e_content'], null, $lang);
            $member_id = (is_guest($row['e_submitter'])) ? null : strval($row['e_submitter']);
            $new->attach(do_template('NEWSLETTER_WHATSNEW_RESOURCE_FCOMCODE', array('_GUID' => '654cafa75ec9f9b8e0e0fb666f28fb37', 'MEMBER_ID' => $member_id, 'URL' => $url, 'NAME' => $name, 'DESCRIPTION' => $description, 'CONTENT_TYPE' => 'event', 'CONTENT_ID' => strval($id)), null, false, null, '.txt', 'text'));

            handle_has_checked_recently($url); // We know it works, so mark it valid so as to not waste CPU checking within the generated Comcode
        }

        return array($new, do_lang('CALENDAR', '', '', '', $lang));
    }
}
