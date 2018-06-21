<?php
/**
 *
 * Penalty Cards. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Alex de Kruijff, http://alex.kruijff.org
 * @license Original BSD licence
 *
 */

namespace akruijff\penalty_cards\mcp;

global $phpbb_root_path;
include_once($phpbb_root_path . 'ext/akruijff/penalty_cards/includes/constants.php');
include_once($phpbb_root_path . 'ext/akruijff/penalty_cards/includes/functions.php');

#function phpbb_module_\akruijff\penalty_cards\mcp\main_module_url

function _penalty_cards_mcp_module_url($mode, &$module_row) {
return 'x';
}

function _akruijff_penalty_cards_url($mode, &$module_row) {
return 'x';
}

function phpbb_akruijff_penalty_cards_mcp_module_url($mode, &$module_row) {
return 'x';
}

function _akruijff_penalty_cards_mcp_module_url($mode, &$module_row) {
return 'x';
}

/**
 * Penalty Cards MCP module.
 */
class main_module {
	var $u_action;

	public function url() {
		return "foo";
	}

	function main($id, $mode) {
		global $request;
		switch($mode) {
			case 'front':
				$action = $this->request_first_key('action');
				if (!empty($action))
					$this->process_front_form($id, $action);
				else
					$this->show_front($id);
				break;
			case 'create':
				if ($request->is_set_post('submit'))
					$this->process_create_form($id);
				else
					$this->show_create();
				break;
			case 'view':
				$this->show_view($id);
				break;
			case 'edit':
				$this->show_edit($id);
				break;
		}
	}

	private function request_first_key($name) {
		global $request;
		$arr = $request->variable($name, array('' => ''));
		if (is_array($arr))
			list($arr, ) = each($arr);
		else
			$arr = $request->variable('arr', '');
		return $arr;
	}

	private function assign_template($name, $title) {
		global $template, $request;
		$this->tpl_name = $name;
		$this->page_title = $title;
		add_form_key('akruijff_penalty_cards');
		$template->assign_var('U_POST_ACTION', $this->u_action . '&amp;p=' . $request->variable('p', 0));
	}

//	****************************************************************

	private function process_front_form($id, $action) {
		global $request, $db;
		if ($action = 'del_marked') {
			$list = $request->variable('card_id_list', array(0));
			$sql = 'DELETE FROM ' . PENALTY_CARDS_TABLE . '
				WHERE card_id IN (' . array_to_string($list) . ')';
			$db->sql_query($sql);
		}
		$this->show_front($id);
	}

	private function show_front($id) {
		$this->assign_template('mcp_front', 'MCP_AKRUIJFF_PENALTY_CARDS_FRONT');
		$this->assign_front($id);
	}

	private function assign_front($id) {
		global $request;
		$start = $request->variable('start', 0);
		$size = fetch_active_card_count();
		$this->assign_pagination($start, $size);
		$this->assign_page_content($id, $start);
	}

	private function assign_pagination($start, $size) {
		global $config, $phpbb_container;
		$base_url = $this->u_action;
		$pagination = $phpbb_container->get('pagination');
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $size, $config['topics_per_page'], $start);
	}

	private function assign_page_content($id, $start) {
		global $config;
		$arr = fetch_cards($start, $config['topics_per_page']);
		foreach($arr as $row)
			$this->assign_front_block($id, $row);
	}

	private function assign_front_block($id, $row) {
		global $phpbb_root_path, $phpEx, $template, $user;
		$arr = card_to_template_vars($row);
		$arr = array_merge($arr, array(
			'MOD_USERNAME' => $row['mod_username'],
			'MOD_ID' => $row['mod_id'],

			'U_PROFILE' => append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['card_user_id']),
			'U_VIEW' => append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;mode=view&amp;c=" . $row['card_id']),
			'U_EDIT' => append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;mode=edit&amp;c=" . $row['card_id']),
			'U_DELETE' => append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;mode=delete&amp;c=" . $row['card_id']),
		));
		$template->assign_block_vars('penalty_cards_row', $arr);
	}

//	****************************************************************

	private function process_create_form($id) {
		global $phpEx, $phpbb_root_path;
		$card = $this->get_card();
		$this->check_card($card);
		if ($this->is_card_valid($card)) {
			$this->register_card($card);
			redirect(append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;mode=front"));
		} else {
			$this->show_create();
			$this->assign_card($card);
		}
	}

	private function get_card() {
		global $phpbb_root_path, $phpEx, $user, $request, $config;
		if (!function_exists('phpbb_get_user_rank'))
			include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

		$post_id = request_number_variable('p');
		$post_row = fetch_post_row($post_id);
		$user_rank_data = phpbb_get_user_rank($post_row, $post_row['user_posts']);

		return array(
			'user_id' => $post_row['user_id'],
			'user_ip' => $post_row['user_ip'],
			'user_type' => $post_row['user_type'],
			'username' => $post_row['username'],
			'avatar_img' => phpbb_get_user_avatar($post_row),
			'rank_title' => $user_rank_data['title'],
			'rank_img' => $user_rank_data['img'],
			'user_lang' => $user->lang_name,
			'user_color' => !empty($post_row['user_colour']) ? $post_row['user_colour'] : '',
			'joined' => $user->format_date($post_row['user_regdate']),
			'posts' => $post_row['user_posts'] ? $post_row['user_posts'] : 0,
			'warnings' => $post_row['user_warnings'] ? $post_row['user_warnings'] : 0,
			'cards' => !empty($post_row['user_id']) ? fetch_card_count($post_row['user_id']) : '0',

			'post_id' => $post_id,
			'post' => $this->get_post($post_row),
			'post_time' => $post_row['post_time'],

			'card_type' => $request->variable('card-type', ''),
			'duration' => $this->request_duration(),
			'reason' => $request->variable('reason', ''),
			'reason_shown' => $request->variable('reason_shown', ''),

			'mod_id' => $user->data['user_id'],
			'from_id' => !empty($config['akruijff_penalty_cards_notification_sender'])
				? fetch_user_id($config['akruijff_penalty_cards_notification_sender'])
				: $user->data['user_id'],
			'from_ip' => !empty($config['akruijff_penalty_cards_notification_sender'])
				? '127.0.0.1'
				: $user->ip,
			'from_username' => !empty($config['akruijff_penalty_cards_notification_sender'])
				? $config['akruijff_penalty_cards_notification_sender']
				: $user->data['username'],
		);
	}

	private function get_post($row) {
		$parse_flags = OPTION_FLAG_SMILIES | ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0);
		return generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, true);
	}

	private function request_duration() {
		global $request;
		$duration = $request->variable('duration', '');
		return is_numeric($duration) ? $duration
			: $duration == 'permanent' ? 0
			: $request->variable('duration_specification', '');
	}

	private function check_card($card) {
		global $user;
		if ($card['user_type'] == USER_IGNORE)
			trigger_error('CANNOT_CARD_ANONYMOUS');
		if ($card['user_type'] == USER_FOUNDER)
			trigger_error('CANNOT_CARD_FOUNDER');
		if ($user->data['user_id'] == $card['user_id'])
			trigger_error('CANNOT_CARD_SELF');
	}

	private function is_card_valid($card) {
		global $user, $config, $template;
		if (!check_form_key('akruijff_penalty_cards'))
			trigger_error('FORM_INVALID', E_USER_WARNING);
		$msg = '';
		if (is_card_issued($card['post_id']))
			$msg .= $user->lang('MCP_AKRUIJFF_PENALTY_CARDS_ERROR_CARD_PRESENT') . '<br />';
		if ($card['card_type'] != YELLOW_CARD && $card['card_type'] != RED_CARD)
			$msg .= $user->lang('MCP_AKRUIJFF_PENALTY_CARDS_ERROR_CARD_TYPE') . '<br />';
		if (!is_numeric($card['duration']))
			$msg .= $user->lang('MCP_AKRUIJFF_PENALTY_CARDS_ERROR_DURATION') . '<br />';
		if (!$card['reason'])
			$msg .= $user->lang('MCP_AKRUIJFF_PENALTY_CARDS_ERROR_REASON') . '<br />';
		if ($card['card_type'] == YELLOW_CARD && $card['cards'] >= $config['akruijff_penalty_cards_max_yellow_cards'])
			$msg .= $user->lang('MCP_AKRUIJFF_PENALTY_CARDS_ERROR_TOO_MANY') . '<br />';
		if ($config['akruijff_penalty_cards_window'] == 0 || $card['post_time'] + $config['akruijff_penalty_cards_window'] * DAY < time())
			$msg .= $user->lang('MCP_AKRUIJFF_PENALTY_CARDS_ERROR_WINDOW') . '<br />';
		if ($msg)
			$template->assign_var('MESSAGE', $msg);
		return $msg ? false : true;
	}

	private function register_card($card) {
		$card['start'] = time();
		$card['end'] = $card['start'] + $card['duration'] * DAY;
		if ($card['card_type'] == RED_CARD && $card['user_type'] == USER_NORMAL) {
			$card = $this->register_ban($card);
			$card['card_type'] = 'yellow';
		} else
			$this->notify_user_of_yellow_card($card);
		insert_card($card);
		$this->log_card($card);
	}

	private function register_ban($card) {
		clean_ban_list();
		ban_user($card);
		$ban_row = fetch_ban_row($card['user_id'], $card['end']);
		$card['ban_id'] = $ban_row['ban_id'];
		return $card;
	}

	private function log_card($card) {
		global $phpbb_log, $user;
		$user_id = $user->data['user_id'];
		$operation = !empty($card['ban_id'])
			? 'LOG_AKRUIJFF_PENALTY_CARDS_ISSUED_RED_CARD'
			: 'LOG_AKRUIJFF_PENALTY_CARDS_ISSUED_YELLOW_CARD';
		$phpbb_log->add('admin', $user_id, $user->ip, $operation, false, array(
			$card['username'], $card['duration'], $card['reason'],
		));
		$phpbb_log->add('mod', $user_id, $user->ip, $operation, false, array(
			$card['username'], $card['duration'], $card['reason'],
			'forum_id' => $card['forum_id'],
			'topic_id' => $card['topic_id'],
			'post_id' => $card['post_id'],
		));
		$phpbb_log->add('user', $user_id, $user->ip, $operation . '_REASON', false, array(
			'reportee_id' => $card['user_id'],
			$card['username'], $card['duration'], $card['reason'],
		));
	}

	private function notify_user_of_yellow_card($card) {
		global $phpbb_root_path, $phpEx;
		$card['user_lan'] = 'en'; // $user_row['user_lan'];
		$subject = translate_for_user('MCP_AKRUIJFF_PENALTY_CARDS_YELLOW_CARD_SUBJECT', $card);
		$format = translate_for_user('MCP_AKRUIJFF_PENALTY_CARDS_YELLOW_CARD_MESSAGE', $card);
		$url = generate_board_url() . '/viewtopic.' . $phpEx . '?p=' . $card['post_id'] . '#p' . $card['post_id'];
		$message = sprintf($format, $url);
		send_pm($subject, $message, array(
			'to_id' => $card['user_id'],
			'from_id' => $card['from_id'],
			'from_ip' => $card['from_ip'],
			'from_username' => $card['from_username'],
		));
	}

	private function assign_card($card) {
		global $template;
		$template->assign_vars(array(
			'POST_ID' => $card['post_id'],
			'CARD_TYPE' => $card['card_type'],
			'DURATION' => $card['duration'],
			'REASON' => $card['reason'],
			'REASON_SHOWN' => $card['reason_shown'],
		));
	}

	private function show_create() {
		$card = $this->get_card();
		$this->assign_template('mcp_create', 'MCP_AKRUIJFF_PENALTY_CARDS_CREATE');
		$this->assign_create($card);
	}

	private function assign_create($card) {
		$this->assign_post_details($card);
		$this->assign_card_details();
	}

	private function assign_post_details($card) {
		global $template;
		$template->assign_vars(array(
			'USER_COLOR' => $card['user_color'],
			'USERNAME' => $card['username'],
			'AVATAR_IMG' => $card['avatar_img'],
			'RANK_TITLE' => $card['rank_title'],
			'RANK_IMG' => $card['rank_img'],
			'JOINED' => $card['joined'],
			'POSTS' => $card['posts'],
			'WARNINGS' => $card['warnings'],
			'CARDS' => $card['cards'],
			'POST' => $card['post'],
		));
	}

	private function assign_card_details() {
		global $config, $template, $request;
		$type = $request->variable('type', '');
		$template->assign_vars(array(
			'CARD_TYPE' => $type,
			'YELLOW_CARD' => YELLOW_CARD,
			'RED_CARD' => RED_CARD,
			'DURATION1' => $config['akruijff_penalty_cards_duration1'],
			'DURATION2' => $config['akruijff_penalty_cards_duration2'],
			'DURATION3' => $config['akruijff_penalty_cards_duration3'],
			'DURATION_PERMANENT' => 'permanent',
			'DURATION_OTHER' => 'other',
		));
	}

//	****************************************************************

	private function show_view($id) {
		$this->assign_template('mcp_view', 'MCP_AKRUIJFF_PENALTY_CARDS_VIEW');
	}

//	****************************************************************

	private function show_edit($id) {
		$this->assign_template('mcp_edit', 'MCP_AKRUIJFF_PENALTY_CARDS_EDIT');
	}
}
