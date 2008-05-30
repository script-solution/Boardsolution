<?php
/**
 * Contains the team-module
 * 
 * @version			$Id: module_team.php 733 2008-05-23 06:40:04Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The team-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_team extends BS_Front_Module
{
	public function run()
	{
		$admins = array();
		
		$this->tpl->add_variables(array(
			'show_pm' => $this->cfg['display_denied_options'] || $this->user->is_loggedin()
		));
		
		// collect admins
		$admin_ids = array();
		foreach(BS_DAO::get_profile()->get_users_by_groups(array(BS_STATUS_ADMIN)) as $data)
		{
			$url = $this->url->get_url(
				'userprofile','&amp;'.BS_URL_LOC.'=pmcompose&amp;'.BS_URL_ID.'='.$data['id']
			);
			
			// don't add admins as moderators again
			$admin_ids[$data['id']] = true;
			
			list($list,$count) = $this->_get_forum_list($data['id']);
			$admins[] = array(
				'user_name' => BS_UserUtils::get_instance()->get_link($data['id'],$data['user_name'],$data['user_group']),
				'id' => $data['id'],
				'pm_url' => $url,
				'forum_count' => $count,
				'forum_list' => $list
			);
		}
		
		$this->tpl->add_array('admins',$admins);
		
		// determine moderators
		$mods = array();
		foreach(BS_DAO::get_mods()->get_all_grouped_by_user() as $data)
		{
			if(isset($admin_ids[$data['user_id']]))
				continue;
			
			// we have to handle super-mods different
			$user_groups = PLIB_Array_Utils::advanced_explode(',',$data['user_group']);
			$is_super_mod = false;
			foreach($user_groups as $gid)
			{
				$gdata = $this->cache->get_cache('user_groups')->get_element($gid);
				if($gdata['is_super_mod'] == 1)
				{
					$is_super_mod = true;
					break;
				}
			}
			
			// don't add mods twice
			$admin_ids[$data['user_id']] = true;
			
			$url = $this->url->get_url(
				'userprofile','&amp;'.BS_URL_LOC.'=pmcompose&amp;'.BS_URL_ID.'='.$data['user_id']
			);
			
			list($list,$count) = $this->_get_forum_list($is_super_mod ? 0 : $data['user_id']);
			$mods[] = array(
				'user_name' => BS_UserUtils::get_instance()->get_link($data['user_id'],$data['user_name'],
					$data['user_group']),
				'id' => $data['user_id'],
				'pm_url' => $url,
				'forum_count' => $count,
				'forum_list' => $list
			);
		}
		
		// look for super-moderator-groups
		foreach($this->cache->get_cache('user_groups') as $data)
		{
			// no admin and super-mod?
			if($data['id'] != BS_STATUS_ADMIN && $data['is_super_mod'] == 1)
			{
				// they moderate all forums so we can collect it once for all members of the group
				list($list,$count) = $this->_get_forum_list(0);
				
				// grab all members of the group
				foreach(BS_DAO::get_profile()->get_users_by_groups(array($data['id'])) as $udata)
				{
					// have we already collected the user?
					if(isset($admin_ids[$udata['id']]))
						continue;
					
					$url = $this->url->get_url(
						'userprofile','&amp;'.BS_URL_LOC.'=pmcompose&amp;'.BS_URL_ID.'='.$udata['id']
					);
					
					$mods[] = array(
						'user_name' => BS_UserUtils::get_instance()->get_link($udata['id'],$udata['user_name'],
							$udata['user_group']),
						'id' => $udata['id'],
						'pm_url' => $url,
						'forum_count' => $count,
						'forum_list' => $list
					);
				}
			}
		}
		
		$this->tpl->add_array('mods',$mods);
	}
	
	/**
	 * Builds the forum-list for the given user
	 * 
	 * @param int $user_id the id of the user (0 = all forums)
	 * @return array an array with: array(&lt;list&gt;,&lt;count&gt;)
	 */
	private function _get_forum_list($user_id)
	{
		$count = 0;
		$forum_list = '';
		
		// grab all forums
		if($user_id == 0)
		{
			$forums = array();
			$fids = $this->forums->get_sub_node_ids(0);
			foreach($fids as $fid)
			{
				if($this->forums->get_forum_type($fid) == 'contains_cats')
					continue;
				
				$forums[] = array('rid' => $fid);
			}
		}
		// grab the moderated forums
		else
			$forums = $this->cache->get_cache('moderators')->get_elements_with(array('user_id' => $user_id));
		
		// determine the visible forums
		$visible_forums = array();
		foreach($forums as $forum)
		{
			if($this->cfg['hide_denied_forums'] && !$this->auth->has_access_to_intern_forum($forum['rid']))
				continue;
			
			$visible_forums[] = $forum;
		}
	
		$i = 0;
		$len = count($visible_forums);
		foreach($visible_forums as $forum)
		{
			$forum_list .= BS_ForumUtils::get_instance()->get_forum_path($forum['rid'],false);
			$count++;
			if($i++ < $len - 1)
				$forum_list .= '<br />';
		}
		
		if($count == 0)
			$forum_list = '-';
		
		return array($forum_list,$count);
	}
	
	public function get_location()
	{
		return array($this->locale->lang('the_team') => $this->url->get_url('team'));
	}
	
	public function has_access()
	{
		return true;
	}
}
?>