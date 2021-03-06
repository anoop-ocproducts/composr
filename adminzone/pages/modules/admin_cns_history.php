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
 * @package    cns_forum
 */

/**
 * Module page class.
 */
class Module_admin_cns_history
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions.
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user).
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return NULL to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        if (get_forum_type() != 'cns') {
            return null;
        }

        return array(
            'browse' => array('POST_HISTORY', 'buttons/history'),
        );
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know meta-data for <head> before we start streaming output.
     *
     * @return ?tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        $type = get_param_string('type', 'browse');

        require_lang('cns');
        require_css('cns_admin');

        if ($type == 'browse') {
            $member_id = get_param_integer('member_id', -1);
            $post_id = get_param_integer('post_id', -1);
            $topic_id = get_param_integer('topic_id', -1);

            if ($member_id != -1) {
                $this->title = get_screen_title('POST_HISTORY_MEMBER');
            } elseif ($post_id != -1) {
                $this->title = get_screen_title('POST_HISTORY_POST');
            } elseif ($topic_id != -1) {
                $this->title = get_screen_title('POST_HISTORY_TOPIC');
            } else {
                $this->title = get_screen_title('POST_HISTORY');
            }
        }

        if ($type == 'restore') {
            $this->title = get_screen_title('POST_HISTORY');
        }

        if ($type == 'revert') {
            $this->title = get_screen_title('POST_HISTORY');
        }

        if ($type == 'delete') {
            $this->title = get_screen_title('POST_HISTORY');
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return tempcode The result of execution.
     */
    public function run()
    {
        if (get_forum_type() != 'cns') {
            warn_exit(do_lang_tempcode('NO_CNS'));
        } else {
            cns_require_all_forum_stuff();
        }
        require_css('cns');

        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->gui();
        }
        if ($type == 'restore') {
            return $this->restore();
        }
        if ($type == 'revert') {
            return $this->revert();
        }
        if ($type == 'delete') {
            return $this->delete();
        }

        return new Tempcode();
    }

    /**
     * The UI to show the edit/delete history of posts (exact history shown depending on GET parameters).
     *
     * @return tempcode The UI
     */
    public function gui()
    {
        check_privilege('view_content_history');

        $member_id = get_param_integer('member_id', -1);
        $post_id = get_param_integer('post_id', -1);
        $topic_id = get_param_integer('topic_id', -1);

        $where = array();
        if ($member_id != -1) {
            $where['h_owner_member_id'] = $member_id;
        }
        if ($post_id != -1) {
            $where['h_post_id'] = $post_id;
        }
        if ($topic_id != -1) {
            $where['h_topic_id'] = $topic_id;
        }
        if (count($where) == 0) {
            $where = null;
        }

        $start = get_param_integer('start', 0);
        $max = get_param_integer('max', 40);

        $max_rows = $GLOBALS['FORUM_DB']->query_select_value('f_post_history', 'COUNT(*)', $where);

        $posts = $GLOBALS['FORUM_DB']->query_select('f_post_history', array('*'), $where, 'ORDER BY h_action_date_and_time DESC', $max, $start);
        $content = new Tempcode();
        foreach ($posts as $post) {
            $create_date_and_time = get_timezoned_date($post['h_create_date_and_time']);
            $action_date_and_time = get_timezoned_date($post['h_action_date_and_time']);
            $owner_member = $GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($post['h_owner_member_id']);
            $alterer_member = $GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($post['h_alterer_member_id']);

            // Action/Link
            $topic_exists = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_topics', 'id', array('id' => $post['h_topic_id']));
            if (!is_null($topic_exists)) {
                if (($post['h_action'] == 'EDIT_POST')) {
                    $relates_to = build_url(array('page' => 'topicview', 'type' => 'findpost', 'id' => $post['h_post_id']), get_module_zone('topicview'));
                    $relates_to->attach('#post_' . strval($post['h_post_id']));
                    $relates_text = do_lang_tempcode('VIEW_POST');
                    $relates_tooltip = 'post#' . strval($post['h_post_id']);
                } elseif (($post['h_action'] == 'DELETE_POST')) {
                    $relates_to = build_url(array('page' => 'topicview', 'id' => $post['h_topic_id']), get_module_zone('topicview'));
                    $relates_text = do_lang_tempcode('VIEW_TOPIC');
                    $relates_tooltip = 'topic#' . strval($post['h_topic_id']);
                }
                $link = hyperlink($relates_to, $relates_text, false, true, $relates_tooltip);
            } else {
                $link = new Tempcode();
            }
            $action = do_lang($post['h_action']);

            // Buttons
            $buttons = new Tempcode();
            if (has_privilege(get_member(), 'delete_content_history')) { // Delete permanently
                $url = build_url(array('page' => '_SELF', 'type' => 'delete', 'h_id' => $post['id']), '_SELF', null, true);
                $buttons->attach(do_template('BUTTON_SCREEN_ITEM', array('_GUID' => '11c9f9ef4a646493544cb29778134960', 'IMMEDIATE' => true, 'URL' => $url, 'IMG' => 'menu___generic_admin__delete', 'FULL_TITLE' => do_lang_tempcode('DELETE_HISTORY_POST'), 'TITLE' => do_lang_tempcode('DELETE_HISTORY_POST'))));
            }
            if ((has_privilege(get_member(), 'restore_content_history')) && (!is_null($topic_exists)) && ($post['h_action'] == 'DELETE_POST')) { // Restore
                $url = build_url(array('page' => '_SELF', 'type' => 'restore', 'h_id' => $post['id']), '_SELF', null, true);
                $buttons->attach(do_template('BUTTON_SCREEN_ITEM', array('_GUID' => '49623e00065f488bb27097bb722232dc', 'IMMEDIATE' => true, 'URL' => $url, 'IMG' => 'buttons__restore', 'FULL_TITLE' => do_lang_tempcode('RESTORE_HISTORY_POST'), 'TITLE' => do_lang_tempcode('RESTORE_HISTORY_POST'))));
            }
            if ((has_privilege(get_member(), 'restore_content_history')) && (!is_null($topic_exists)) && ($post['h_action'] == 'EDIT_POST')) { // Restore
                $url = build_url(array('page' => '_SELF', 'type' => 'revert', 'h_id' => $post['id']), '_SELF', null, true);
                $buttons->attach(do_template('BUTTON_SCREEN_ITEM', array('_GUID' => '3f41d4d399676972c01ebb14f6ee56db', 'IMMEDIATE' => true, 'URL' => $url, 'IMG' => 'buttons__choose', 'FULL_TITLE' => do_lang_tempcode('REVERT_HISTORY_POST'), 'TITLE' => do_lang_tempcode('REVERT_HISTORY_POST'))));
            }

            $content->attach(do_template('CNS_HISTORY_POST', array(
                '_GUID' => 'f3512689a8b3fcf4215f63f9f340cdac',
                'LABEL' => do_lang_tempcode('BEFORE_ACTION'),
                'LINK' => $link,
                'BUTTONS' => $buttons,
                'ACTION' => $action,
                'ACTION_DATE_AND_TIME' => $action_date_and_time,
                'ACTION_DATE_AND_TIME_RAW' => strval($post['h_action_date_and_time']),
                'CREATE_DATE_AND_TIME_RAW' => strval($post['h_create_date_and_time']),
                'CREATE_DATE_AND_TIME' => $create_date_and_time,
                'OWNER_MEMBER' => $owner_member,
                'ALTERER_MEMBER' => $alterer_member,
                'BEFORE' => $post['h_before'],
            )));
        }
        if ((count($posts) != 0) && ($post_id != -1)) {
            $original_post = $GLOBALS['FORUM_DB']->query_select('f_posts', array('*'), array('id' => $post_id), '', 1);
            if (array_key_exists(0, $original_post)) {
                $action = do_lang('CURRENT');
                $link = hyperlink(build_url(array('page' => 'topicview', 'type' => 'findpost', 'id' => $post_id), get_module_zone('topicview')), do_lang_tempcode('VIEW_POST'), false, false);
                $buttons = new Tempcode();
                $action_date_and_time = '';
                $action_date_and_time_raw = '';
                $owner_member = $GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($original_post[0]['p_poster']);
                $alterer_member = new Tempcode();
                $before = get_translated_text($original_post[0]['p_post'], $GLOBALS['FORUM_DB']);
                $create_date_and_time = get_timezoned_date($original_post[0]['p_time']);
                $create_date_and_time_raw = strval($original_post[0]['p_time']);
                $content2 = do_template('CNS_HISTORY_POST', array(
                    '_GUID' => 'a3512689a8b3fcf4215f63f9f340cdac',
                    'LABEL' => do_lang_tempcode('CURRENT_STATUS'),
                    'LINK' => $link,
                    'BUTTONS' => $buttons,
                    'ACTION' => $action,
                    'ACTION_DATE_AND_TIME' => $action_date_and_time,
                    'ACTION_DATE_AND_TIME_RAW' => $action_date_and_time_raw,
                    'CREATE_DATE_AND_TIME_RAW' => $create_date_and_time_raw,
                    'CREATE_DATE_AND_TIME' => $create_date_and_time,
                    'OWNER_MEMBER' => $owner_member,
                    'ALTERER_MEMBER' => $alterer_member,
                    'BEFORE' => $before,
                ));
                $content2->attach($content);
                $content = $content2;
            }
        }

        require_code('templates_pagination');
        $pagination = pagination(do_lang_tempcode('POST_HISTORY'), $start, 'start', $max, 'max', $max_rows);

        $tpl = do_template('CNS_HISTORY_SCREEN', array('_GUID' => '7dd45ce985fc7222771368336c3f19e4', 'PAGINATION' => $pagination, 'TITLE' => $this->title, 'CONTENT' => $content));
        require_code('templates_internalise_screen');
        return internalise_own_screen($tpl);
    }

    /**
     * The actualiser to restore a deleted post.
     *
     * @return tempcode The UI
     */
    public function restore()
    {
        check_privilege('restore_content_history');

        $id = get_param_integer('h_id');
        $post = $GLOBALS['FORUM_DB']->query_select('f_post_history', array('*'), array('id' => $id), '', 1);
        if (!array_key_exists(0, $post)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
        }
        $post = $post[0];
        require_code('cns_posts_action');
        require_code('cns_posts_action2');
        $owner_username = $GLOBALS['FORUM_DRIVER']->get_username($post['h_owner_member_id']);
        if (is_null($owner_username)) {
            $owner_username = do_lang('UNKNOWN');
        }
        cns_make_post($post['h_topic_id'], '', $post['h_before'], 0, false, 1, 0, $owner_username, null, $post['h_create_date_and_time'], $post['h_owner_member_id'], null, time(), get_member(), false, true, null, true, '', 0, null, false, false, false, false, null, false);

        $url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF', null, true);
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }

    /**
     * The actualiser to revert an edited post.
     *
     * @return tempcode The UI
     */
    public function revert()
    {
        check_privilege('restore_content_history');

        $id = get_param_integer('h_id');
        $post = $GLOBALS['FORUM_DB']->query_select('f_post_history', array('*'), array('id' => $id), '', 1);
        if (!array_key_exists(0, $post)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
        }
        $post = $post[0];
        $post2 = $GLOBALS['FORUM_DB']->query_select('f_posts', array('*'), array('id' => $post['h_post_id']), '', 1);
        if (!array_key_exists(0, $post2)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
        }
        $post2 = $post2[0];
        require_code('cns_posts_action');
        require_code('cns_posts_action2');
        require_code('cns_posts_action3');
        cns_edit_post($post['h_post_id'], $post2['p_validated'], $post2['p_title'], $post['h_before'], 0, $post2['p_is_emphasised'], $post2['p_intended_solely_for'], false, false, do_lang('REVERT_HISTORY_POST'));

        $url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF', null, true);
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }

    /**
     * The actualiser to delete some element from the post history.
     *
     * @return tempcode The UI
     */
    public function delete()
    {
        check_privilege('delete_content_history');

        $GLOBALS['FORUM_DB']->query_delete('f_post_history', array('id' => get_param_integer('h_id')), '', 1);

        $url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF', null, true);
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }
}
