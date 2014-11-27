<?php
/**
 * 
 * Prime Ban to Group
 * 
 * @copyright (c) 2014 Wolfsblut ( www.pinkes-forum.de )
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 * @author Clemens Husung (Wolfsblvt)
 */

namespace wolfsblvt\primebantogroup\cron\task;

class cron_task_resync_banned_groups extends \phpbb\cron\task\base
{
	/** @var \wolfsblvt\primebantogroup\core\primebantogroup */
	protected $primeban;
	
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	
	/** @var \phpbb\config\config */
	protected $config;

	/** @var string phpBB root path  */
	protected $root_path;

	/** @var string PHP file extension */
	protected $php_ext;

	/**
	 * Constructor
	 * 
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\config\config $config
	 * @param string $root_path
	 * @param string $php_ext
	 * @return \wolfsblvt\primebantogroup\cron\task\cron_task_primebantogroup
	 * @access public
	 */
	public function __construct(\wolfsblvt\primebantogroup\core\primebantogroup $primeban, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, $root_path, $php_ext)
	{
		$this->primeban = $primeban;
		$this->db = $db;
		$this->config = $config;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		
		$this->ext_root_path = 'ext/wolfsblvt/primebantogroup';
	}
	
	/**
	 * Runs this cron task.
	 *
	 * @return null
	 */
	public function run()
	{
		$config_inactive = $this->config['wolfsblvt.primebantogroup.inactive_group'];
		
		// Remove users from groups wich are unbanned
		$this->primeban->ban_to_group(null, 'unban');
		
		// Remove users wich are no more inactive
		// TODO: inactive users remove (if inactive is disabled, remove all)
		$inactive_group = $this->primeban->get_group_data($this->primeban->INACTIVE_GROUP_NAME);
		
		$sql = 'SELECT ug.user_id, u.user_type
				FROM ' . USER_GROUP_TABLE . ' ug
				LEFT JOIN ' . USERS_TABLE . ' u ON (ug.user_id = u.user_id)
				WHERE group_id = ' . $inactive_group['group_id'];
		$result = $this->db->sql_query($sql);
		$data = array('user_ids' => array(), 'usernames' => array());
		while ($row = $this->db->sql_fetchrow($result))
		{
			if(!$config_inactive || $row['user_type'] != USER_INACTIVE)
			{
				$data['user_ids'][] = $row['user_id'];
				$data['usernames'][] = $row['username'];
			}
		}
		$this->db->sql_freeresult($result);
		
		$this->primeban->group_inactive_users($data['user_ids'], 'remove', $data['usernames']);
		
		
		
		// Lets make one thing sure first. I'll not check if users are already in the group and will be added again in this function.
		// The phpBB group_user_add function checks that anyway, so no need here.
		// Why would you do work twice? (:		
		
		$users_to_groups = array(
			$this->primeban->BANNED_GROUP_NAME		=> array('user_ids' => array(), 'usernames' => array()),
			$this->primeban->SUSPENDED_GROUP_NAME	=> array('user_ids' => array(), 'usernames' => array()),
			$this->primeban->INACTIVE_GROUP_NAME	=> array('user_ids' => array(), 'usernames' => array()),
		);
		
		// Get the users that needs to be added in groups
		$sql = 'SELECT u.user_id, u.username, ban_userid, b.ban_end
				FROM ' . USERS_TABLE . ' u
				LEFT JOIN ' . BANLIST_TABLE . ' b ON (u.user_id = b.ban_userid)
				WHERE u.user_type <> ' . USER_IGNORE . ' 
					AND (
						( b.ban_end = 0 OR b.ban_end > ' . time() . ' )
						OR u.user_type = ' . USER_INACTIVE . '
					)';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group = false;
			if ($row['ban_userid'])
			{
				$group = ($row['ban_end'] == 0) ? $this->primeban->BANNED_GROUP_NAME : $this->primeban->SUSPENDED_GROUP_NAME;
			}
			else if ($config_inactive && $row['user_type'] == USER_INACTIVE)
			{
				$group = $this->primeban->INACTIVE_GROUP_NAME;
			}
			
			if ($group)
			{
				$users_to_groups[$group]['user_ids'][] = $row['user_id'];
				$users_to_groups[$group]['usernames'][] = $row['username'];
			}
		}
		$this->db->sql_freeresult($result);
		
		// Add inacitve users (if wished)
		$data = $users_to_groups[$this->primeban->INACTIVE_GROUP_NAME];
		if ($config_inactive && !empty($data['user_ids']))
		{
			$this->primeban->group_inactive_users($data['user_ids'], 'add', $data['usernames']);
		}
		
		// Add fully banned users
		$data = $users_to_groups[$this->primeban->BANNED_GROUP_NAME];
		if (!empty($data['user_ids']))
		{
			$this->primeban->ban_to_group($data['user_ids'], 0, 'user', $data['usernames']);
		}
		
		// Add suspended users
		$data = $users_to_groups[$this->primeban->SUSPENDED_GROUP_NAME];
		if (!empty($data['user_ids']))
		{
			$this->primeban->ban_to_group($data['user_ids'], 1, 'user', $data['usernames']);
		}
		
		$this->config->set('wolfsblvt.primebantogroup.resync_last_gc', time());
	}
	
	/**
	 * Returns whether this cron task can run, given current board configuration.
	 *
	 * @return bool
	 */
	public function is_runnable()
	{
		return true; // Can run everytime you want
	}
	
	/**
	 * Returns whether this cron task should run now, because enough time
	 * has passed since it was last run.
	 *
	 * @return bool
	 */
	public function should_run()
	{
		return false; // Should run just manually if triggered
	}
}
