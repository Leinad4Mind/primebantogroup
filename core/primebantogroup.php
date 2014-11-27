<?php
/**
 * 
 * Prime Ban to Group
 * 
 * @copyright (c) 2014 Wolfsblut ( www.pinkes-forum.de )
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 * @author Clemens Husung (Wolfsblvt)
 */

namespace wolfsblvt\primebantogroup\core;

class primebantogroup
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	
	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path  */
	protected $root_path;

	/** @var string PHP file extension */
	protected $php_ext;
	
	
	/** @internal readonly! */
	public $BANNED_GROUP_NAME = 'BANNED_USERS';
	public $SUSPENDED_GROUP_NAME = 'SUSPENDED_USERS';
	public $INACTIVE_GROUP_NAME = 'INACTIVE_USERS';

	/**
	 * Constructor
	 * 
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\user $user
	 * @param string $root_path
	 * @param string $php_ext
	 * @return \wolfsblvt\primebantogroup\core\primebantogroup
	 * @access public
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $root_path, $php_ext)
	{
		$this->db = $db;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		
		$this->ext_root_path = 'ext/wolfsblvt/primebantogroup';
		
		// Add language vars
		$this->user->add_lang_ext('wolfsblvt/primebantogroup', 'primebantogroup');
	}
	
	/**
	 * Primary function to add and remove users from banned group
	 * 
	 * @param array $ids The array of user ids.
	 * @param int|'unban' $ban_len Either the length of the ban or string 'unban' to unban
	 * @param string $mode The mode of the ban. Either 'user', 'email' or empty.
	 * @param array $usernames An optional array of usernames.
	 * @return void
	 */
	public function ban_to_group($ids = null, $ban_len = 0, $mode = '', $usernames = false)
	{
		if ($mode == 'user' || $mode == 'email' || $mode == '')
		{
			if (is_int($ban_len))
			{
				$this->group_users_add(($ban_len ? $this->SUSPENDED_GROUP_NAME : $this->BANNED_GROUP_NAME), $ids, $usernames);
			}
			else if ($ban_len == 'unban')
			{
				if (empty($ids) || $mode === '') // remove stale bans
				{
					$sql = 'SELECT u.user_id, u.username, b.ban_end'
						 . '	FROM ' . USERS_TABLE . ' u, ' . BANLIST_TABLE . ' b'
						 . '	WHERE ban_end < ' . time()
						 . '		AND ban_end <> 0'
						 . '		AND u.user_type <> ' . USER_IGNORE
						 . '		AND u.user_id = b.ban_userid';
				}
				else
				{
					$sql = 'SELECT u.user_id, u.username, b.ban_end'
						 . '	FROM ' . USERS_TABLE . ' u, ' . BANLIST_TABLE . ' b'
						 . '	WHERE ' . $this->db->sql_in_set('b.ban_id', $ids)
						 . '		AND ' . ($mode == 'email' ? 'u.user_email = b.ban_email' : 'u.user_id = b.ban_userid');
				}
				$result = $this->db->sql_query($sql);
				$ids = array();
				while ($row = $this->db->sql_fetchrow($result))
				{
					$type = $row['ban_end'] ? $this->SUSPENDED_GROUP_NAME : $this->BANNED_GROUP_NAME;
					$ids[$type][] = $row['user_id'];
					$names[$type][] = $row['username'];
				}
				$this->db->sql_freeresult($result);
				foreach ($ids as $group_name => $user_ids)
				{
					$this->group_users_del($group_name, $user_ids, $names[$group_name]);
				}
			}
		}
	}

	/**
	 * Adds or removes users from group inactive
	 * 
	 * @param int|array $user_ids user_id or array of user_ids
	 * @param string $action Can either be 'add' or 'remove'
	 * @param array $usernames Optional usernames
	 * @return bool successfull or not
	 */
	public function group_inactive_users($user_ids, $action = 'add', $usernames = false) {
		if (!empty($user_ids))
		{
			if ($action == 'add')
			{
				$this->group_users_add($this->INACTIVE_GROUP_NAME, $user_ids, $usernames);
				return true;
			}
			else if ($action == 'remove')
			{
				$this->group_users_del($this->INACTIVE_GROUP_NAME, $user_ids, $usernames);
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns data of a specific group
	 * 
	 * @param string $group_name The name of the group
	 * @return array Group data
	 */
	public function get_group_data($group_name)
	{
		$sql = 'SELECT * '
			 . ' FROM ' . GROUPS_TABLE . ' '
			 . " WHERE group_name='" . $this->db->sql_escape($group_name) . "'"
		;
		$result = $this->db->sql_query($sql);
		$group_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $group_data;
	}

	/**
	 * Add user(s) to group
	 * 
	 * @param string $group_name The name of the group
	 * @param array $user_ids Array of user_ids wich should be added
	 * @param array $usernames Optional usernames
	 * @return bool successfull or not
	 */
	private function group_users_add($group_name, $user_ids, $usernames = false)
	{
		// include for function
		if(!function_exists('group_user_add'))
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		
		$group_data	= $this->get_group_data($group_name);
		$group_id	= isset($group_data['group_id']) ? $group_data['group_id'] : null;
		if (!empty($group_id))
		{
			$group_name = ($group_data['group_type'] == GROUP_SPECIAL && !empty($this->user->lang['G_' . $group_data['group_name']])) ? $this->user->lang['G_' . $group_data['group_name']] : $group_name;
			group_user_add($group_id, $user_ids, $usernames, $group_name, true, 0, 0, $group_data);
			return true;
		}
		return false;
	}


	/**
	 * Remove a user/s from a given group. When we remove users we update their
	 * default group_id. We do this by examining which "special" groups they belong
	 * to. The selection is made based on a reasonable priority system
	 * 
	 * @param string $group_name The name of the group
	 * @param array $user_ids Array of user_ids wich should be deleted
	 * @param array $usernames Optional usernames
	 * @return bool successfull or not
	 */
	private function group_users_del($group_name, $user_ids, $usernames = false)
	{
		// include for function
		if(!function_exists('group_user_del'))
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		
		$group_data	= $this->get_group_data($group_name);
		$group_id	= isset($group_data['group_id']) ? (int)$group_data['group_id'] : null;
		if (!empty($group_id))
		{
			// Get users belonging to the group from which we want them removed (only needed to prevent log entries for removing users that don't even belong to the group)
			$sql = 'SELECT user_id'
				 . ' FROM ' . USER_GROUP_TABLE
				 . ' WHERE group_id = ' . $group_id
				 . ' 	AND ' . $this->db->sql_in_set('user_id', (array)$user_ids)
			;
			$result = $this->db->sql_query($sql);
			$user_ids = array(); // clear out the user_ids array
			while ($row = $this->db->sql_fetchrow($result))
			{
				$user_ids[] = $row['user_id'];
			}
			$this->db->sql_freeresult($result);
			if (!empty($user_ids))
			{
				remove_default_rank($group_id, $user_ids);
				$group_name = ($group_data['group_type'] == GROUP_SPECIAL && !empty($this->user->lang['G_' . $group_data['group_name']])) ? $this->user->lang['G_' . $group_data['group_name']] : $group_name;
				group_user_del($group_id, $user_ids, $usernames, $group_name, 0, 0, $group_data);
				return true;
			}
		}
		return false;
	}
}
