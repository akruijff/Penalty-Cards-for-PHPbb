<?php
/**
 *
 * Penalty Cards. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Alex de Kruijff, http://alex.kruijff.org
 * @license Original BSD licence
 *
 */

namespace akruijff\penalty_cards\controller;

global $phpbb_root_path, $phpEx;
include_once($phpbb_root_path . 'ext/akruijff/penalty_cards/includes/constants.php');
include_once($phpbb_root_path . 'ext/akruijff/penalty_cards/includes/functions.php');

include_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);

/**
 * Penalty Cards main controller.
 */
class board {
	/* @var \phpbb\controller\helper */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper	$helper
	 */
	public function __construct(\phpbb\controller\helper $helper) {
		$this->helper = $helper;
	}

	public function card($id) {
		return $this->index();
	}

	public function index() {
		global $request, $user;
		$start = $request->variable('start', 0);
		$size = $this->fetch_card_count();
		$this->assign_pagination($start, $size);
		$this->assign_page_content($start);
		return $this->helper->render('index.html', $user->lang('AKRUIJFF_PENALTY_CARDS_INDEX_TITLE'));
	}

	private function fetch_card_count() {
		global $db;
		$sql = 'SELECT COUNT(*) AS card_count
			FROM ' . PENALTY_CARDS_TABLE;
		$result = $db->sql_query($sql);
		$count = $db->sql_fetchfield('card_count');
		$db->sql_freeresult($result);
	}

	private function assign_pagination($start, $size) {
		global $config, $phpbb_container, $phpbb_root_path;
		$base_url = "${phpbb_root_path}app.php/akruijff/penalty_cards";
		$pagination = $phpbb_container->get('pagination');
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $size, $config['topics_per_page'], $start);
	}

	private function assign_page_content($start) {
		global $config;
		$arr = $this->fetch_cards($start, $config['topics_per_page']);
		foreach ($arr as $row)
			$this->assign_index_block($row);
	}

	private function fetch_cards($start, $limit) {
		global $db;
		$sql = 'SELECT c.card_id, c.card_ban_id, c.card_start, c.card_end, u.user_id, u.username, u.user_colour, p.post_id
			FROM ' . PENALTY_CARDS_TABLE . ' c, ' . USERS_TABLE . ' u, ' . POSTS_TABLE . " p
			WHERE c.card_user_id = u.user_id
				AND c.card_post_id = p.post_id
			ORDER BY c.card_start DESC
			LIMIT $start, $limit";
		$result = $db->sql_query($sql);

		global $phpbb_root_path, $phpEx;
		if (!function_exists('phpbb_get_user_rank'))
			include("${phpbb_root_path}includes/functions_display.$phpEx");

		$arr = array();
		$current = time();
		while($row = $db->sql_fetchrow($result)) {
			$row['INACTIVE'] = $row['card_end'] > $current;
			$arr[$i] = $row;
			++$i;
		}

		$db->sql_freeresult($result);
		return $arr;
	}

	private function assign_index_block($row) {
		global $phpbb_root_path, $phpEx, $template, $user;

		if (!function_exists('phpbb_get_user_rank'))
			include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		$user_rank_data = phpbb_get_user_rank($post_row, $post_row['user_posts']);

		$template->assign_block_vars('penalty_cards_row', array(
			'U_USER' => get_user_url($row['user_id']),
			'U_POST' => get_post_url($row['post_id']),
			'USERNAME_FULL' => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
			'USERNAME' => get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
			'USERNAME' => $row['username'],
			'RANK_TITLE' => $user_rank_data['title'],
			'RANK_IMG' => $user_rank_data['img'],
			'RANK_IMG_SRC' => $user_rank_data['rank_img_src'],
			'CARD_ID' => $row['card_id'],
			'CARD_CLASS' => get_card_class($row['card_ban_id']),
			'CARD_TYPE' => get_card_text($row['card_ban_id']),
			'DURATION' => $row['card_end'] == 0 ? $user->lang('AKRUIJFF_PENALTY_CARDS_DURATION_PERMANENT') : $user->lang('AKRUIJFF_PENALTY_CARDS_DURATION_VALUE', ($row['card_end'] - $row['card_start']) / DAY),
			'EXPIRATION_DATE' => $row['card_end'] == 0 ? $user->lang('AKRUIJFF_PENALTY_CARDS_NEVER') : $user->format_date($row['card_end']),
			'REASON' => $row['card_reason_shown'] ? $row['card_reason_shown'] : $row['card_reason'],
		));
	}
}
