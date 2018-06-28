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
class board_link implements EventSubscriberInterface {
	static public function getSubscribedEvents() {
		return array(
			'core.user_setup' => 'load_language_on_setup',
			'core.page_header' => 'page_header',
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

	public function page_header($event) {
		global $template, $phpbb_root_path;
		$template->assign_var('U_AKRUIJFF_PENALTY_CARDS_INDEX', generate_board_url() . '/app.php/akruijff/penalty_cards');
	}
}
