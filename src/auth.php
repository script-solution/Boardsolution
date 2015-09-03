<?php
/**
 * Contains the auth-class
 * 
 * @package			Boardsolution
 * @subpackage	src
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * The auth-class is used for all kind of authorisation-tasks. It stores the permissions of the
 * current user depending on the usergroups, moderator status in current forum and so on.
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Auth extends FWS_Object
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
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$cache = FWS_Props::get()->cache();

		$this->_calculate_group_perm();

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		if($fid !== null && $fid > 0)
		{
			if($user->is_loggedin() && !$user->is_admin())
			{
				// we have to do this here manually because the is_moderator_in_current_forum() method
				// uses this field to determine if the user is a mod
				$ismod = $cache->get_cache('moderators')->element_exists_with(array(
					'rid' => $fid,'user_id' => $user->get_user_id()
				));
				$this->_is_current_forum_mod = $this->_user_group_perm['is_super_mod'] == 1 || $ismod;
				
				if($this->_is_current_forum_mod)
					$this->_is_mod_in_any_forum = true;
				else
				{
					// check if he/she is a moderator in any forum
					$ismodany = $cache->get_cache('moderators')->element_exists_with(array(
						'user_id' => $user->get_user_id())
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
		$cfg = FWS_Props::get()->cfg();

		return $cfg[$type] > 0 && $this->_user_group_perm['disable_ip_blocks'] == 0;
	}
	
	/**
	 * Checks wether the user with given id (0 = current) is an admin
	 *
	 * @param int $id the user-id (0 = current)
	 * @return boolean true if so
	 */
	public function is_admin($id = 0)
	{
		if($id > 0)
		{
			$data = BS_DAO::get_profile()->get_user_by_id($id);
			return $this->is_in_group($data['user_group'],BS_STATUS_ADMIN);
		}
		
		$user = FWS_Props::get()->user();
		return $user->is_admin();
	}
	
	/**
	 * Checks wether the current user or the given one is moderator in the current forum
	 * 
	 * @param int $user_id the id of th user (0 = current)
	 * @param string $group_ids a comma-separated string with all groups of the user (just if
	 * 	$user_id != 0). if not given it will be loaded from db
	 * @return boolean true if the user is a moderator in the current forum
	 */
	public function is_moderator_in_current_forum($user_id = 0,$group_ids = '')
	{
		$input = FWS_Props::get()->input();
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		return $this->is_moderator_in_forum($fid,$user_id,$group_ids);
	}
	
	/**
	 * Checks wether the current user or the given one is moderator in the given forum
	 * 
	 * @param int $fid the forum-id
	 * @param int $user_id the id of th user (0 = current)
	 * @param string $group_ids a comma-separated string with all groups of the user (just if
	 * 	$user_id != 0). if not given it will be loaded from db
	 * @return boolean true if the user is a moderator in the given forum
	 */
	public function is_moderator_in_forum($fid,$user_id = 0,$group_ids = '')
	{
		$cache = FWS_Props::get()->cache();

		// current user?
		if($user_id == 0)
			return $this->_is_current_forum_mod;
		
		// check for the given user-id and user-groups
		$is_mod = $cache->get_cache('moderators')->element_exists_with(array(
			'rid' => $fid,'user_id' => $user_id
		));
		if($is_mod)
			return true;
		
		// load group-ids if not present
		if($group_ids == '')
		{
			$data = BS_DAO::get_profile()->get_user_by_id($user_id);
			$group_ids = $data['user_group'];
		}
		
		// is one of the groups super-mod?
		$ugroups = $cache->get_cache('user_groups');
		foreach(FWS_Array_Utils::advanced_explode(',',$group_ids) as $group_id)
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
	 * @param string $group_ids the usergroups of the user. (just if $user_id != 0). if not given
	 * 	it will be loaded from db
	 * @return boolean true if this user is a moderator in any forum
	 */
	public function is_moderator_in_any_forum($user_id = 0,$group_ids = '')
	{
		$cache = FWS_Props::get()->cache();

		// the current user?
		if($user_id == 0)
			return $this->_is_mod_in_any_forum;
		
		// load group-ids if not present
		if($group_ids == '')
		{
			$data = BS_DAO::get_profile()->get_user_by_id($user_id);
			$group_ids = $data['user_group'];
		}
		
		// is one of the groups super-mod?
		$ugroups = $cache->get_cache('user_groups');
		foreach(FWS_Array_Utils::advanced_explode(',',$group_ids) as $group_id)
		{
			$gdata = $ugroups->get_element($group_id);
			if($gdata['is_super_mod'] == 1)
				return true;
		}
		
		// mod in a forum?
		// TODO this is very slow. perhaps should we organize the moderators more clever?
		if($cache->get_cache('moderators')->element_exists_with(array('user_id' => $user_id)))
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
		$cache = FWS_Props::get()->cache();

		$murl = BS_URL::get_mod_url('memberlist');
		
		$user_groups = '';
		$ugroups = FWS_Array_Utils::advanced_explode(",",$group_ids);
		$i = 0;
		foreach($ugroups as $gid)
		{
			$gdata = $cache->get_cache('user_groups')->get_element($gid);
			if(!$show_hidden && $gdata['is_visible'] == 0)
				continue;
			
			if($add_links)
			{
				$murl->set(BS_URL_MS_GROUP,array($gid));
				$group = '<a href="'.$murl->to_url().'" style="color: #'.$gdata['group_color'].';">';
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
		
		return FWS_String::substr($user_groups,0,-2);
	}

	/**
	 * retrieves the name of the group with given id
	 *
	 * @param int $group_id the id of the group
	 * @return string name of the group
	 */
	public function get_groupname($group_id)
	{
		$cache = FWS_Props::get()->cache();

		$data = $cache->get_cache('user_groups')->get_element($group_id);
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
		$cache = FWS_Props::get()->cache();

		$gdata = $cache->get_cache('user_groups')->get_element($group_id);
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
		$cache = FWS_Props::get()->cache();
		$cfg = FWS_Props::get()->cfg();

		$gdata = $cache->get_cache('user_groups')->get_element((int)$group_ids);
		if($gdata['overrides_mod'] == 0 && $this->is_moderator_in_any_forum($id,$group_ids))
			return $cfg['mod_color'];

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
		$cache = FWS_Props::get()->cache();
		$cfg = FWS_Props::get()->cfg();

		$ugroups = $cache->get_cache('user_groups');
		$gdata = $ugroups->get_element((int)$group_ids);

		if($gdata['overrides_mod'] == 0 && $this->is_moderator_in_any_forum($id,$group_ids))
		{
			return array(
				'is_mod' => true,
				'filled' => $cfg['mod_rank_filled_image'],
				'empty' => $cfg['mod_rank_empty_image']
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
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();

		// the admin has always permission
		if($user->is_admin())
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
				if($cfg['mod_edit_topics'] == 1 && $this->is_moderator_in_current_forum())
					return true;

				if($post_user != 0)
				{
					if($user->get_user_id() == $post_user &&
						$this->_user_group_perm['edit_own_threads'] == 1)
						return true;
				}
				return false;

			case BS_MODE_DELETE_TOPICS:
				if($cfg['mod_delete_topics'] == 1 && $this->is_moderator_in_current_forum())
					return true;

				if($post_user != 0)
				{
					if($user->get_user_id() == $post_user &&
						$this->_user_group_perm['delete_own_threads'] == 1)
						return true;
				}
				return false;

			case BS_MODE_MOVE_TOPICS:
				return $cfg['mod_move_topics'] == 1 && $this->is_moderator_in_current_forum();

			case BS_MODE_EDIT_POST:
				if($cfg['mod_edit_posts'] == 1 && $this->is_moderator_in_current_forum())
					return true;

				return $user->get_user_id() == $post_user &&
					$this->_user_group_perm['edit_own_posts'] == 1;

			case BS_MODE_DELETE_POSTS:
				if($cfg['mod_delete_posts'] == 1 && $this->is_moderator_in_current_forum())
					return true;

				if($post_user != 0)
					return $user->get_user_id() == $post_user &&
						$this->_user_group_perm['delete_own_posts'] == 1;

				return false;

			case BS_MODE_SPLIT_POSTS:
				return $cfg['mod_split_posts'] == 1 && $this->is_moderator_in_current_forum();

			case BS_MODE_OPENCLOSE_TOPICS:
				if($cfg['mod_openclose_topics'] == 1 && $this->is_moderator_in_current_forum())
					return true;

				return $user->get_user_id() == $post_user &&
					$this->_user_group_perm['openclose_own_threads'] == 1;
			
			case BS_MODE_LOCK_TOPICS:
				return $cfg['mod_lock_topics'] == 1 && $this->is_moderator_in_current_forum();
			
			case BS_MODE_MARK_TOPICS_IMPORTANT:
				return $cfg['mod_mark_topics_important'] == 1 && $this->is_moderator_in_current_forum();
		}

		return false;
	}
	
	/**
	 * Returns all permissions for the given forum and action
	 *
	 * @param int $action the action to perform (BS_MODE_REPLY, BS_MODE_START_TOPIC, ...)
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
	 * @param int $action the action to perform (BS_MODE_REPLY, BS_MODE_START_TOPIC, ...)
	 * @param int $fid the id of the forum
	 * @return bool true if the user has permission
	 */
	public function has_permission_in_forum($action,$fid)
	{
		$user = FWS_Props::get()->user();

		// admins have always permission!
		if($user->is_admin())
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
		return in_array($group,FWS_Array_Utils::advanced_explode(',',$groups));
	}
	
	/**
	 * Checks wether the current user is in any of the given groups
	 *
	 * @param array $groups the group-ids
	 * @return boolean true if so
	 */
	public function is_in_any_group($groups)
	{
		$user = FWS_Props::get()->user();
		
		if(!is_array($groups))
			$groups = func_get_args();
		return count(array_intersect($user->get_all_user_groups(),$groups)) > 0;
	}

	/**
	 * checks wether the user has permission for the given operation
	 *
	 * @param string $operation the operation you want to perform (see "bs_user_groups"-table)
	 * @return boolean true if the user has permission
	 */
	public function has_global_permission($operation)
	{
		$user = FWS_Props::get()->user();

		// the admin has always permission
		if($user->is_admin())
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
		$user = FWS_Props::get()->user();

		if(!$this->has_global_permission('enter_board'))
			return false;
		else if($user->is_bot())
		{
			$bot = $user->get_bot_name();
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
		$user = FWS_Props::get()->user();
		$cache = FWS_Props::get()->cache();

		if($user->is_admin())
			return true;

		$acpaccess = $cache->get_cache('acp_access');
		
		// has the user access to acp?
		$cond = array('access_type' => 'user','access_value' => $user->get_user_id());
		if($acpaccess->element_exists_with($cond))
			return true;

		// has any of the user-groups the user belongs to access to the acp?
		foreach($user->get_all_user_groups() as $gid)
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
		$user = FWS_Props::get()->user();
		$cache = FWS_Props::get()->cache();

		if($user->is_admin() || $module == 'faq')
			return true;
		
		$acpaccess = $cache->get_cache('acp_access');

		// has the user access to acp?
		$cond = array(
			'access_type' => 'user',
			'access_value' => $user->get_user_id(),
			'module' => $module
		);
		if($acpaccess->element_exists_with($cond))
			return true;

		// has any of the user-groups the user belongs to access to the acp?
		foreach($user->get_all_user_groups() as $gid)
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
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$functions = FWS_Props::get()->functions();
		
		$fid = ($id == 0) ? $input->get_var(BS_URL_FID,'get',FWS_Input::ID) : $id;
		return $functions->has_access_to_intern_forum(
			$user->get_user_id(),$user->get_all_user_groups(),$fid
		);
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
		$cfg = FWS_Props::get()->cfg();
		$cache = FWS_Props::get()->cache();
		$user = FWS_Props::get()->user();
		$output = '';
		
		// cache the moderators here
		static $all_mods;
		if(is_null($all_mods))
		{
			// to save performance we do this query only if the user-names should be colored
			if($cfg['always_color_usernames'])
			{
				// grab all mods and store the moderatored forums of them
				$uids = array();
				foreach($cache->get_cache('moderators') as $value)
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
				foreach($cache->get_cache('moderators') as $value)
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
				$output .= BS_UserUtils::get_link(
					$value['id'],$value['user_name'],$value['user_group']
				);
				$output .= ', ';
			}
			else
				$output .= BS_UserUtils::get_link($value['id'],$value['user_name']).', ';
			
			$total++;
		}
		$output = FWS_String::substr($output,0,FWS_String::strlen($output) - 2);
		
		// add super-moderators (except admin)
		// we can do this once, because it does not depend on the forum-id
		static $groups;
		if(is_null($groups))
		{
			$nadmingroups = array();
			foreach($cache->get_cache('user_groups') as $gdata)
			{
				if($gdata['id'] != BS_STATUS_ADMIN && $gdata['is_super_mod'] == 1)
					$nadmingroups[] = $gdata['id'];
			}
			
			$groups = array();
			if(count($nadmingroups) > 0)
			{
				foreach(BS_DAO::get_profile()->get_users_by_groups($nadmingroups) as $udata)
				{
					$groups[$udata['id']] = BS_UserUtils::get_link(
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
		
		if($too_many)
		{
			$murl = BS_URL::build_mod_url('team');
			$output .= ', <a href="'.$murl.'">...</a>';
		}

		return $output;
	}

	/**
	 * Initializes the forum-permissions (reply, start topics, ...) if not already done.
	 */
	private function _init_forum_perm()
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$user = FWS_Props::get()->user();

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
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		if($fid == null)
			return;
		
		// by default the user has no permission
		$this->_current_forum_perm = array();
		if(isset($this->_forum_perm[$fid]))
		{
			foreach(array_keys($this->_forum_perm[$fid]) as $key)
				$this->_current_forum_perm[$key] = false;
		}

		// check if it is a intern forum
		if($forums->is_intern_forum($fid) && !$this->has_access_to_intern_forum($fid))
			return;

		// grab forum-data
		$data = $forums->get_node_data($fid);
		if($data === null)
			return;
		
		// store the permissions corresponding to the user-status and groups
		if(isset($this->_forum_perm[$fid]))
		{
			$is_admin = $user->is_admin();
			$ugroups = $user->get_all_user_groups();
			foreach($this->_forum_perm[$fid] as $key => $groups)
			{
				if($is_admin || $data->get_forum_type() == 'contains_cats')
					$this->_current_forum_perm[$key] = true;
				else
					$this->_current_forum_perm[$key] = count(array_intersect($ugroups,$groups)) > 0;
			}
		}
	}

	/**
	 * Calculates the permission for this user
	 */
	private function _calculate_group_perm()
	{
		$cache = FWS_Props::get()->cache();
		$user = FWS_Props::get()->user();

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

		$ugroups = $cache->get_cache('user_groups');
		$this->_user_group_perm = $ugroups->get_element($user->get_user_group());

		foreach($user->get_all_user_groups() as $id)
		{
			$data = $ugroups->get_element($id);
			foreach($permissions as $perm)
			{
				if($this->_user_group_perm[$perm] == 0 && $data[$perm] == 1)
					$this->_user_group_perm[$perm] = 1;
			}
		}
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
