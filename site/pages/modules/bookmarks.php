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
 * @package    bookmarks
 */

/**
 * Module page class.
 */
class Module_bookmarks
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
        $info['locked'] = true;
        return $info;
    }

    /**
     * Uninstall the module.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('bookmarks');
    }

    /**
     * Install the module.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install($upgrade_from = null, $upgrade_from_hack = null)
    {
        $GLOBALS['SITE_DB']->create_table('bookmarks', array(
            'id' => '*AUTO',
            'b_owner' => 'MEMBER',
            'b_folder' => 'SHORT_TEXT',
            'b_title' => 'SHORT_TEXT',
            'b_page_link' => 'SHORT_TEXT',
        ));
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
        if ($check_perms && is_guest($member_id)) {
            return array();
        }

        $cnt = $GLOBALS['SITE_DB']->query_select_value('bookmarks', 'COUNT(*)', array('b_owner' => is_null($member_id) ? get_member() : $member_id));

        $ret = array();
        if ($cnt != 0) {
            $ret += array(
                'browse' => array('MANAGE_BOOKMARKS', 'menu/site_meta/bookmarks'),
            );
        }
        $ret += array(
            'add' => array('ADD_BOOKMARK', 'menu/_generic_admin/add_one'),
        );
        return $ret;
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

        require_lang('bookmarks');

        if ($type == 'browse' || $type == '_manage') {
            $this->title = get_screen_title('MANAGE_BOOKMARKS');
        }

        if ($type == 'add' || $type == '_add') {
            $this->title = get_screen_title('ADD_BOOKMARK');
        }

        if ($type == '_edit') {
            $this->title = get_screen_title('EDIT_BOOKMARK');
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
        require_code('bookmarks');
        require_css('bookmarks');

        if (is_guest()) {
            access_denied('NOT_AS_GUEST');
        }

        // Decide what we're doing
        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->manage_bookmarks();
        }
        if ($type == '_manage') {
            return $this->_manage_bookmarks();
        }
        if ($type == 'add') {
            return $this->add();
        }
        if ($type == '_add') {
            return $this->_add();
        }
        if ($type == '_edit') {
            return $this->_edit_bookmark();
        }

        return new Tempcode();
    }

    /**
     * The UI to manage bookmarks.
     *
     * @return tempcode The UI
     */
    public function manage_bookmarks()
    {
        require_code('form_templates');
        require_lang('zones');

        $fields = new Tempcode();
        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '2efc21de71434c715f920c7dbd14e687', 'TITLE' => do_lang_tempcode('MOVE'))));
        $rows = $GLOBALS['SITE_DB']->query_select('bookmarks', array('DISTINCT b_folder'), array('b_owner' => get_member()), 'ORDER BY b_folder');
        $list = form_input_list_entry('', false, do_lang_tempcode('NA_EM'));
        $list->attach(form_input_list_entry('!', false, do_lang_tempcode('ROOT_EM')));
        foreach ($rows as $row) {
            if ($row['b_folder'] != '') {
                $list->attach(form_input_list_entry($row['b_folder']));
            }
        }

        $set_name = 'choose_folder';
        $required = true;
        $set_title = do_lang_tempcode('BOOKMARK_FOLDER');
        $field_set = alternate_fields_set__start($set_name);

        $field_set->attach(form_input_list(do_lang_tempcode('EXISTING'), do_lang_tempcode('DESCRIPTION_OLD_BOOKMARK_FOLDER'), 'folder', $list, null, false, false));

        $field_set->attach(form_input_line(do_lang_tempcode('NEW'), do_lang_tempcode('DESCRIPTION_NEW_BOOKMARK_FOLDER'), 'folder_new', '', false));

        $fields->attach(alternate_fields_set__end($set_name, $set_title, '', $field_set, $required));

        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => 'ec1bb050b1a6b31a8c2774c6994f3fb2', 'TITLE' => do_lang_tempcode('ACTIONS'))));
        $fields->attach(form_input_tick(do_lang_tempcode('DELETE'), do_lang_tempcode('DESCRIPTION_DELETE'), 'delete', false));
        $post_url = build_url(array('page' => '_SELF', 'type' => '_manage'), '_SELF');
        $form = do_template('FORM', array('_GUID' => '5d9a17c5be18674991c3b17a4a4e7bfe', 'HIDDEN' => '', 'FIELDS' => $fields, 'TEXT' => '', 'URL' => $post_url, 'SUBMIT_ICON' => 'buttons__proceed', 'SUBMIT_NAME' => do_lang_tempcode('MOVE_OR_DELETE_BOOKMARKS')));

        $bookmarks = array();
        $_bookmarks = $GLOBALS['SITE_DB']->query_select('bookmarks', array('*'), array('b_owner' => get_member()), 'ORDER BY b_folder');
        foreach ($_bookmarks as $bookmark) {
            $bookmarks[] = array('ID' => strval($bookmark['id']), 'CAPTION' => $bookmark['b_title'], 'FOLDER' => $bookmark['b_folder'], 'PAGE_LINK' => $bookmark['b_page_link']);
        }

        return do_template('BOOKMARKS_SCREEN', array('_GUID' => '685f020d6407543271ce99b5775bb357', 'TITLE' => $this->title, 'FORM_URL' => $post_url, 'FORM' => $form, 'BOOKMARKS' => $bookmarks));
    }

    /**
     * The actualiser to manage bookmarks.
     *
     * @return tempcode The UI
     */
    public function _manage_bookmarks()
    {
        $bookmarks = $GLOBALS['SITE_DB']->query_select('bookmarks', array('id'), array('b_owner' => get_member()));
        if (post_param_string('delete', '') != '') { // A delete
            foreach ($bookmarks as $bookmark) {
                if (get_param_integer('bookmark_' . $bookmark['id'], 0) == 1) {
                    $GLOBALS['SITE_DB']->query_delete('bookmarks', array('id' => $bookmark['id']), '', 1);
                }
            }
        } else { // Otherwise it's a move
            $folder = post_param_string('folder_new', '');
            if ($folder == '') {
                $folder = post_param_string('folder');
            }
            if ($folder == '!') {
                $folder = '';
            }

            foreach ($bookmarks as $bookmark) {
                if (get_param_integer('bookmark_' . $bookmark['id'], 0) == 1) {
                    $GLOBALS['SITE_DB']->query_update('bookmarks', array('b_folder' => $folder), array('id' => $bookmark['id']), '', 1);
                }
            }
        }

        $url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }

    /**
     * The UI to add a bookmark.
     *
     * @return tempcode The UI
     */
    public function add()
    {
        require_code('form_templates');

        url_default_parameters__enable();
        $ret = add_bookmark_form(build_url(array('page' => '_SELF', 'type' => '_add', 'do_redirect' => (get_param_integer('no_redirect', 0) == 0) ? '1' : '0'), '_SELF'));
        url_default_parameters__disable();
        return $ret;
    }

    /**
     * The actualiser to add a bookmark.
     *
     * @return tempcode The UI
     */
    public function _add()
    {
        $folder = post_param_string('folder_new', '');
        if ($folder == '') {
            $folder = post_param_string('folder');
        }
        if ($folder == '!') {
            $folder = '';
        }

        add_bookmark(get_member(), $folder, post_param_string('title'), post_param_string('page_link'));

        if (get_param_integer('do_redirect') == 1) {
            $url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');
            return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
        } else {
            return inform_screen($this->title, do_lang_tempcode('SUCCESS'));
        }
    }

    /**
     * The actualiser to edit a bookmark.
     *
     * @return tempcode The UI
     */
    public function _edit_bookmark()
    {
        $id = get_param_integer('id');

        if (post_param_string('delete', null) !== null) { // A delete
            $member = get_member();
            delete_bookmark($id, $member);
        } else {
            $caption = post_param_string('caption');
            $page_link = post_param_string('page_link');
            $member = get_member();
            edit_bookmark($id, $member, $caption, $page_link);
        }

        $url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }
}
