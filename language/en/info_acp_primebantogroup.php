<?php
/**
 * 
 * Prime Ban to Group [English]
 * 
 * @copyright (c) 2014 Wolfsblut ( www.pinkes-forum.de )
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
	'PBTG_TITLE_ACP'			=> 'Prime Ban to Group',
	'PBTG_SETTINGS'				=> 'Settings',
	'PBTG_EXPLAIN'				=> 'Automatically moves users into a special group when they are banned or suspended. This allows you to visually indicate their status through the use of group attributes, such as a custom rank image. Users will be removed from the group when they are manually unbanned or when their temporary ban has ended. Users who are already banned or suspended when this extension is installed can be moved to the group if you resync the groups.',
));
