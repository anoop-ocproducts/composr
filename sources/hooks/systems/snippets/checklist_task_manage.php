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
 * @package    core_adminzone_dashboard
 */

/**
 * Hook class.
 */
class Hook_snippet_checklist_task_manage
{
    /**
     * Run function for snippet hooks. Generates XHTML to insert into a page using AJAX.
     *
     * @return tempcode The snippet
     */
    public function run()
    {
        $type = get_param_string('type');

        if (!has_zone_access(get_member(), 'adminzone')) {
            return new Tempcode();
        }

        decache('main_staff_checklist');

        require_lang('staff_checklist');

        switch ($type) {
            case 'add':
                $recurinterval = get_param_integer('recurinterval', 0);

                $task_title = get_param_string('tasktitle', false, true);

                $id = $GLOBALS['SITE_DB']->query_insert('customtasks', array(
                    'tasktitle' => $task_title,
                    'datetimeadded' => time(),
                    'recurinterval' => $recurinterval,
                    'recurevery' => get_param_string('recurevery'),
                    'taskisdone' => null,
                ), true);

                require_code('notifications');
                $subject = do_lang('CT_NOTIFICATION_MAIL_SUBJECT', get_site_name(), $task_title);
                $mail = do_lang('CT_NOTIFICATION_MAIL', comcode_escape(get_site_name()), comcode_escape($task_title));
                dispatch_notification('checklist_task', null, $subject, $mail);

                return do_template('BLOCK_MAIN_STAFF_CHECKLIST_CUSTOM_TASK', array(
                    '_GUID' => 'e95228a3740dc7eda2d1b0ccc7d3d9d3',
                    'TASK_TITLE' => comcode_to_tempcode(get_param_string('tasktitle', false, true)),
                    'ADD_DATE' => display_time_period(time()),
                    'RECUR_INTERVAL' => ($recurinterval == 0) ? '' : integer_format($recurinterval),
                    'RECUR_EVERY' => get_param_string('recurevery'),
                    'TASK_DONE' => 'not_completed',
                    'ID' => strval($id),
                ));

            case 'delete':
                $GLOBALS['SITE_DB']->query_delete('customtasks', array(
                    'id' => get_param_integer('id'),
                ), '', 1);
                break;

            case 'mark_done':
                $GLOBALS['SITE_DB']->query_update('customtasks', array('taskisdone' => time()), array('id' => get_param_integer('id')), '', 1);
                break;

            case 'mark_undone':
                $GLOBALS['SITE_DB']->query_update('customtasks', array('taskisdone' => null), array('id' => get_param_integer('id')), '', 1);
                break;
        }

        return new Tempcode();
    }
}
