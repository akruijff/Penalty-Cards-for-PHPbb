<?php
/**
 *
 * Penalty Cards. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Alex de Kruijff, http://alex.kruijff.org
 * @license Original BSD licence
 *
 */

if (!defined('IN_PHPBB'))
	exit;

if (empty($lang) || !is_array($lang))
	$lang = array();

$lang = array_merge($lang, array(
	'MCP_AKRUIJFF_PENALTY_CARDS_CATAGORY'			=> 'Penalty Cards',
	'MCP_AKRUIJFF_PENALTY_CARDS_FRONT'				=> 'Front page',
	'MCP_AKRUIJFF_PENALTY_CARDS_FRONT_TITLE'		=> 'Front page',
	'MCP_AKRUIJFF_PENALTY_CARDS_CREATE'				=> 'Issue card',
	'MCP_AKRUIJFF_PENALTY_CARDS_CREATE_TITLE'		=> 'Issue card',
	'MCP_AKRUIJFF_PENALTY_CARDS_VIEW'				=> 'View card',
	'MCP_AKRUIJFF_PENALTY_CARDS_VIEW_TITLE'			=> 'View card',
	'MCP_AKRUIJFF_PENALTY_CARDS_EDIT'				=> 'Edit card',
	'MCP_AKRUIJFF_PENALTY_CARDS_EDIT_TITLE'			=> 'Edit card',
	'MCP_AKRUIJFF_PENALTY_CARDS_DELETE'				=> 'Delete card',
	'MCP_AKRUIJFF_PENALTY_CARDS_DELETE_TITLE'		=> 'Delete card',
	'MCP_AKRUIJFF_PENALTY_CARDS_DETAILS'			=> 'Card details',

	'MCP_AKRUIJFF_PENALTY_CARDS_YELLOW_CARD'		=> 'Yellow card (warning)',
	'MCP_AKRUIJFF_PENALTY_CARDS_RED_CARD'			=> 'Red card (ban)',

	'MCP_AKRUIJFF_PENALTY_CARDS_ERROR_CARD_PRESENT'	=> 'Card already issued for post',
	'MCP_AKRUIJFF_PENALTY_CARDS_ERROR_CARD_TYPE'	=> 'Card type must be yellow or red.',
	'MCP_AKRUIJFF_PENALTY_CARDS_ERROR_DURATION'		=> 'Duration must be a number.',
	'MCP_AKRUIJFF_PENALTY_CARDS_ERROR_REASON'		=> 'Reason must be supplied.',
	'MCP_AKRUIJFF_PENALTY_CARDS_ERROR_TOO_MANY'		=> 'User has too many yellow cards; issue a red card!',
	'MCP_AKRUIJFF_PENALTY_CARDS_ERROR_WINDOW'		=> 'Post is too old.',

	'AKRUIJFF_PENALTY_CARDS_VIEW'				=> 'view',
	'AKRUIJFF_PENALTY_CARDS_EDIT'				=> 'edit', 
	'AKRUIJFF_PENALTY_CARDS_DELETE'				=> 'delete',

	'MCP_AKRUIJFF_PENALTY_CARDS_YELLOW_CARD_ISSUED'		=> 'Yellow card issued',
	'MCP_AKRUIJFF_PENALTY_CARDS_RED_CARD_ISSUED'		=> 'Red card issued',

	'MCP_AKRUIJFF_PENALTY_CARDS_YELLOW_CARD_SUBJECT'	=> 'Yellow card for post',
	'MCP_AKRUIJFF_PENALTY_CARDS_YELLOW_CARD_MESSAGE'	=> 'You have received a yellow card for this post: %s.',
));
