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

class install_mcp_module extends \phpbb\db\migration\migration {
	public function effectively_installed() {
		$sql = 'SELECT module_id
			FROM ' . $this->table_prefix . "modules
			WHERE module_class = 'mcp'
				AND module_langname = 'MCP_AKRUIJFF_PENALTY_CARDS'";
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id !== false;
	}

	static public function depends_on() {
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data() {
		return array(
			array('module.add', array(
				'mcp',
				0,
				'MCP_AKRUIJFF_PENALTY_CARDS_CATAGORY'
			)),
			array('module.add', array(
				'mcp',
				'MCP_AKRUIJFF_PENALTY_CARDS_CATAGORY',
				array(
					'module_basename'	=> '\akruijff\penalty_cards\mcp\main_module',
					'modes'				=> array('front', 'create', 'view', 'edit'),
				),
			)),
		);
	}
}
