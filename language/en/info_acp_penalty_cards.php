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
	'ACL_M_AKRUIJFF_PENALTY_CARDS_WARN'	=> 'Can issue yellow cards (warning)',
	'ACL_M_AKRUIJFF_PENALTY_CARDS_BAN'	=> 'Can issue red cards (warning)',

	'ACP_AKRUIJFF_PENALTY_CARDS_CATAGORY'		=> 'Penalty Cards',
	'ACP_AKRUIJFF_PENALTY_CARDS_SETTINGS'		=> 'Settings',
	'ACP_AKRUIJFF_PENALTY_CARDS_SETTINGS_TITLE'	=> 'Penalty Cards Settings',

	'ACP_AKRUIJFF_PENALTY_CARDS_MAX_YELLOW_CARDS'	=> 'Maximum number of yellow cards',
	'ACP_AKRUIJFF_PENALTY_CARDS_DURATION1'			=> 'Card 1 duration',
	'ACP_AKRUIJFF_PENALTY_CARDS_DURATION2'			=> 'Card 2 duration',
	'ACP_AKRUIJFF_PENALTY_CARDS_DURATION3'			=> 'Card 3 duration',
	'ACP_AKRUIJFF_PENALTY_CARDS_WINDOW'				=> 'Punishable period',

	'ACP_AKRUIJFF_PENALTY_CARDS_NOTIFICATION_SENDER'	=> 'Send notification as',
	'ACP_AKRUIJFF_PENALTY_CARDS_NOTIFIACTION_DEFAULT'	=> 'Set default',
));
