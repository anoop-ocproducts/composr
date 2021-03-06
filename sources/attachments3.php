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
 * @package    core_rich_media
 */

/*
Editing/deleting attachments.
*/

/**
 * Delete the specified attachment
 *
 * @param  AUTO_LINK $id The attachment ID to delete
 * @param  object $connection The database connection to use
 * @set    cms forum
 */
function _delete_attachment($id, $connection)
{
    $connection->query_delete('attachment_refs', array('a_id' => $id));

    // Get attachment details
    $_attachment_info = $connection->query_select('attachments', array('a_url', 'a_thumb_url'), array('id' => $id), '', 1);
    if (!array_key_exists(0, $_attachment_info)) {
        return; // Already gone
    }
    $attachment_info = $_attachment_info[0];

    // Delete url and thumb_url if local
    if ((url_is_local($attachment_info['a_url'])) && (substr($attachment_info['a_url'], 0, 19) == 'uploads/attachments')) {
        $url = rawurldecode($attachment_info['a_url']);
        @unlink(get_custom_file_base() . '/' . $url);
        sync_file($url);
        if (($attachment_info['a_thumb_url'] != '') && (strpos($attachment_info['a_thumb_url'], 'uploads/filedump/') === false)) {
            $thumb_url = rawurldecode($attachment_info['a_thumb_url']);
            @unlink(get_custom_file_base() . '/' . $thumb_url);
            sync_file($thumb_url);
        }
    }

    // Delete attachment
    $connection->query_delete('attachments', array('id' => $id), '', 1);
}

/**
 * Deletes all the attachments a given language code holds. Well, not quite! It deletes all references, and any attachments have through it, run out of references.
 *
 * @param  ID_TEXT $type The arbitrary type that the attached is for (e.g. download)
 * @param  ID_TEXT $id The ID in the set of the arbitrary types that the attached is for
 * @param  ?object $connection The database connection to use (null: standard site connection)
 */
function delete_comcode_attachments($type, $id, $connection = null)
{
    if (is_null($connection)) {
        $connection = $GLOBALS['SITE_DB'];
    }

    require_lang('comcode');

    $refs = $connection->query_select('attachment_refs', array('a_id', 'id'), array('r_referer_type' => $type, 'r_referer_id' => $id));
    $connection->query_delete('attachment_refs', array('r_referer_type' => $type, 'r_referer_id' => $id));
    foreach ($refs as $ref) {
        // Was that the last reference to this attachment? (if so -- delete attachment)
        $test = $connection->query_select_value_if_there('attachment_refs', 'id', array('a_id' => $ref['a_id']));
        if (is_null($test)) {
            _delete_attachment($ref['a_id'], $connection);
        }
    }
}

/**
 * This function is the same as delete_comcode_attachments, except that it deletes the language code as well.
 *
 * @param  mixed $lang_id The language ID
 * @param  ID_TEXT $type The arbitrary type that the attached is for (e.g. download)
 * @param  ID_TEXT $id The ID in the set of the arbitrary types that the attached is for
 * @param  ?object $connection The database connection to use (null: standard site connection)
 */
function delete_lang_comcode_attachments($lang_id, $type, $id, $connection = null)
{
    if (is_null($connection)) {
        $connection = $GLOBALS['SITE_DB'];
    }

    delete_comcode_attachments($type, $id, $connection);

    if (multi_lang_content()) {
        $connection->query_delete('translate', array('id' => $lang_id), '', 1);
    }
}

/**
 * Update a language code, in such a way that new attachments are created if they were specified.
 *
 * @param  ID_TEXT $field_name The field name
 * @param  mixed $lang_id The language ID
 * @param  LONG_TEXT $text The new text
 * @param  ID_TEXT $type The arbitrary type that the attached is for (e.g. download)
 * @param  ID_TEXT $id The ID in the set of the arbitrary types that the attached is for
 * @param  ?object $connection The database connection to use (null: standard site connection)
 * @param  boolean $backup_string Whether to backup the language string before changing it
 * @param  ?MEMBER $for_member The member that owns the content this is for (null: current member)
 * @return array The language ID save fields
 */
function update_lang_comcode_attachments($field_name, $lang_id, $text, $type, $id, $connection = null, $backup_string = false, $for_member = null)
{
    if ($lang_id === 0) {
        return insert_lang_comcode_attachments($field_name, 3, $text, $type, $id, $connection, false, $for_member);
    }

    if ($text === STRING_MAGIC_NULL) {
        return $lang_id;
    }

    if (is_null($connection)) {
        $connection = $GLOBALS['SITE_DB'];
    }

    require_lang('comcode');

    _check_attachment_count();

    if (($backup_string) && (multi_lang_content())) {
        if (multi_lang()) {
            $current = $connection->query_select('translate', array('*'), array('id' => $lang_id, 'language' => user_lang()));
            if (!array_key_exists(0, $current)) {
                $current = $connection->query_select('translate', array('*'), array('id' => $lang_id));
            }
        } else {
            $current = array(array(
                                 'language' => user_lang(),
                                 'text_original' => $lang_id,
                                 'broken' => 0,
                             ));
        }

        $connection->query_insert('translate_history', array(
            'lang_id' => $lang_id,
            'language' => $current[0]['language'],
            'text_original' => $current[0]['text_original'],
            'broken' => $current[0]['broken'],
            'action_member' => get_member(),
            'action_time' => time()
        ));
    }

    $member = (function_exists('get_member')) ? get_member() : $GLOBALS['FORUM_DRIVER']->get_guest_id();

    if ((is_null($for_member)) || ($GLOBALS['FORUM_DRIVER']->get_username($for_member) === null)) {
        $for_member = $member;
    }

    /*
    We set the Comcode user to the editing user (not the content owner) if the editing user does not have full HTML/Dangerous-Comcode privileges.
    The Comcode user is set to the content owner if the editing user does have those privileges (which is the idealised, consistent state).
    This is necessary as editing admin's content shouldn't let you write content with admin's privileges, even if you have privilege to edit their content
     - yet also, if the source_user is changed, when admin edits it has to change back again.
    */
    if (((cms_admirecookie('use_wysiwyg', '1') == '0') && (get_value('edit_with_my_comcode_perms') === '1')) || (!has_privilege($member, 'allow_html')) || (!has_privilege($member, 'use_very_dangerous_comcode'))) {
        $source_user = $member;
    } else {
        $source_user = $for_member; // Reset to latest submitter for main record
    }

    $_info = do_comcode_attachments($text, $type, $id, false, $connection, null, $source_user);
    $text_parsed = '';//Actually we'll let it regenerate with the correct permissions ($member, not $for_member) $_info['tempcode']->to_assembly();

    if (multi_lang_content()) {
        $remap = array(
            'text_original' => $_info['comcode'],
            'text_parsed' => $text_parsed,
            'source_user' => $source_user,
        );

        $test = $connection->query_select_value_if_there('translate', 'text_original', array('id' => $id, 'language' => user_lang()));
        if (!is_null($test)) { // Good, we save into our own language, as we have a translation for the lang entry setup properly
            $connection->query_update('translate', $remap, array('id' => $lang_id, 'language' => user_lang()));
        } else { // Darn, we'll have to save over whatever we did load from
            $connection->query_update('translate', $remap, array('id' => $lang_id));
        }
    } else {
        $ret = array();
        $ret[$field_name] = $_info['comcode'];
        $ret[$field_name . '__text_parsed'] = $text_parsed;
        $ret[$field_name . '__source_user'] = $source_user;
        return $ret;
    }

    return array(
        $field_name => $lang_id,
    );
}
