<?php
/**
*
* Precise Similar Topics
*
* @copyright (c) 2013 Matt Friedman
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace wolfsblvt\primebantogroup\acp;

/**
* @package module_install
*/
class primebantogroup_info
{
	function module()
	{
		return array(
			'filename'	=> '\wolfsblvt\primebantogroup\acp\primebantogroup_module',
			'title'		=> 'PBTG_TITLE_ACP',
			'modes'		=> array(
				'settings'	=> array('title' => 'PBTG_SETTINGS', 'auth' => 'ext_wolfsblvt\primebantogroup && acl_a_board', 'cat' => array('PBTG_TITLE_ACP')),
			),
		);
	}
}
