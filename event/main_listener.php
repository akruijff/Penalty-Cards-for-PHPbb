<?php
/**
 *
 * Penalty Cards. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Alex de Kruijff, http://alex.kruijff.org
 * @license Original BSD licence
 *
 */

namespace akruijff\penalty_cards\event;

global $phpbb_root_path;
include_once($phpbb_root_path . 'ext/akruijff/penalty_cards/includes/constants.php');
include_once($phpbb_root_path . 'ext/akruijff/penalty_cards/includes/functions.php');

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Penalty Cards Event listener.
 */
class main_listener implements EventSubscriberInterface {
	static public function getSubscribedEvents() {
		return array(
			'core.user_setup' => 'load_language_on_setup',
			'core.viewtopic_get_post_data' => 'viewtopic_get_post_data',
			'core.viewtopic_post_rowset_data' => 'viewtopic_post_rowset_data',
			'core.viewtopic_modify_post_row' => 'viewtopic_modify_post_row',
			'core.viewtopic_modify_page_title' => 'viewtopic_modify_page_title', // can break
#			'core.viewtopic_page_header_before' => 'viewtopic_modify_page_title', // does not jet exists
			'core.viewtopic_post_row_after' => 'viewtopic_post_row_after',
		);
	}

	private $cards;
	private $user_cards = array();
	private $post_row = array();

	/* @var \phpbb\controller\helper */
	protected $helper;

	public function __construct(\phpbb\controller\helper $helper) {
		$this->helper = $helper;
	}

	/**
	 * Load common language files during user setup
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_language_on_setup($event) {
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'akruijff/penalty_cards',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function viewtopic_get_post_data($event) {
		$event['sql_ary'] = $this->add_card_data($event['sql_ary']);
		$this->user_cards = $this->fetch_user_cards($event['post_list']);
	}

	private function add_card_data($sql) {
		$sql['SELECT'] = $sql['SELECT'] . ', c.*, c.card_id, c.card_start, c.card_end, c.card_reason, c.card_reason_shown';

		$join = $sql['LEFT_JOIN'];
		array_push($join, array(
			'FROM' => array(PENALTY_CARDS_TABLE => 'c'),
			'ON' => 'c.card_post_id = p.post_id',
		));
		$sql['LEFT_JOIN'] = $join;
		return $sql;
	}

	private function fetch_user_cards($post_list) {
		global $db;
		$sql = 'SELECT c.card_id, c.card_ban_id, c.card_post_id, c.card_user_id
			FROM ' . PENALTY_CARDS_TABLE . ' c
			WHERE (c.card_end = 0 OR c.card_start < ' . time() . ' AND ' . time() . ' < c.card_end)
				AND c.card_user_id IN (
					SELECT DISTINCT p.poster_id
					FROM ' . POSTS_TABLE . ' p
					WHERE p.post_id IN (' . array_to_string($post_list) . ')
				)';
		$result = $db->sql_query($sql);
		$arr = $this->build_user_cards_array($result);
		$db->sql_freeresult($result);
		return $arr;
	}

	private function build_user_cards_array($result) {
		global $db;
		$arr = array();
		while($row = $db->sql_fetchrow($result)) {
			$user_id = $row['card_user_id'];
			$post_id = $row['card_post_id'];
			$card_id = $row['card_id'];
			$ban_id = $row['card_ban_id'];
			if (empty($arr[$user_id]))
				$arr[$user_id] = array();
			$card_type = empty($ban_id) ? 'yellow' : 'red';
			if (empty($arr[$user_id][$card_type]))
				$arr[$user_id][$card_type] = array();
			if (empty($arr[$user_id][$card_type][$card_id]))
				$arr[$user_id][$card_type][$card_id] = array();
			$arr[$user_id][$card_type][$card_id] = $post_id;
		}
		return $arr;
	}

	public function viewtopic_post_rowset_data($event) {
		$row = $event['row'];
		$this->add_card_buttons($row);
		if (!empty($row['card_id']))
			$this->add_card_message($row);
	}

	private function add_card_buttons($row) {
		global $user, $phpbb_root_path;
		$post_id = $row['post_id'];
		$user_id = $row['user_id'];
		$base_url = "{$phpbb_root_path}mcp.$this->phpEx";
		$base_arg = 'i=' . MCP_ID . '&amp;mode=create&amp;p=' . $row['post_id'] . '&amp;type=';
		$count = empty($this->cards[$user_id]) ? 0 : $this->cards[$user_id]['cards'];
		$this->post_row[$post_id] = array(
			'U_YELLOW_CARD' => can_issue_yellow($row, $count)
				? append_sid($base_url, $base_arg . 'yellow', true, $user->session_id)
				: '',
			'U_RED_CARD' => can_issue_red($row)
				? append_sid($base_url, $base_arg . 'red', true, $user->session_id)
				: '',
		);
	}

	private function add_card_message($row) {
		global $user;
		$post_id = $row['post_id'];
		$this->post_row[$post_id] = array_merge($this->post_row[$post_id], array(
			'CARD_MESSAGE' => $user->lang('AKRUIJFF_PENALTY_CARDS_ISSUED',
				($row['card_end'] - $row['card_start']) / DAY,
				$user->format_date($row['card_start']),
				$user->format_date($row['card_end']),
				$this->format_card($row)),
			'CARD_REASON' => $row['card_reason_shown']
				? $row['card_reason_shown']
				: $row['card_reason'],
		));
	}

	private function format_card($card_row) {
		global $user;
		$ban_id = $card_row['card_ban_id'];
		$url = get_index_url($card_row['card_id']);
		$type = $user->lang($ban_id ? 'AKRUIJFF_PENALTY_CARDS_RED_CARD': 'AKRUIJFF_PENALTY_CARDS_YELLOW_CARD');
		$class = get_card_class($ban_id);
		return '<a class="' . $class. '" href="' . $url. '">' . $type . '</a>';
	}

	public function viewtopic_modify_post_row($event) {
		$this->publish_post_row($event);
	}

	private function publish_post_row($event) {
		$post_id = $event['row']['post_id'];
		$event['post_row'] = array_merge($event['post_row'], $this->post_row[$post_id]);
	}

	public function viewtopic_post_row_after($event) {
		$this->assign_cards($event);
	}

	private function assign_cards($event) {
		$cards = $this->user_cards[$event['row']['user_id']];
		$this->assign_cards_helper($cards, 'yellow');
		$this->assign_cards_helper($cards, 'red');
	}

	private function assign_cards_helper($cards, $card_type) {
		global $template, $phpbb_root_path, $phpEx;
		if (empty($cards[$card_type]))
			return;
		foreach($cards[$card_type] as $card_id => $post_id)
			$template->assign_block_vars('postrow.akruijff_penalty_cards_' . $card_type, array(
				'U_AKRUIJFF_PENALTY_CARDS_CARD' => "${phpbb_root_path}viewtopic.$phpEx?p=$post_id#p$post_id",
			));
	}

	public function viewtopic_modify_page_title($event) {
		unset($this->post_row);
		unset($this->cards);
	}
}
