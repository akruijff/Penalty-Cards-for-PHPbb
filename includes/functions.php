<?php /**
 *
 * Penalty Cards. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Alex de Kruijff, http://alex.kruijff.org
 * @license Original BSD licence
 *
 */

function request_number_variable($key) {
	global $request;
	$value = $request->variable($key, 0);
	if ($value == 0)
		trigger_error('FORUM_INVALID', E_USER_WARNING);
	return $value;
}

function request_username($key) {
	global $request;
	$username = $request->variable($key, '');
	$user_id = fetch_user_id($username);
	if ($user_id == 0)
		trigger_error('FORUM_INVALID', E_USER_WARNING);
	return $username;
}

function can_issue_yellow($event, $cards) {
	global $auth, $config, $user;
	return $auth->acl_get('m_akruijff_penalty_cards_warn')
		&& $event['poster_id'] != $user->data['user_id']
		&& $event['poster_id'] != ANONYMOUS
		&& $cards < $config['akruijff_penalty_cards_max_yellow_cards'];
}

function can_issue_red($event) {
	global $auth, $config, $user;
	return $auth->acl_get('m_akruijff_penalty_cards_ban')
		&& $event['poster_id'] != $user->data['user_id']
		&& $event['poster_id'] != ANONYMOUS;
}

function is_card_issued($post_id) {
	$row = fetch_post_row($post_id);
	return !empty($row['card_id']);
}

function delete_sessions($card) {
	global $db;
	$db->sql_query('DELETE FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = ' . $card['user_id']);
}

function get_card_class($ban_id) {
	return $ban_id ? 'red-card-issued' : 'yellow-card-issued';
}

function get_card_text($ban_id) {
	return $ban_id == NULL ? YELLOW_CARD : RED_CARD;
}

function get_index_url($card_id) {
	return generate_board_url() . "/app.php/akruijff/penalty_cards/$card_id/";
}

function get_post_url($post_id) {
	return generate_board_url() . "/viewtopic.php?p=$post_id#p$post_id";
}

function get_user_url($user_id) {
	return generate_board_url() . "/memberlist.php?u=$user_id";
}

function translate_for_user($key, $user_row) {
	global $user;
	return $user_row['user_lang'] != $user->lang_name
		? transalte_for_other_user($key, $user_row)
		: $user->lang($key);
}

function translate_for_other_user($key, $user_row) {
	global $config;
	$lang = array();
	$lang_code = basename['user_lang'];
	$user_row['user_lang'] = file_exists("${phpbb_root_path}ext/akruijff/penalty_cards/language/$lang_code/info_mcp.$phpEx")
		? $user_row['user_lang']
		: $config['default_lang'];
	include("$phpbb_root_path}language/" . basename($user_row['user_lang']) . "/mcp.$phpEx");
	$result = $lang[$key];
	unset($lang);
	return $result;
}

function send_pm($subject, $message, $meta) {
	global $phpbb_root_path, $phpEx;
	include_once("${phpbb_root_path}includes/functions_privmsgs.$phpEx");
	include_once("${phpbb_root_path}includes/message_parser.$phpEx");

	$parser = new parse_message();
	$parser->message = $message;
	$parser->parse(true,  true, true, false, false, true, true);

	$arr = array(
		'from_user_id' => $meta['from_id'],
		'from_user_ip' => $meta['from_ip'],
		'from_username' => $meta['from_username'],
		'enable_sig' => false,
		'enable_bbcode' => true,
		'enable_smilies' => true,
		'enable_urls' => false,
		'icon_id' => 0,
		'bbcode_bitfield' => $parser->bbcode_bitfield,
		'bbcode_uid' => $parser->bbcode_uid,
		'message' => $parser->message,
		'address_list' => array('u' => array($meta['to_id'] => 'to')),
	);
	submit_pm('post', $subject, $arr, false);
}

function array_to_string($list) {
	foreach($list as $item)
		$result = empty($result) ? $item : $result . ', ' . $item;
	return $result;
}

function fetch_user_id($username) {
	global $db;
	$sql = 'SELECT user_id, username
		FROM ' . USERS_TABLE ."
		WHERE username='$username'";
	$result = $db->sql_query($sql);
	$user_id = $db->sql_fetchfield('user_id');
	$db->sql_freeresult($result);
	return $user_id;
}

function fetch_username($user_id) {
	global $db;
	$sql = 'SELECT user_id, username
		FROM ' . USERS_TABLE ."
		WHERE username='$username'";
	$result = $db->sql_query($sql);
	$username = $db->sql_fetchfield('username');
	$db->sql_freeresult($result);
	return $username;
}

function fetch_active_card_count() {
	global $db;
	$sql = 'SELECT COUNT(*) AS card_count
		FROM ' . PENALTY_CARDS_TABLE . '
		WHERE ' . time () . ' < card_end OR card_end = 0';
	$result = $db->sql_query($sql);
	$count = $db->sql_fetchfield('card_count');
	$db->sql_freeresult($result);
	return $count;
}

function fetch_card_count($user_id) {
	$rows = fetch_post_rows(array($user_id));
	return !empty($rows[$user_id]['cards']) ?  $rows[$user_id]['cards'] : 0;

}

function fetch_post_rows($post_list) {
	global $db;
	$sql = 'SELECT u.*, count(card_id) as cards
		FROM (' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u)
		LEFT JOIN ' . PENALTY_CARDS_TABLE . ' c ON c.card_post_id = p.post_id
		WHERE post_id IN (' . array_to_string($post_list) . ')
			AND (' . time() . ' < card_end OR card_end = 0)
			AND u.user_id = p.poster_id
		GROUP BY user_id';
	$result = $db->sql_query($sql);
	$cards = array();
	While($row = $db->sql_fetchrow($result))
		$cards[$row['user_id']] = $row;
	$db->sql_freeresult($result);
	return $cards;
}

function fetch_post_row($post_id) {
	global $db;
	$sql = 'SELECT u.*, p.*, c.*
		FROM (' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u)
		LEFT JOIN ' . PENALTY_CARDS_TABLE . ' c ON c.card_post_id = p.post_id
		WHERE p.post_id = ' . $post_id . '
			AND u.user_id = p.poster_id';
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	return $row;
}

function fetch_cards($start, $limit) {
	global $db;
	$sql = 'SELECT u.*, p.*, c.*, m.user_id as mod_id, m.username as mod_username
		FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u, ' . PENALTY_CARDS_TABLE . ' c, ' . USERS_TABLE . " m
		WHERE u.user_id = p.poster_id
			AND p.post_id = c.card_post_id
			AND m.user_id = c.card_mod_id
		ORDER BY c.card_start DESC
		LIMIT $start, $limit";
	$result = $db->sql_query($sql);
	$arr = convert_cards_result_to_array($result);
	$arr = supplement_cards($arr);
	$db->sql_freeresult($result);
	return $arr;
}

function convert_cards_result_to_array($result) {
	global $db;
	$arr = array();
	$i = 0;
	$current = time();
	while($row = $db->sql_fetchrow($result)) {
		$row['S_INACTIVE'] = $row['card_end'] > $current;
		$arr[$i] = $row;
		++$i;
	}
	return $arr;
}

function supplement_cards($arr) {
	global $phpbb_root_path, $phpEx;
	if (!function_exists('phpbb_get_user_rank'))
		include("${phpbb_root_path}includes/functions_display.$phpEx");
	foreach($arr as $key => $row) {
		$user_rank_data = phpbb_get_user_rank($row, $row['user_id'] == ANONYMOUS ? false : $row['user_posts']);
		$row['rank_title'] = $user_rank_data['title'];
		$row['rank_img'] = $user_rank_data['img'];
		$row['rank_img_src'] = $user_rank_data['img_src'];
		$arr[$key] = $row;
	}
	return $arr;
}

function insert_card($card) {
	global $db;
	$arr = array(
		'card_ban_id' => !empty($card['ban_id']) ? $card['ban_id'] : NULL,
		'card_post_id' => $card['post_id'],
		'card_mod_id' => $card['mod_id'],
		'card_start' => $card['start'],
		'card_end' => $card['card_duration'] != 0 ? $card['end'] : 0,
		'card_reason' => $card['reason'],
		'card_reason_shown' => $card['reason_shown'],
	);
	$db->sql_query('INSERT INTO ' . PENALTY_CARDS_TABLE . ' ' . $db->sql_build_array('INSERT', $arr));
}

function fetch_ban_row($user_id, $end) {
	global $db;
	$sql = 'SELECT ban_id
		FROM ' . BANLIST_TABLE . "
		WHERE ban_userid =  $user_id 
			AND ban_end = $end";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	return $row;
}

function clean_ban_list() {
	global $db, $cache;
	$sql = 'DELETE FROM ' . BANLIST_TABLE . '
		WHERE ban_end < ' . time() . '
			AND ban_end <> 0';
	$db->sql_query($sql);
	$cache->destroy('sql', BANLIST_TABLE);
}

function ban_user($card) {
	insert_ban($card);
	delete_sessions($card);
}

function insert_ban($card) {
	global $db;
	$arr = array(
		'ban_userid' => $card['user_id'],
		'ban_start' => $card['start'],
		'ban_end' => $card['end'],
		'ban_reason' => $card['reason'],
		'ban_give_reason' => $card['reason_shown'],
	);
	$db->sql_query('INSERT INTO ' . BANLIST_TABLE . ' ' . $db->sql_build_array('INSERT', $arr));
}

function card_to_template_vars($row) {
	global $phpbb_root_path, $phpEx, $template, $user;
	return array(
		'U_USER' => get_user_url($row['user_id']),
		'U_POST' => get_post_url($row['post_id']),
		'USERNAME_FULL' => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
		'USERNAME' => get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
		'USERNAME' => $row['username'],
		'RANK_TITLE' => $row['rank_title'],
		'RANK_IMG' => $row['rank_img'],
		'RANK_IMG_SRC' => $row['rank_img_src'],
		'CARD_ID' => $row['card_id'],
		'CARD_CLASS' => get_card_class($row['card_ban_id']),
		'CARD_TYPE' => get_card_text($row['card_ban_id']),
		'DURATION' => $row['card_end'] == 0 ? $user->lang('AKRUIJFF_PENALTY_CARDS_DURATION_PERMANENT') : $user->lang('AKRUIJFF_PENALTY_CARDS_DURATION_VALUE', ($row['card_end'] - $row['card_start']) / DAY),
		'EXPIRATION_DATE' => $row['card_end'] == 0 ? $user->lang('AKRUIJFF_PENALTY_CARDS_NEVER') : $user->format_date($row['card_end']),
	);
}
