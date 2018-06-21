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
class main {
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config		$config
	 * @param \phpbb\controller\helper	$helper
	 * @param \phpbb\template\template	$template
	 * @param \phpbb\user			$user
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user) {
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
	}

	public function card($id) {
		return $this->index();
	}

	public function index() {
		global $request, $user;
		$start = $request->variable('start', 0);
		$size = fetch_active_card_count();
		$this->assign_pagination($start, $size);
		$this->assign_page_content($start);
		return $this->helper->render('index.html', $user->lang('AKRUIJFF_PENALTY_CARDS_INDEX_TITLE'));
	}

	private function assign_pagination($start, $size) {
		global $config, $phpbb_container, $phpbb_root_path;
		$base_url = "${phpbb_root_path}app.php/akruijff/penalty_cards";
		$pagination = $phpbb_container->get('pagination');
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $size, $config['topics_per_page'], $start);
	}

	private function assign_page_content($start) {
		global $config;
		$arr = fetch_cards($start, $config['topics_per_page']);
		foreach ($arr as $row)
			$this->assign_index_block($row);
	}

	private function assign_index_block($row) {
		global $template, $user;
		$arr = card_to_template_vars($row);
		$arr = array_merge($arr, array(
			'REASON' => $row['card_reason_shown'] ? $row['card_reason_shown'] : $row['card_reason'],
		));
		$template->assign_block_vars('penalty_cards_row', $arr);
	}
}
