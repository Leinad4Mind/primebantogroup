<?php
/**
 * 
 * Prime Ban to Group [Swedish]
 * Swedish translation by Holger (http://www.maskinisten.net)
 * 
 * @copyright (c) 2014 Wolfsblvt ( www.pinkes-forum.de )
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 * @author Clemens Husung (Wolfsblvt)
 * 
 * Original code by primehalo (https://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=183323)
 * Thanks to him for let me convert his MOD.
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'PBTG_TITLE_ACP'				=> 'Banna till Grupp',
	'PBTG_SETTINGS_ACP'				=> 'Inställningar',

	'PBTG_TITLE'					=> 'Banna till Grupp',
	'PBTG_TITLE_EXPLAIN'			=> 'Lägger till användare till speciella grupper om de bannas temporärt eller permanent. På detta sätt kan dessa användare framhävas visuellt genom gruppegenskaperna, t.ex. med en egen rankbild. Användarna avlägsnas ur gruppen om banningen hävs manuellt eller automatiskt. Användare som redan är bannade när du installerar detta tillägg kan enkelt tillordnas de nya grupperna genom resynkronisering av grupperna.',

	'PBTG_SETTINGS'					=> 'Banna till Grupp - inställningar',

	'PBTG_RESYNC'					=> 'Resynkronisera grupper',
	'PBTG_RESYNC_EXPLAIN'			=> 'Resynkroniserar grupperna för bannade, suspenderade och inaktiva användare. Alla användare som ej hör till dessa grupper avlägsnas ur grupperna och alla som hör till grupperna läggs till. Utför detta efter deaktivering eller aktivering av gruppen för inaktiva användare, efter första aktiveringen av detta tillägg samt efter temporär deaktivering av tillägget.',
	'PBTG_CHECK'					=> 'Intervall för gruppkontroll genom Cron Task',
	'PBTG_CHECK_EXPLAIN'			=> 'Ställ in intervallet för den Cron Task som kontrollerar grupperna (standard: 600).',
	'PBTG_ACT_INACTIVE'				=> 'Aktivera grupp för inaktiva användare',
	'PBTG_ACT_INACTIVE_EXPLAIN'		=> 'Aktiverar gruppen för inaktiva användare. Beakta att du måste resynkronisera gruppen om du ändrar inställningarna.',

	'PBTG_RESYNC_SUCCESS'			=> 'Resynkroniseringen har utförts.',
	'PBTG_INVALID'					=> 'Ogiltig aktion.',
));
