<?php
/**
 * Contains the team-module
 * 
 * @version			$Id$
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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_breadcrumb($locale->lang('the_team'),BS_URL::build_mod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$cache = FWS_Props::get()->cache();
		$admins = array();
		
		$tpl->add_variables(array(
			'show_pm' => $cfg['display_denied_options'] || $user->is_loggedin()
		));
		
		$purl = BS_URL::get_sub_url('userprofile','pmcompose');
		
		// collect admins
		$admin_ids = array();
		foreach(BS_DAO::get_profile()->get_users_by_groups(array(BS_STATUS_ADMIN)) as $data)
		{
			// don't add admins as moderators again
			$admin_ids[$data['id']] = true;
			
			list($list,$count) = $this->_get_forum_list($data['id']);
			$admins[] = array(
				'user_name' => BS_UserUtils::get_link($data['id'],$data['user_name'],$data['user_group']),
				'id' => $data['id'],
				'pm_url' => $purl->set(BS_URL_ID,$data['id'])->to_url(),
				'forum_count' => $count,
				'forum_list' => $list
			);
		}
		
		$tpl->add_array('admins',$admins);
		
		// determine moderators
		$mods = array();
		foreach(BS_DAO::get_mods()->get_all_grouped_by_user() as $data)
		{
			if(isset($admin_ids[$data['user_id']]))
				continue;
			
			// we have to handle super-mods different
			$user_groups = FWS_Array_Utils::advanced_explode(',',$data['user_group']);
			$is_super_mod = false;
			foreach($user_groups as $gid)
			{
				$gdata = $cache->get_cache('user_groups')->get_element($gid);
				if($gdata['is_super_mod'] == 1)
				{
					$is_super_mod = true;
					break;
				}
			}
			
			// don't add mods twice
			$admin_ids[$data['user_id']] = true;
			
			list($list,$count) = $this->_get_forum_list($is_super_mod ? 0 : $data['user_id']);
			$mods[] = array(
				'user_name' => BS_UserUtils::get_link($data['user_id'],$data['user_name'],
					$data['user_group']),
				'id' => $data['user_id'],
				'pm_url' => $purl->set(BS_URL_ID,$data['user_id'])->to_url(),
				'forum_count' => $count,
				'forum_list' => $list
			);
		}
		
		// look for super-moderator-groups
		$team_groups = array();
		foreach($cache->get_cache('user_groups') as $data)
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
					
					$mods[] = array(
						'user_name' => BS_UserUtils::get_link($udata['id'],$udata['user_name'],
							$udata['user_group']),
						'id' => $udata['id'],
						'pm_url' => $purl->set(BS_URL_ID,$udata['id'])->to_url(),
						'forum_count' => $count,
						'forum_list' => $list
					);
				}
			}
			else if($data['is_team'] == 1 && $data['id'] != BS_STATUS_GUEST &&
				$data['id'] != BS_STATUS_ADMIN)
			{
				$team_groups[] = $data['id'];
			}
		}
		
		// add team-groups
		$other = array();
		if(count($team_groups))
		{
			// grab all members of the group
			foreach(BS_DAO::get_profile()->get_users_by_groups($team_groups) as $udata)
			{
				$gname = $this->get_group_name($udata['user_group']);
				if(!isset($other[$gname]))
					$other[$gname] = array();
				
				$other[$gname] = array(
					'user_name' => BS_UserUtils::get_link($udata['id'],$udata['user_name'],
						$udata['user_group']),
					'id' => $udata['id'],
						'pm_url' => $purl->set(BS_URL_ID,$udata['id'])->to_url(),
				);
			}
		}
		
		$tpl->add_array('mods',$mods);
		$tpl->add_array('other',$other);
	}
	
	/**
	 * Determines the group-name of the group that should be taken from the given group-list
	 *
	 * @param string $groups a comma-separated list of group-ids
	 * @return string the name of the group or null if not found
	 */
	private function get_group_name($groups)
	{
		$cache = FWS_Props::get()->cache();

		$gcache = $cache->get_cache('user_groups');
		$agroups = FWS_Array_Utils::advanced_explode(',',$groups);
		foreach($agroups as $gid)
		{
			if($gid != BS_STATUS_ADMIN && ($gdata = $gcache->get_element($gid)) !== null &&
					$gdata['is_team'] == 1)
				return $gdata['group_title'];
		}
		
		return null;
	}
	
	/**
	 * Builds the forum-list for the given user
	 * 
	 * @param int $user_id the id of the user (0 = all forums)
	 * @return array an array with: array(&lt;list&gt;,&lt;count&gt;)
	 */
	private function _get_forum_list($user_id)
	{
		$cache = FWS_Props::get()->cache();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$forums = FWS_Props::get()->forums();

		$count = 0;
		$forum_list = '';
		
		// grab all forums
		if($user_id == 0)
		{
			$nodes = array();
			$fids = $forums->get_sub_node_ids(0);
			foreach($fids as $fid)
			{
				if($forums->get_forum_type($fid) == 'contains_cats')
					continue;
				
				$nodes[] = array('rid' => $fid);
			}
		}
		// grab the moderated forums
		else
			$nodes = $cache->get_cache('moderators')->get_elements_with(array('user_id' => $user_id));
		
		// determine the visible forums
		$visible_forums = array();
		foreach($nodes as $forum)
		{
			if($cfg['hide_denied_forums'] && !$auth->has_access_to_intern_forum($forum['rid']))
				continue;
			
			$visible_forums[] = $forum;
		}
	
		$i = 0;
		$len = count($visible_forums);
		foreach($visible_forums as $forum)
		{
			$forum_list .= BS_ForumUtils::get_forum_path($forum['rid'],false);
			$count++;
			if($i++ < $len - 1)
				$forum_list .= '<br />';
		}
		
		if($count == 0)
			$forum_list = '-';
		
		return array($forum_list,$count);
	}
}
?>