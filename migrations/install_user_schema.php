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

class install_user_schema extends \phpbb\db\migration\migration {
	public function effectively_installed() {
		return $this->db_tools->sql_table_exists($this->table_prefix . 'akruijff_penalty_cards');
	}

	static public function depends_on() {
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_schema() {
		return array(
			'add_tables' => array(
				$this->table_prefix . 'akruijff_penalty_cards' => array(
					'COLUMNS' => array(
						'card_id' => array('UINT:10', NULL, 'auto_increment'),
						'card_ban_id' => array('UINT:10', NULL),
						'card_post_id' => array('UINT:10', NULL),
						'card_user_id' => array('UINT:10', NULL),
						'card_mod_id' => array('UINT:10', NULL),
						'card_start' => array('UINT:11', NULL),
						'card_end' => array('UINT:11', NULL),
						'card_reason' => array('VCHAR:255', ''),
						'card_reason_shown' => array('VCHAR:255', ''),
					),
					'PRIMARY_KEY' => 'card_id',
					'KEYS' => array (
						'post_index' => array('UNIQUE', 'post_id'),
						'user_index' => array('INDEX', 'user_id'),
						'mod_index' => array('INDEX', 'mod_id'),
						'start_index' => array('INDEX', 'card_start'),
						'end_index' => array('INDEX', 'card_end'),
					),
				),
			),
		);
	}

	public function revert_schema() {
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'akruijff_penalty_cards',
			),
		);
	}
}
