<?php
/**
 * Contains the auth-class
 * 
 * @version			$Id: auth.php 741 2008-05-24 12:04:56Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The auth-class is used for all kind of authorisation-tasks. It stores the permissions of the
 * current user depending on the usergroups, moderator status in current forum and so on.
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Auth extends PLIB_FullObject implements PLIB_Initable
{
	/**
	 * User-group permissions
	 *
	 * @var array
	 */
	private $_user_group_perm;
	
	/**
	 * The forum-permissions
	 *
	 * @var array
	 */
	private $_forum_perm = null;

	/**
	 * The permissions for the current forum (view, reply, ...)
	 *
	 * @var array
	 */
	private $_current_forum_perm = null;
	
	/**
	 * Stores wether this user is a moderator in any forum
	 *
	 * @var boolean
	 */
	private $_is_mod_in_any_forum = false;

	/**
	 * Is this user a moderator in the current forum?
	 *
	 * @var boolean
	 */
	private $_is_current_forum_mod = false;
	
	public function init()
	{
		$this->_calculate_group_perm();

		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		if($fid !== null && $fid > 0)
		{
			if($this->user->is_loggedin() && !$this->user->is_admin())
			{
				// we have to do this here manually because the is_moderator_in_current_forum() method
				// uses this field to determine if the user is a mod
				$ismod = $this->cache->get_cache('moderators')->element_exists_with(array(
					'rid' => $fid,'user_id' => $this->user->get_user_id()
				));
				$this->_is_current_forum_mod = $this->_user_group_perm['is_super_mod'] == 1 || $ismod;
				
				if($this->_is_current_forum_mod)
					$this->_is_mod_in_any_forum = true;
				else
				{
					// check if he/she is a moderator in any forum
					$ismodany = $this->cache->get_cache('moderators')->element_exists_with(array(
						'user_id' => $this->user->get_user_id())
					);
					$this->_is_mod_in_any_forum = $this->_user_group_perm['is_super_mod'] == 1 || $ismodany;
				}
			}
		}
	}

	/**
	 * checks wether the ip-block of the given type is enabled for the current user
	 *
	 * @param string $type the type of ip-block (see the bs_config table)
	 * @return boolean true if the ip-block is enabled
	 */
	public function is_ipblock_enabled($type)
	{
		return $this->cfg[$type] > 0 && $this->_user_group_perm['disable_ip_blocks'] == 0;
	}
	
	/**
	 * Checks wether the current user or the given one is moderator in the current forum
	 * 
	 * @param int $user_id the id of th user (0 = current)
	 * @param string $group_ids a comma-separated string with all groups of the user (just if
	 * 	$user_id != 0)
	 * @return boolean true if the user is a moderator in the current forum
	 */
	public function is_moderator_in_current_forum($user_id = 0,$group_ids = '')
	{
		// current user?
		if($user_id == 0)
			return $this->_is_current_forum_mod;
		
		// check for the given user-id and user-groups
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$is_mod = $this->cache->get_cache('moderators')->element_exists_with(array(
			'rid' => $fid,'user_id' => $user_id
		));
		if($is_mod)
			return true;
		
		// is one of the groups super-mod?
		$ugroups = $this->cache->get_cache('user_groups');
		foreach(PLIB_Array_Utils::advanced_explode(',',$group_ids) as $group_id)
		{
			$gdata = $ugroups->get_element($group_id);
			if($gdata['is_super_mod'] == 1)
				return true;
		}
		
		return false;
	}

	/**
	 * Checks wether the given user or the current user is a moderator in any forum.
	 * 
	 * @param int $user_id the id of the user (0 = current user)
	 * @param string $group_ids the usergroups of the user. (just if $user_id != 0)
	 * @return boolean true if this user is a moderator in any forum
	 */
	public function is_moderator_in_any_forum($user_id = 0,$group_ids = '')
	{
		// the current user?
		if($user_id == 0)
			return $this->_is_mod_in_any_forum;
		
		// is one of the groups super-mod?
		$ugroups = $this->cache->get_cache('user_groups');
		foreach(PLIB_Array_Utils::advanced_explode(',',$group_ids) as $group_id)
		{
			$gdata = $ugroups->get_element($group_id);
			if($gdata['is_super_mod'] == 1)
				return true;
		}
		
		// mod in a forum?
		// TODO this is very slow. perhaps should we organize the moderators more clever?
		if($this->cache->get_cache('moderators')->element_exists_with(array('user_id' => $user_id)))
			return true;

		return false;
	}
	
	/**
	 * Builds a list with all group-ids of the given group-ids
	 * 
	 * @param string $group_ids all group-ids separated by ","
	 * @param boolean $add_links do you want to add links?
	 * @param boolean $one_line do you want to add all groups in one line?
	 * @param boolean $show_hidden do you want to show hidden-groups?
	 * @return string the user-group-list
	 */
	public function get_usergroup_list($group_ids,$add_links = true,$one_line = true,
		$show_hidden = false)
	{
		$user_groups = '';
		$ugroups = PLIB_Array_Utils::advanced_explode(",",$group_ids);
		$i = 0;
		foreach($ugroups as $gid)
		{
			$gdata = $this->cache->get_cache('user_groups')->get_element($gid);
			if(!$show_hidden && $gdata['is_visible'] == 0)
				continue;
			
			if($add_links)
			{
				$url = $this->url->get_url('memberlist','&amp;'.BS_URL_MS_GROUP.urlencode('[]').'='.$gid);
				$group = '<a href="'.$url.'" style="color: #'.$gdata['group_color'].';">';
				$group .= $gdata['group_title'].'</a>';
			}
			else
				$group = '<span style="color: #'.$gdata['group_color'].';">'.$gdata['group_title'].'</span>';
			
			if($i == 0)
			{
				$user_groups .= '<b>'.$group.'</b>';
				if($one_line)
					$user_groups .= ', ';
				else
					$user_groups .= '<br />  ';
			}
			else
				$user_groups .= $group.', ';
			
			$i++;
		}
		
		return PLIB_String::substr($user_groups,0,-2);
	}

	/**
	 * retrieves the name of the group with given id
	 *
	 * @param int $group_id the id of the group
	 * @return string name of the group
	 */
	public function get_groupname($group_id)
	{
		$data = $this->cache->get_cache('user_groups')->get_element($group_id);
		if($data != null)
			return $data['group_title'];

		return '';
	}
	
	/**
	 * builds a colored group-name depending on the group-color
	 * 
	 * @param int $group_id the id of the usergroup
	 * @return string the colored groupname
	 */
	public function get_colored_groupname($group_id)
	{
		$gdata = $this->cache->get_cache('user_groups')->get_element($group_id);
		return '<span style="color: #'.$gdata['group_color'].';">'.$gdata['group_title'].'</span>';
	}
	
	/**
	 * Builds a colored username depending on the usergroup and status of the user
	 * 
	 * @param int $id the id of the user
	 * @param string $name the name of the user
	 * @param string $group_ids the ids of the usergroups of the user
	 * @return string the colored username
	 */
	public function get_colored_username($id,$name,$group_ids)
	{
		$color = $this->get_user_color($id,$group_ids);
		return '<span style="color: #'.$color.';">'.$name.'</span>';
	}

	/**
	 * retrieves the group-color of the user with given id
	 *
	 * @param int $id the id of the user
	 * @param string $group_ids the user-group-ids of the user
	 * @return string color of the group
	 */
	public function get_user_color($id,$group_ids)
	{
		$gdata = $this->cache->get_cache('user_groups')->get_element((int)$group_ids);
		if($gdata['overrides_mod'] == 0 && $this->is_moderator_in_any_forum($id,$group_ids))
			return $this->cfg['mod_color'];

		return $gdata['group_color'];
	}

	/**
	 * retrieves the group-images for the user with given id
	 *
	 * @param int $id the id of the user
	 * @param string $group_ids the user-group-ids of the user
	 * @return array an associative array of the form:
	 * 	<code>
	 * 		array(
	 * 			'is_mod' => <isMod>,
	 * 			'filled' => <filledImage>,
	 * 			'empty' => <emptyImage>
	 * 		)
	 * 	</code>
	 */
	public function get_user_images($id,$group_ids)
	{
		$ugroups = $this->cache->get_cache('user_groups');
		$gdata = $ugroups->get_element((int)$group_ids);

		if($gdata['overrides_mod'] == 0 && $this->is_moderator_in_any_forum($id,$group_ids))
		{
			return array(
				'is_mod' => true,
				'filled' => $this->cfg['mod_rank_filled_image'],
				'empty' => $this->cfg['mod_rank_empty_image']
			);
		}

		$filled = $gdata['group_rank_filled_image'];
		if($filled == '')
		{
			$usergdata = $ugroups->get_element(BS_STATUS_USER);
			$filled = $usergdata['group_rank_filled_image'];
		}

		$empty = $gdata['group_rank_empty_image'];
		if($empty == '')
		{
			$usergdata = $ugroups->get_element(BS_STATUS_USER);
			$empty = $usergdata['group_rank_empty_image'];
		}

		return array(
			'is_mod' => false,
			'filled' => $filled,
			'empty' => $empty
		);
	}

	/**
	 * checks wether the user can do the given action in the current forum
	 *
	 * @param int $action the action the user wants to perform: see config.php entries MODE_*
	 * @param int $post_user the user-id of the owner of the corresponding stuff
	 * @return boolean true if the user has permission
	 */
	public function has_current_forum_perm($action,$post_user = 0)
	{
		// the admin has always permission
		if($this->user->is_admin())
			return true;

		switch($action)
		{
			case BS_MODE_START_TOPIC:
			case BS_MODE_START_POLL:
			case BS_MODE_START_EVENT:
			case BS_MODE_REPLY:
				$this->_init_forum_perm();
				return isset($this->_current_forum_perm[$action]) && $this->_current_forum_perm[$action];

			case BS_MODE_EDIT_OWN_TOPICS:
				return $this->_user_group_perm['edit_own_threads'] == 1;

			case BS_MODE_EDIT_TOPIC:
				if($this->cfg['mod_edit_topics'] == 1 && $this->is_moderator_in_current_forum())
					return true;

				if($post_user != 0)
				{
					if($this->user->get_user_id() == $post_user &&
						$this->_user_group_perm['edit_own_threads'] == 1)
						return true;
				}
				return false;

			case BS_MODE_DELETE_TOPICS:
				if($this->cfg['mod_delete_topics'] == 1 && $this->is_moderator_in_current_forum())
					return true;

				if($post_user != 0)
				{
					if($this->user->get_user_id() == $post_user &&
						$this->_user_group_perm['delete_own_threads'] == 1)
						return true;
				}
				return false;

			case BS_MODE_MOVE_TOPICS:
				return $this->cfg['mod_move_topics'] == 1 && $this->is_moderator_in_current_forum();

			case BS_MODE_EDIT_POST:
				if($this->cfg['mod_edit_posts'] == 1 && $this->is_moderator_in_current_forum())
					return true;

				return $this->user->get_user_id() == $post_user &&
					$this->_user_group_perm['edit_own_posts'] == 1;

			case BS_MODE_DELETE_POSTS:
				if($this->cfg['mod_delete_posts'] == 1 && $this->is_moderator_in_current_forum())
					return true;

				if($post_user != 0)
					return $this->user->get_user_id() == $post_user &&
						$this->_user_group_perm['delete_own_posts'] == 1;

				return false;

			case BS_MODE_SPLIT_POSTS:
				return $this->cfg['mod_split_posts'] == 1 && $this->is_moderator_in_current_forum();

			case BS_MODE_OPENCLOSE_TOPICS:
				if($this->cfg['mod_openclose_topics'] == 1 && $this->is_moderator_in_current_forum())
					return true;

				return $this->user->get_user_id() == $post_user &&
					$this->_user_group_perm['openclose_own_threads'] == 1;
			
			case BS_MODE_LOCK_TOPICS:
				return $this->cfg['mod_lock_topics'] == 1 && $this->is_moderator_in_current_forum();
			
			case BS_MODE_MARK_TOPICS_IMPORTANT:
				return $this->cfg['mod_mark_topics_important'] == 1 && $this->is_moderator_in_current_forum();
		}

		return false;
	}
	
	/**
	 * Returns all permissions for the given forum and action
	 *
	 * @param string $action the action to perform (BS_MODE_REPLY, BS_MODE_START_TOPIC, ...)
	 * @param int $fid the id of the forum
	 * @return array all groups that have access
	 */
	public function get_permissions_in_forum($action,$fid)
	{
		$this->_init_forum_perm();
		
		if(isset($this->_forum_perm[$fid][$action]))
			return $this->_forum_perm[$fid][$action];
		
		return array();
	}

	/**
	 * checks if this user has the given permission for the forum with given id
	 *
	 * @param string $action the action to perform (BS_MODE_REPLY, BS_MODE_START_TOPIC, ...)
	 * @param int $fid the id of the forum
	 * @return true if the user has permission
	 */
	public function has_permission_in_forum($action,$fid)
	{
		// admins have always permission!
		if($this->user->is_admin())
			return true;
		
		$this->_init_forum_perm();
		
		if(isset($this->_forum_perm[$fid][$action]))
			return $this->is_in_any_group($this->_forum_perm[$fid][$action]);
		
		return false;
	}
	
	/**
	 * Determines wether the given group-string contains the given group
	 *
	 * @param string $groups the group-string
	 * @param int $group the group
	 * @return boolean true if <var>$group</var> exists in <var>$groups</var>
	 */
	public function is_in_group($groups,$group)
	{
		return in_array($group,PLIB_Array_Utils::advanced_explode(',',$groups));
	}
	
	/**
	 * Checks wether the current user is in any of the given groups
	 *
	 * @param array $groups the group-ids
	 * @return boolean true if so
	 */
	public function is_in_any_group($groups)
	{
		return count(array_intersect($this->user->get_all_user_groups(),$groups)) > 0;
	}

	/**
	 * checks wether the user has permission for the given operation
	 *
	 * @param string $operation the operation you want to perform (see "bs_user_groups"-table)
	 * @return boolean true if the user has permission
	 */
	public function has_global_permission($operation)
	{
		// the admin has always permission
		if($this->user->is_admin())
			return true;

		return $this->_user_group_perm[$operation] == 1;
	}
	
	/**
	 * Determines if the user has access to the board
	 *
	 * @return boolean true if so
	 */
	public function has_board_access()
	{
		if(!$this->has_global_permission('enter_board'))
			return false;
		else if($this->user->is_bot())
		{
			$bot = $this->user->get_bot_data();
			if($bot !== null && !$bot['bot_access'])
				return false;
		}
		
		return true;
	}

	/**
	 * determines if the current user has access to the ACP
	 *
	 * @return boolean true if the user has access
	 */
	public function has_acp_access()
	{
		if($this->user->is_admin())
			return true;

		$acpaccess = $this->cache->get_cache('acp_access');
		
		// has the user access to acp?
		$cond = array('access_type' => 'user','access_value' => $this->user->get_user_id());
		if($acpaccess->element_exists_with($cond))
			return true;

		// has any of the user-groups the user belongs to access to the acp?
		foreach($this->user->get_all_user_groups() as $gid)
		{
			$cond = array('access_type' => 'group','access_value' => $gid);
			if($acpaccess->element_exists_with($cond))
				return true;
		}

		return false;
	}

	/**
	 * checks wether the user has access to the given module
	 *
	 * @param string $module the name of the module
	 * @return boolean true if the user has access
	 */
	public function has_access_to_module($module)
	{
		if($this->user->is_admin() || $module == 'faq')
			return true;
		
		$acpaccess = $this->cache->get_cache('acp_access');

		// has the user access to acp?
		$cond = array(
			'access_type' => 'user',
			'access_value' => $this->user->get_user_id(),
			'module' => $module
		);
		if($acpaccess->element_exists_with($cond))
			return true;

		// has any of the user-groups the user belongs to access to the acp?
		foreach($this->user->get_all_user_groups() as $gid)
		{
			$cond = array(
				'access_type' => 'group',
				'access_value' => $gid,
				'module' => $module
			);
			if($acpaccess->element_exists_with($cond))
				return true;
		}

		return false;
	}

	/**
	 * checks wether this user has access to the forum with given id
	 * if the id is 0 the current forum will be used
	 *
	 * @param int $id the id of the forum or 0 for the current forum
	 * @return boolean true if the user has access
	 */
	public function has_access_to_intern_forum($id = 0)
	{
		$fid = ($id == 0) ? $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID) : $id;

		$forum_data = $this->forums->get_node_data($fid);
		if($forum_data === null)
			return false;
		
		if($forum_data->get_forum_is_intern())
		{
			// admins have always access
			if($this->user->is_admin())
				return true;

			// guests if never access to intern forums
			if(!$this->user->is_loggedin())
				return false;

			$rows = $this->cache->get_cache('intern')->get_elements_with(array('fid' => $fid));
			if(is_array($rows) && count($rows) > 0)
			{
				$ugroups = $this->user->get_all_user_groups();
				$uid = $this->user->get_user_id();
				foreach($rows as $data)
				{
					if($data['access_type'] == 'user' && $data['access_value'] == $uid)
						return true;
					else if($data['access_type'] == 'group' && in_array($data['access_value'],$ugroups))
						return true;
				}
			}

			return false;
		}

		// it is no intern forum, so the user has access
		return true;
	}

	/**
	 * generates a string with links to the moderators of the given forum
	 *
	 * @param int $fid the id of the forum
	 * @return string a string with the html-code for the forum-view containing the moderators of
	 * 	the given forum
	 */
	public function get_forum_mods($fid)
	{
		$output = '';
		
		// cache the moderators here
		static $all_mods;
		if(is_null($all_mods))
		{
			// to save performance we do this query only if the user-names should be colored
			if($this->cfg['always_color_usernames'])
			{
				// grab all mods and store the moderatored forums of them
				$uids = array();
				foreach($this->cache->get_cache('moderators') as $value)
				{
					if(!isset($uids[$value['user_id']]))
						$uids[$value['user_id']] = array();
					$uids[$value['user_id']][] = $value['rid'];
				}
				
				// grab the usergroups and so on from db
				if(count($uids) > 0)
				{
					foreach(BS_DAO::get_profile()->get_users_by_ids(array_keys($uids)) as $udata)
					{
						// store the moderators for each forum
						$forums = $uids[$udata['id']];
						foreach($forums as $ufid)
						{
							if(!isset($all_mods[$ufid]))
								$all_mods[$ufid] = array();
							$all_mods[$ufid][] = $udata;
						}
					}
				}
				// ensure that the variable is initialized
				else
					$all_mods = array();
			}
			else
			{
				// simply store all mods for each forum in the array
				foreach($this->cache->get_cache('moderators') as $value)
				{
					if(!isset($all_mods[$value['rid']]))
						$all_mods[$value['rid']] = array();
					$all_mods[$value['rid']][] = array(
						'id' => $value['user_id'],
						'user_name' => $value['user_name']
					);
				}
			}
		}
		
		$total = 0;
		$too_many = false;
		
		// add mods
		$user = array();
		$mods = isset($all_mods[$fid]) ? $all_mods[$fid] : array();
		foreach($mods as $value)
		{
			// already added all possible?
			if($total >= BS_MAX_MODS_DISPLAY)
			{
				$too_many = true;
				break;
			}
			
			$user[$value['id']] = true;
			if(isset($value['user_group']))
			{
				$output .= BS_UserUtils::get_instance()->get_link(
					$value['id'],$value['user_name'],$value['user_group']
				);
				$output .= ', ';
			}
			else
				$output .= BS_UserUtils::get_instance()->get_link($value['id'],$value['user_name']).', ';
			
			$total++;
		}
		$output = PLIB_String::substr($output,0,PLIB_String::strlen($output) - 2);
		
		// add super-moderators (except admin)
		// we can do this once, because it does not depend on the forum-id
		static $groups;
		if(is_null($groups))
		{
			$nadmingroups = array();
			foreach($this->cache->get_cache('user_groups') as $gdata)
			{
				if($gdata['id'] != BS_STATUS_ADMIN && $gdata['is_super_mod'] == 1)
					$nadmingroups[] = $gdata['id'];
			}
			
			$groups = array();
			if(count($nadmingroups) > 0)
			{
				foreach(BS_DAO::get_profile()->get_users_by_groups($nadmingroups) as $udata)
				{
					$groups[$udata['id']] = BS_UserUtils::get_instance()->get_link(
						$udata['id'],$udata['user_name'],$udata['user_group']
					);
				}
			}
		}
		
		if(count($groups) > 0 && !$too_many)
		{
			foreach($groups as $user_id => $group)
			{
				if(isset($user[$user_id]))
					continue;
				
				// already added all possible?
				if($total >= BS_MAX_MODS_DISPLAY)
				{
					$too_many = true;
					break;
				}
				
				if($total > 0)
					$output .= ', ';
				$output .= $group;
				$total++;
			}
		}
		
		if($output == '')
			$output = '-';
		else if($too_many)
		{
			$url = $this->url->get_url('team');
			$output .= ', <a href="'.$url.'">...</a>';
		}

		return $output;
	}

	/**
	 * Initializes the forum-permissions (reply, start topics, ...) if not already done.
	 */
	private function _init_forum_perm()
	{
		if($this->_forum_perm !== null)
			return;
		
		// map the db-enum-values to our constants
		$map = array(
			'reply' => BS_MODE_REPLY,
			'topic' => BS_MODE_START_TOPIC,
			'event' => BS_MODE_START_EVENT,
			'poll' => BS_MODE_START_POLL
		);
		
		// at first we store the permissions for all forums
		$this->_forum_perm = array();
		foreach(BS_DAO::get_forums_perm()->get_all() as $row)
		{
			if(!isset($this->_forum_perm[$row['forum_id']]))
			{
				$this->_forum_perm[$row['forum_id']] = array(
					'reply' => array(),
					'topic' => array(),
					'poll' => array(),
					'event' => array()
				);
			}
			
			$type = $map[$row['type']];
			$this->_forum_perm[$row['forum_id']][$type][] = $row['group_id'];
		}
		
		// is there a current-forum-id?
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		if($fid == null)
			return;
		
		// by default the user has no permission
		$this->_current_forum_perm = array();
		foreach(array_keys($this->_forum_perm[$fid]) as $key)
			$this->_current_forum_perm[$key] = false;

		// check if it is a intern forum
		if($this->forums->is_intern_forum($fid) && !$this->has_access_to_intern_forum($fid))
			return;

		// grab forum-data
		$data = $this->forums->get_node_data($fid);
		if($data === null)
			return;
		
		// store the permissions corresponding to the user-status and groups
		$is_admin = $this->user->is_admin();
		$ugroups = $this->user->get_all_user_groups();
		foreach($this->_forum_perm[$fid] as $key => $groups)
		{
			if($is_admin || $data->get_forum_type() == 'contains_cats')
				$this->_current_forum_perm[$key] = true;
			else
				$this->_current_forum_perm[$key] = count(array_intersect($ugroups,$groups)) > 0;
		}
	}

	/**
	 * Calculates the permission for this user
	 */
	private function _calculate_group_perm()
	{
		$permissions = array(
			'view_memberlist',
			'view_linklist',
			'view_stats',
			'view_calendar',
			'view_search',
			'view_userdetails',
			'edit_own_posts',
			'delete_own_posts',
			'edit_own_threads',
			'delete_own_threads',
			'openclose_own_threads',
			'send_mails',
			'add_new_link',
			'attachments_add',
			'attachments_download',
			'add_cal_event',
			'edit_cal_event',
			'delete_cal_event',
			'subscribe_forums',
			'disable_ip_blocks',
			'view_user_ip',
			'view_online_locations',
			'enter_board',
			'view_user_online_detail',
			'always_edit_poll_options',
			'is_super_mod'
		);

		$ugroups = $this->cache->get_cache('user_groups');
		$this->_user_group_perm = $ugroups->get_element($this->user->get_user_group());

		foreach($this->user->get_all_user_groups() as $id)
		{
			$data = $ugroups->get_element($id);
			foreach($permissions as $perm)
			{
				if($this->_user_group_perm[$perm] == 0 && $data[$perm] == 1)
					$this->_user_group_perm[$perm] = 1;
			}
		}
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>