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
			'core.page_header' => 'page_header',
			'core.viewtopic_get_post_data' => 'viewtopic_get_post_data',
			'core.viewtopic_post_rowset_data' => 'viewtopic_post_rowset_data',
			'core.viewtopic_modify_post_row' => 'viewtopic_modify_post_row',
			'core.viewtopic_modify_page_title' => 'viewtopic_modify_page_title', // can break
			'core.viewtopic_page_header_before' => 'viewtopic_modify_page_title', // does not jet exists
		);
	}

	private $cards;
	private $arr = array();

	/** @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/** @var string phpEx */
	protected $phpEx;

	public function __construct(
			\phpbb\auth\auth $auth,
			\phpbb\controller\helper $helper,
			\phpbb\template\template $template,
			\phpbb\user $user, $phpEx) {
		$this->auth = $auth;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->phpEx = $phpEx;
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

	public function page_header($event) {
		global $template, $phpbb_root_path;
		$template->assign_var('U_AKRUIJFF_PENALTY_CARDS_INDEX', generate_board_url() . '/app.php/akruijff/penalty_cards');
	}

	public function viewtopic_get_post_data($event) {
		$this->cards = fetch_post_rows($event['post_list']);
		$event['sql_ary'] = $this->add_card_data($event['sql_ary']);
	}

	private function add_card_data($sql) {
		$sql['SELECT'] = $sql['SELECT'] . ', c.*';

		$join = $sql['LEFT_JOIN'];
		array_push($join, array(
			'FROM' => array(PENALTY_CARDS_TABLE => 'c'),
			'ON' => 'c.card_post_id = p.post_id',
		));
		$sql['LEFT_JOIN'] = $join;
		return $sql;
	}

	public function viewtopic_post_rowset_data($event) {
		$this->add_card_buttons($event['row']);
		if (!empty($event['row']['card_id']))
			$this->add_card_message($event['row']);
	}

	private function add_card_buttons($row) {
		global $user, $phpbb_root_path;
		$post_id = $row['post_id'];
		$user_id = $row['user_id'];
		$base_url = "{$phpbb_root_path}mcp.$this->phpEx";
		$base_arg = 'i=' . MCP_ID . '&amp;mode=create&amp;p=' . $row['post_id'] . '&amp;type=';
		$count = empty($this->cards[$user_id]) ? 0 : $this->cards[$user_id]['cards'];
		$this->arr[$post_id] = array(
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
		$this->arr[$post_id] = array_merge($this->arr[$post_id], array(
			'CARD_MESSAGE' => $user->lang('CARD_ISSUED',
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
		$event['post_row'] = array_merge($event['post_row'], $this->arr[$post_id]);
	}

	public function viewtopic_modify_page_title($event) {
		unset($this->arr);
		unset($this->cards);
	}
}
