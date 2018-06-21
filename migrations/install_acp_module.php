<?php
/**
 *
 * Penalty Cards. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Alex de Kruijff, http://alex.kruijff.org
 * @license Original BSD licence
 *
 */

namespace akruijff\penalty_cards\migrations;

class install_acp_module extends \phpbb\db\migration\migration {
	public function effectively_installed() {
		return isset($this->config['akruijff_penalty_cards_duration1']);
	}

	static public function depends_on() {
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data() {
		return array(
			array('config.add', array('akruijff_penalty_cards_max_yellow_cards', 1)),
			array('config.add', array('akruijff_penalty_cards_duration1', 14)),
			array('config.add', array('akruijff_penalty_cards_duration2', 30)),
			array('config.add', array('akruijff_penalty_cards_duration3', 60)),
			array('config.add', array('akruijff_penalty_cards_window', 90)),
			array('config.add', array('akruijff_penalty_cards_notification_sender', '')),
			array('permission.add', array('m_akruijff_penalty_cards_warn')),
			array('permission.add', array('m_akruijff_penalty_cards_ban')),
			array('permission.permission_set', array('ROLE_MOD_FULL', 'm_akruijff_penalty_cards_warn')),
			array('permission.permission_set', array('ROLE_MOD_FULL', 'm_akruijff_penalty_cards_ban')),
			array('permission.permission_set', array('ROLE_MOD_STANDARD', 'm_akruijff_penalty_cards_warn')),
			array('permission.permission_unset', array('ROLE_MOD_FULL', 'm_warn')),
			array('permission.permission_unset', array('ROLE_MOD_FULL', 'm_ban')),
			array('permission.permission_unset', array('ROLE_MOD_STANDARD', 'm_warn')),

			// Parent module
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_AKRUIJFF_PENALTY_CARDS_CATAGORY'
			)),

			// Main module
			array('module.add', array(
				'acp',
				'ACP_AKRUIJFF_PENALTY_CARDS_CATAGORY',
				array(
					'module_basename'	=> '\akruijff\penalty_cards\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
