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
 * @package    ecommerce
 */

/**
 * Find member subscriptions.
 *
 * @param  MEMBER $member_id The member.
 * @param  boolean $usergroup_subscriptions_only Whether to limit us to usergroup subscriptions.
 * @return array A list of subscriptions and subscription lifetime positions.
 */
function find_member_subscriptions($member_id, $usergroup_subscriptions_only = false)
{
    $subscriptions = array();

    // Subscription expiry date
    if ((get_forum_type() == 'cns') && (addon_installed('ecommerce'))) {
        $query = 'SELECT * FROM ' . get_table_prefix() . 'subscriptions WHERE s_member_id=' . strval($member_id) . ' AND (' . db_string_equal_to('s_state', 'active') . ' OR ' . db_string_equal_to('s_state', 'cancelled') . ') ORDER BY s_time';
        $_subscriptions = $GLOBALS['SITE_DB']->query($query);
        require_code('ecommerce');
        $GLOBALS['NO_DB_SCOPE_CHECK'] = true;
        $_subscriptions_non_recurring = $GLOBALS['SITE_DB']->query_select('f_group_member_timeouts', array('*'), array('member_id' => $member_id));
        $GLOBALS['NO_DB_SCOPE_CHECK'] = false;
        foreach ($_subscriptions_non_recurring as $sub) {
            $found_transaction = false;
            $subs_trans = $GLOBALS['SITE_DB']->query_select('transactions', array('*'), array('t_purchase_id' => $member_id, 't_status' => 'Completed'), 'ORDER BY t_time DESC');
            foreach ($subs_trans as $sub_trans) {
                $matches = array();
                if (preg_match('#^USERGROUP(\d+)$#', $sub_trans['t_type_code'], $matches) != 0) {
                    $sub_trans_2 = $GLOBALS['FORUM_DB']->query_select('f_usergroup_subs', array('*'), array('id' => intval($matches[1])), '', 1);
                    if (isset($sub_trans_2[0])) {
                        if ($sub_trans_2[0]['s_group_id'] == $sub['group_id']) {
                            $found_transaction = true;
                            break;
                        }
                    }
                }
            }

            if ($found_transaction) {
                $_subscriptions[] = array(
                    'id' => null,
                    's_type_code' => $sub_trans['t_type_code'],
                    's_member_id' => $member_id,
                    's_state' => ($sub['timeout'] > time()) ? 'cancelled' : 'active',
                    's_amount' => $sub_trans['t_amount'],
                    's_special' => '',
                    's_time' => $sub_trans['t_time'],
                    's_auto_fund_source' => '',
                    's_auto_fund_key' => '',
                    's_via' => $sub_trans['t_via'],
                    's_length' => $sub_trans_2[0]['s_length'],
                    's_length_units' => $sub_trans_2[0]['s_length_units'],
                );
            }
        }
        foreach ($_subscriptions as $sub) {
            // Load hook/etc details
            if (substr($sub['s_type_code'], 0, 9) == 'USERGROUP') {
                $usergroup_subscription_id = intval(substr($sub['s_type_code'], 9));
                static $usergroup_subscription_rows = null;
                if ($usergroup_subscription_rows === null) {
                    $usergroup_subscription_rows = list_to_map('id', $GLOBALS['FORUM_DB']->query_select('f_usergroup_subs', array('*')));
                }
                if (!array_key_exists($usergroup_subscription_id, $usergroup_subscription_rows)) {
                    continue;
                }
                $usergroup_subscription_row = $usergroup_subscription_rows[$usergroup_subscription_id];
                $usergroup_subscription_title = get_translated_text($usergroup_subscription_row['s_title']);
                $usergroup_subscription_description = get_translated_tempcode('f_usergroup_subs', $usergroup_subscription_row, 's_description');

                $usergroup_id = $usergroup_subscription_row['s_group_id'];
                $usergroup_rows = $GLOBALS['FORUM_DB']->query_select('f_groups', array('*'), array('id' => $usergroup_id), '', 1);
                if (!array_key_exists(0, $usergroup_rows)) {
                    continue;
                }
                $usergroup_row = $usergroup_rows[0];
                $usergroup_name = get_translated_text($usergroup_row['g_name'], $GLOBALS['FORUM_DB']);

                $item_name = $usergroup_subscription_title;
            } else {
                if ($usergroup_subscriptions_only) {
                    continue;
                }

                $usergroup_subscription_id = mixed();
                $usergroup_subscription_title = mixed();
                $usergroup_subscription_description = mixed();
                $usergroup_id = mixed();
                $usergroup_name = mixed();

                $type_code = $sub['s_type_code'];
                $object = find_product($type_code);
                if (is_null($object)) {
                    continue;
                }
                $products = $object->get_products(false, $type_code);
                $product_row = $products[$type_code];
                $item_name = $product_row[4];
            }

            $is_manual = ($sub['s_via'] == 'manual');

            $length = $sub['s_length'];
            $length_units = $sub['s_length_units']; // y-year, m-month, w-week, d-day

            // Work out term times
            $start_time = $sub['s_time'];
            $time_period_units = array('y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day');
            $term_end_time = strtotime('+' . strval($length) . ' ' . $time_period_units[$length_units], $start_time);
            $term_start_time = $start_time;
            if (!$is_manual) { // Non-manual ones have auto-renewal
                while ($term_end_time < time()) { // Must have auto-renewed
                    $term_end_time = strtotime('+' . strval($length) . ' ' . $time_period_units[$length_units], $term_end_time);
                    if ($term_end_time < time()) {
                        $term_start_time = $term_end_time;
                    }
                }
            }

            // Work out expiry
            $expiry_time = $term_end_time;
            if (!$is_manual) {
                if ($sub['s_state'] != 'cancelled') {
                    $expiry_time = null; // Auto-renewal --> no expiry
                }
            }
            $is_active = ($expiry_time === null || $expiry_time > time());

            // Details
            $subscription = array(
                'subscription_id' => $sub['id'],
                'type_code' => $sub['s_type_code'],
                'item_name' => $item_name,

                // These will be NULL for non-usergroup subscriptions
                'usergroup_subscription_id' => $usergroup_subscription_id,
                'usergroup_subscription_title' => $usergroup_subscription_title, // If not NULL, is same as $item_name -- but it's nice to be verbose/clear
                'usergroup_subscription_description' => $usergroup_subscription_description,
                'usergroup_id' => $usergroup_id,
                'usergroup_name' => $usergroup_name,

                'is_active' => $is_active,

                'length' => $length,
                'length_units' => $length_units,

                'amount' => $sub['s_amount'],

                'state' => $sub['s_state'],

                'via' => $sub['s_via'],
                'auto_fund_source' => $sub['s_auto_fund_source'],
                'auto_fund_key' => $sub['s_auto_fund_key'],

                'start_time' => $start_time,
                'term_start_time' => $term_start_time, // For non-recurring, this is the same as start_time
                'term_end_time' => $term_end_time, // For non-recurring, this is the same as expiry_time
                'expiry_time' => $expiry_time, // May be null: For recurring, expiry only happens on explicit cancellation or failed payment
            );
            if (($is_active) || (!isset($subscriptions[$sub['s_type_code']]))) { // We don't want to know multiple subscriptions to the same thing; prioritise active ones
                $subscriptions[$sub['s_type_code']] = $subscription;
            }
        }
    }

    return $subscriptions;
}

/**
 * Get template-ready parameters for displaying a subscription.
 *
 * @param  array $subscription Subscription details.
 * @return array Template-ready parameters.
 */
function prepare_templated_subscription($subscription)
{
    require_lang('ecommerce');

    return array(
        'SUBSCRIPTION_ID' => strval($subscription['subscription_id']),
        'TYPE_CODE' => $subscription['type_code'],
        'ITEM_NAME' => $subscription['item_name'],
        'USERGROUP_SUBSCRIPTION_TITLE' => $subscription['usergroup_subscription_title'], // May be NULL, if not a usergroup subscription
        'USERGROUP_SUBSCRIPTION_DESCRIPTION' => is_null($subscription['usergroup_subscription_description']) ? new Tempcode() : $subscription['usergroup_subscription_description'],
        'USERGROUP_SUBSCRIPTION_ID' => is_null($subscription['usergroup_subscription_id']) ? '' : strval($subscription['usergroup_subscription_id']),
        'USERGROUP_ID' => is_null($subscription['usergroup_id']) ? '' : strval($subscription['usergroup_id']),
        'USERGROUP_NAME' => $subscription['usergroup_name'],
        'LENGTH' => strval($subscription['length']),
        'LENGTH_UNITS' => $subscription['length_units'],
        'PER' => do_lang('_LENGTH_UNIT_' . $subscription['length_units'], integer_format($subscription['length'])),
        'AMOUNT' => $subscription['amount'],
        '_VIA' => $subscription['via'],
        'VIA' => do_lang_tempcode('PAYMENT_GATEWAY_' . $subscription['via']),
        '_STATE' => $subscription['state'],
        'STATE' => do_lang_tempcode('PAYMENT_STATE_' . $subscription['state']),
        '_START_TIME' => strval($subscription['start_time']),
        '_TERM_START_TIME' => strval($subscription['term_start_time']),
        '_TERM_END_TIME' => strval($subscription['term_end_time']),
        '_EXPIRY_TIME' => is_null($subscription['expiry_time']) ? '' : strval($subscription['expiry_time']),
        'START_TIME' => get_timezoned_date($subscription['start_time'], false, false, false, true),
        'TERM_START_TIME' => get_timezoned_date($subscription['term_start_time'], false, false, false, true),
        'TERM_END_TIME' => get_timezoned_date($subscription['term_end_time'], false, false, false, true),
        'EXPIRY_TIME' => is_null($subscription['expiry_time']) ? '' : get_timezoned_date($subscription['expiry_time'], false, false, false, true),
        'CANCEL_BUTTON' => ($subscription['state'] == 'active') ? make_cancel_button($subscription['auto_fund_key'], $subscription['via']) : new Tempcode(),
    );
}
