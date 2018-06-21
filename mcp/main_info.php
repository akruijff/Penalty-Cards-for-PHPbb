<?php
/**
 *
 * Penalty Cards. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Alex de Kruijff, http://alex.kruijff.org
 * @license Original BSD licence
 *
 */

namespace akruijff\penalty_cards\mcp;

/**
 * Penalty Cards MCP module info.
 */
class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\akruijff\penalty_cards\mcp\main-module',
			'title'		=> 'MCP_AKRUIJFF_PENALTY_CARDS',
			'modes'		=> array(
                                'user'          => array('title' => 'MCP_BAN_USERNAMES', 'auth' => 'acl_m_ban', 'cat' => array('MCP_BAN')),

				'front'	=> array(
					'title'	=> 'MCP_AKRUIJFF_PENALTY_CARDS_FRONT',
					'auth'	=> 'ext_akruijff/penalty_cards && (acl_m_akruijff_penalty_cards_ban || acl_m_akruijff_penalty_cards_warn)',
					'cat'	=> array('MCP_AKRUIJFF_PENALTY_CARDS_TITLE'),
				),
				'create' => array(
					'title'	=> 'MCP_AKRUIJFF_PENALTY_CARDS_CREATE',
					'auth'	=> 'ext_akruijff/penalty_cards && (acl_m_akruijff_penalty_cards_ban || acl_m_akruijff_penalty_cards_warn)',
					'cat'	=> array('MCP_AKRUIJFF_PENALTY_CARDS_CREATE_TITLE'),
				),
				'view' => array(
					'title'	=> 'MCP_AKRUIJFF_PENALTY_CARDS_VIEW',
					'auth'	=> 'ext_akruijff/penalty_cards && (acl_m_akruijff_penalty_cards_ban || acl_m_akruijff_penalty_cards_warn)',
					'cat'	=> array('MCP_AKRUIJFF_PENALTY_CARDS_VIEW_TITLE'),
				),
				'edit' => array(
					'title'	=> 'MCP_AKRUIJFF_PENALTY_CARDS_EDIT',
					'auth'	=> 'ext_akruijff/penalty_cards && (acl_m_akruijff_penalty_cards_ban || acl_m_akruijff_penalty_cards_warn)',
					'cat'	=> array('MCP_AKRUIJFF_PENALTY_CARDS_EDIT_TITLE'),
				),
			),
		);
	}
}
