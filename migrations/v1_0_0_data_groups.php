<?php
/**
 * 
 * Prime Ban to Group
 * 
 * @copyright (c) 2014 Wolfsblut ( www.pinkes-forum.de )
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 * @author Clemens Husung (Wolfsblvt)
 * 
 * Original code by primehalo (https://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=183323)
 * Thanks to him for let me convert his MOD.
 */

namespace wolfsblvt\primebantogroup\migrations;

class v1_0_0_data_groups extends \phpbb\db\migration\migration
{	
	protected $groups = array(
		'BANNED_USERS',
		'SUSPENDED_USERS',
		'INACTIVE_USERS',
	);
	
	public static function depends_on()
	{
		return array('\wolfsblvt\primebantogroup\migrations\v1_0_0_configs');
	}
	
	public function effectively_installed()
	{
		$sql_query = 'SELECT COUNT(*) as total
						FROM ' . GROUPS_TABLE . '
						WHERE ' . $this->db->sql_in_set('group_name', $this->groups);
		$result = $this->db->sql_query($sql_query);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);
		
		return ($total == count($this->groups));
	}
	
	public function update_data()
	{
		return array(
			array('custom', array(
				array(&$this, 'add_groups')
			)),
		);
	}

	public function revert_data()
	{
		return array(
			array('custom', array(
				array(&$this, 'delete_groups')
			)),
		);
	}
	
	/**
	 * Adds the banned and suspended group to the groups table
	 * 
	 * @return void
	 */
	public function add_groups()
	{
		if ($this->effectively_installed())
			return true;
		
		$group_data = array();
		
		foreach ($this->groups as $group_name)
		{
			$group_data[] = array(
				'group_type' => 3,
				'group_name' => $group_name,
				'group_desc' => '',
			);
		}
		
		$this->db->sql_multi_insert(GROUPS_TABLE, $group_data);
	}
	
	/**
	 * Summary of delete_groups
	 */
	public function delete_groups()
	{
		// include for function
		if(!function_exists('group_delete'))
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		
		// Get the group_ids
		$sql = 'SELECT group_id, group_name
					FROM ' . GROUPS_TABLE . '
					WHERE ' . $this->db->sql_in_set('group_name', $this->groups);
		$result = $this->db->sql_query($sql);
		$group_data = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_data[] = $row;
		}
		$this->db->sql_freeresult($result);
		
		if(empty($group_data))
			return true;
		
		// Delete group by group and start migration again if needed, to prevent timeouts
		group_delete($group_data[0]['group_id'], $group_data[0]['group_name']);
		
		return (count($group_data) > 1) ? false : true;
	}
}
