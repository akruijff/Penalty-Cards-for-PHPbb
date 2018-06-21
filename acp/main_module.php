<?php /**
 *
 * Penalty Cards. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Alex de Kruijff, http://alex.kruijff.org
 * @license Original BSD licence
 *
 */

# * @license GNU General Public License, version 2 (GPL-2.0)
namespace akruijff\penalty_cards\acp;

global $phpbb_root_path;

include_once($phpbb_root_path . 'ext/akruijff/penalty_cards/includes/functions.php');

/**
 * Penalty Cards ACP module.
 */
class main_module {
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode) {
		global $request;
		if ($request->is_set_post('submit'))
			$this->submit();
		else
			$this->show();
	}

	private function submit() {
		global $config, $user;

		if (!check_form_key('akruijff_penalty_cards'))
			trigger_error('FORM_INVALID', E_USER_WARNING);

		$max_yellow_cards = request_number_variable('akruijff_penalty_cards_max_yellow_cards');
		$duration1 = request_number_variable('akruijff_penalty_cards_duration1');
		$duration2 = request_number_variable('akruijff_penalty_cards_duration2');
		$duration3 = request_number_variable('akruijff_penalty_cards_duration3');
		$window = request_number_variable('akruijff_penalty_cards_window');
		$notification_sender = request_username('akruijff_penalty_cards_notification_sender');

		$config->set('akruijff_penalty_cards_max_yellow_cards', $max_yellow_cards);
		$config->set('akruijff_penalty_cards_duration1', $duration1);
		$config->set('akruijff_penalty_cards_duration2', $duration2);
		$config->set('akruijff_penalty_cards_duration3', $duration3);
		$config->set('akruijff_penalty_cards_window', $window);
		$config->set('akruijff_penalty_cards_notification_sender', $notification_sender);

		trigger_error($user->lang('ACP_AKRUIJFF_PENALTY_CARDS_SETTING_SAVED') . adm_back_link($this->u_action));
	}

	public function show() {
		$this->load_template('acp_penalty_cards_settings', 'ACP_AKRUIJFF_PENALTY_CARDS_SETTINGS_TITLE');
		$this->load_config();
	}

	private function load_template($name, $title) {
		global $template;
		$this->tpl_name = $name;
		$this->page_title = $title;
		add_form_key('akruijff_penalty_cards');
		$template->assign_var('U_POST_ACTION', $this->u_action);
	}

	public function load_config() {
		global $phpbb_root_path, $phpEx, $config, $template;
		$template->assign_vars(array(
			'U_FIND_USERNAME' => append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=acp_board&amp;field=akruijff_penalty_cards_notification_sender&amp;select_single=true'),
			'AKRUIJFF_PENALTY_CARDS_MAX_YELLOW_CARDS' => $config['akruijff_penalty_cards_max_yellow_cards'],
			'AKRUIJFF_PENALTY_CARDS_DURATION1' => $config['akruijff_penalty_cards_duration1'],
			'AKRUIJFF_PENALTY_CARDS_DURATION2' => $config['akruijff_penalty_cards_duration2'],
			'AKRUIJFF_PENALTY_CARDS_DURATION3' => $config['akruijff_penalty_cards_duration3'],
			'AKRUIJFF_PENALTY_CARDS_WINDOW' => $config['akruijff_penalty_cards_window'],
			'AKRUIJFF_PENALTY_CARDS_NOTIFICATION_SENDER' => $config['akruijff_penalty_cards_notification_sender'],
		));
	}
}
