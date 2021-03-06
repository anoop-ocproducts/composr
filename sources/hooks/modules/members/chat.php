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
 * @package    chat
 */

/**
 * Hook class.
 */
class Hook_members_chat
{
    /**
     * Find member-related links to inject.
     *
     * @param  MEMBER $member_id The ID of the member we are getting link hooks for
     * @return array List of lists of tuples for results (by link section). Each tuple is: type,title,url
     */
    public function run($member_id)
    {
        if (!addon_installed('chat')) {
            return array();
        }

        $modules = array();
        if (has_actual_page_access(get_member(), 'chat', get_page_zone('chat'))) {
            if ((!is_guest()) && ($member_id != get_member())) {
                require_lang('chat');
                require_code('chat');
                require_code('users2');
                if (!$GLOBALS['FORUM_DRIVER']->is_staff($member_id)) {
                    if (!member_blocked($member_id)) {
                        $modules[] = array('contact', do_lang_tempcode('EXPLAINED_BLOCK_MEMBER'), build_url(array('page' => 'chat', 'type' => 'blocking_add', 'member_id' => $member_id, 'redirect' => get_self_url(true)), get_module_zone('chat')), 'menu/social/chat/member_blocking');
                        if (has_privilege(get_member(), 'start_im')) {
                            $modules[] = array('contact', do_lang_tempcode('START_IM'), build_url(array('page' => 'chat', 'type' => 'browse', 'enter_im' => $member_id), get_module_zone('chat')), 'menu/social/chat/chat');
                        }
                    } else {
                        $modules[] = array('contact', do_lang_tempcode('EXPLAINED_UNBLOCK_MEMBER'), build_url(array('page' => 'chat', 'type' => 'blocking_remove', 'member_id' => $member_id, 'redirect' => get_self_url(true)), get_module_zone('chat')), 'menu/social/chat/member_blocking');
                    }
                }
                if (!member_befriended($member_id)) {
                    $modules[] = array('contact', do_lang_tempcode('MAKE_FRIEND'), build_url(array('page' => 'chat', 'type' => 'friend_add', 'member_id' => $member_id, 'redirect' => get_self_url(true)), get_module_zone('chat')), 'tabs/member_account/friends');
                } else {
                    $modules[] = array('contact', do_lang_tempcode('DUMP_FRIEND'), build_url(array('page' => 'chat', 'type' => 'friend_remove', 'member_id' => $member_id, 'redirect' => get_self_url(true)), get_module_zone('chat')), 'tabs/member_account/friends');
                }
            }
        }
        return $modules;
    }
}
