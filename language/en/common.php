<?php
/**
 *
 * Penalty Cards. An extension For the phpBB Forum Software package.
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
	'LOG_AKRUIJFF_PENALTY_CARDS_ISSUED_RED_CARD_REASON'		=> '<strong>Red card issued to %s for %d days</strong><br>» %s',
	'LOG_AKRUIJFF_PENALTY_CARDS_ISSUED_YELLOW_CARD_REASON'	=> '<strong>Yellow card issued to %s for %d days</strong><br>» %s',
	'LOG_AKRUIJFF_PENALTY_CARDS_ISSUED_RED_CARD'			=> '<strong>Red card issued to %s for %d days</strong>',
	'LOG_AKRUIJFF_PENALTY_CARDS_ISSUED_YELLOW_CARD'			=> '<strong>Yellow card issued to %s for %d days</strong>',

	'AKRUIJFF_PENALTY_CARDS_INDEX_TITLE'	=> 'Penalty Cards',
	'AKRUIJFF_PENALTY_CARDS_NO_ENTRIES'		=> 'No card entries',
	'AKRUIJFF_PENALTY_CARDS_YELLOW_CARD'	=> 'Yellow card',
	'AKRUIJFF_PENALTY_CARDS_RED_CARD'		=> 'Red card',

	'AKRUIJFF_PENALTY_CARDS_ISSUED' => array(
		1 => '%4$s issued for %1$d day until %3$s',
		2 => '%4$s issued for %1$d days until %3$s',
	),

	'AKRUIJFF_PENALTY_CARDS'					=> 'Cards',
	'AKRUIJFF_PENALTY_CARDS_TYPE'				=> 'Card type',
	'AKRUIJFF_PENALTY_CARDS_DURATION'			=> 'Duration',
	'AKRUIJFF_PENALTY_CARDS_EXPIRATION_DATE'	=> 'Expiration date',
	'AKRUIJFF_PENALTY_CARDS_REASON'				=> 'Reason',
	'AKRUIJFF_PENALTY_CARDS_REASON_SHOWN'		=> 'Reason shown',
	'AKRUIJFF_PENALTY_CARDS_ACTIONS'			=> 'Actions',
	'AKRUIJFF_PENALTY_CARDS_DURATION_PERMANENT'	=> 'Permanent',
	'AKRUIJFF_PENALTY_CARDS_NEVER'				=> 'Never',

	'AKRUIJFF_PENALTY_CARDS_DURATION_VALUE'		=> array(
		0 => 'Permanent',
		1 => '%d day',
		2 => '%d days',
	),
));
