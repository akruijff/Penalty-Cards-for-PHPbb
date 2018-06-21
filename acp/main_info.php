<?php
/**
 *
 * Penalty Cards. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Alex de Kruijff, http://alex.kruijff.org
 * @license Original BSD licence
 *
 */

namespace akruijff\penalty_cards\acp;

/**
 * Penalty Cards ACP module info.
 */
class main_info {
	public function module() {
		return array(
			'filename'	=> '\akruijff\penalty_cards\acp\main_module',
			'title'		=> 'ACP_AKRUIJFF_PENALTY_CARDS_CATEGORY',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_AKRUIJFF_PENALTY_CARDS_SETTINGS',
					'auth'	=> 'ext_akruijff/penalty_cards && acl_a_board',
					'cat'	=> array('ACP_AKRUIJFF_PENALTY_CARDS_SETTINGS')
				),
			),
		);
	}
}
