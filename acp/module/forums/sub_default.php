<?php
/**
 * Contains the default-submodule for forums
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
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
 * The default sub-module for the forums-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_forums_default extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACP_ACTION_DELETE_FORUMS,array('delete','delete'));
		$renderer->add_action(BS_ACP_ACTION_TRUNCATE_FORUMS,array('delete','truncate'));
		$renderer->add_action(BS_ACP_ACTION_SWITCH_FORUMS,'switch');
		$renderer->add_action(BS_ACP_ACTION_RESORT_FORUMS,'resort');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$cache = FWS_Props::get()->cache();
		$auth = FWS_Props::get()->auth();
		$tpl = FWS_Props::get()->tpl();
		$forums = FWS_Props::get()->forums();

		if(($delete = $input->get_var('delete','post')) != null)
		{
			$ids = implode(',',$delete);
			$action_type = $input->get_var('action_type','post',FWS_Input::STRING);
			$names = array();
			foreach($forums->get_nodes_with_ids($delete) as $node)
				$names[] = $node->get_name();
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$url = BS_URL::get_acpsub_url();
			$url->set('ids',$ids);
			
			if($action_type == 'delete')
			{
				$functions->add_delete_message(
					sprintf($locale->lang('delete_forums'),$namelist),
					$url->set('at',BS_ACP_ACTION_DELETE_FORUMS)->to_url(),
					BS_URL::build_acpmod_url()
				);
			}
			else if($action_type == 'empty')
			{
				$functions->add_delete_message(
					sprintf($locale->lang('empty_forum_msg'),$namelist),
					$url->set('at',BS_ACP_ACTION_TRUNCATE_FORUMS)->to_url(),
					BS_URL::build_acpmod_url()
				);
			}
		}
		
		$num = BS_DAO::get_forums()->get_count();
		$sub_cats = array();
		
		$shortcuts = $this->_get_usergroup_shortcuts();
		$usergroups = $cache->get_cache('user_groups');
		$tplshortcuts = array();
		foreach($shortcuts as $gid => $sc)
		{
			$gdata = $usergroups->get_element($gid);
			$tplshortcuts[$sc] = $auth->get_colored_groupname($gdata['id']);
		}
		
		$tpl->add_variables(array(
			'at_update' => BS_ACP_ACTION_UPDATE_FORUMS,
			'shortcuts' => $tplshortcuts
		));
		
		$images = array(
			'dot' => 'acp/images/forums/path_dot.gif',
			'middle' => 'acp/images/forums/path_middle.gif'
		);
		
		$switchurl = BS_URL::get_acpsub_url();
		$switchurl->set('at',BS_ACP_ACTION_SWITCH_FORUMS);
		
		$editurl = BS_URL::get_acpsub_url(0,'edit');
		
		$tplforums = array();
		$last_parent = -1;
		$nodes = $forums->get_all_nodes();
		$num = count($nodes);
		for($row = 0;$row < $num;$row++)
		{
			$node = $nodes[$row];
			$data = $node->get_data();

			if(!isset($sub_cats[$data->get_parent_id()]))
				$sub_cats[$data->get_parent_id()] = 1;
			else
				$sub_cats[$data->get_parent_id()]++;

			$sort_options = array();
			$sub_cat_num = $forums->get_child_count($data->get_parent_id());
			for($b = 1;$b <= $sub_cat_num;$b++)
				$sort_options[$b] = $b;

			$description = $data->get_description() != '' ? $data->get_description() : '&nbsp;';
			$fid = $data->get_id();
			$forum_name = $data->get_name();
			if($data->get_forum_is_intern())
				$forum_name = '<i>'.$forum_name.'</i>';
			$forum_type_title = $locale->lang($data->get_forum_type());

			if($data->get_parent_id() == 0)
				$parent = '<i>'.$locale->lang('main_forum').'</i>';
			else
				$parent = $forums->get_forum_name($data->get_parent_id());

			$switch_up_url = '';
			$up_index = $this->_is_same_parent_above($nodes,$row,$data->get_parent_id());
			if($up_index >= 0)
				$switch_up_url = $switchurl->set('ids',$fid.','.$nodes[$up_index]->get_id())->to_url();

			$switch_down_url = '';
			$down_index = $this->_is_same_parent_below($nodes,$row,$data->get_parent_id());
			if($down_index > 0)
				$switch_down_url = $switchurl->set('ids',$nodes[$down_index]->get_id().','.$fid)->to_url();

			$path_images = BS_ForumUtils::get_path_images($node,$sub_cats,$images,1);
			
			$tplforums[] = array(
				'show_separator' => $last_parent >= 0 && $last_parent != $data->get_parent_id(),
				'path_images' => $path_images,
				'permissions' => $this->_get_forum_permissions($data,$shortcuts),
				'forum_type' => $data->get_forum_type(),
				'forum_type_title' => $forum_type_title,
				'forum_name' => $forum_name,
				'description' => $description,
				'sortierung' => $data->get_sort(),
				'parent' => $parent,
				'switch_up_url' => $switch_up_url,
				'switch_down_url' => $switch_down_url,
				'options_url' => $editurl->set('id',$fid)->to_url(),
				'up_index' => $up_index,
				'down_index' => $down_index,
				'fid' => $fid
			);

			$last_parent = $data->get_parent_id();
		}
		
		$tpl->add_variable_ref('forums',$tplforums);
		
		$url = BS_URL::get_acpsub_url();
		$url->set('at',BS_ACP_ACTION_RESORT_FORUMS);
		$tpl->add_variables(array(
			'correct_sort_url' => $url->to_url()
		));
	}

	/**
	 * checks wether a forum above the given one has the same parent-id
	 *
	 * @param array $forums an numeric array with the forums
	 * @param int $index the index of the forum
	 * @param int $parent_id the parent-id of the forum
	 * @return int the index of the found forum or -1
	 */
	private function _is_same_parent_above($forums,$index,$parent_id)
	{
		for($i = $index - 1;$i >= 0;$i--)
		{
			if($forums[$i]->get_data()->get_parent_id() == $parent_id)
				return $i;
		}

		return -1;
	}

	/**
	 * checks wether a forum below the given one has the same parent-id
	 *
	 * @param array $forums an numeric array with the forums
	 * @param int $index the index of the forum
	 * @param int $parent_id the parent-id of the forum
	 * @return int the index of the found forum or -1
	 */
	private function _is_same_parent_below($forums,$index,$parent_id)
	{
		$num = count($forums);
		for($i = $index + 1;$i < $num;$i++)
		{
			if($forums[$i]->get_data()->get_parent_id() == $parent_id)
				return $i;
		}

		return -1;
	}
	
	/**
	 * Tries to find unique shortcuts for the user-group-names
	 *
	 * @return array an array with the shortcuts: array(<groupID> => <shortcut>,...)
	 */
	private function _get_usergroup_shortcuts()
	{
		$cache = FWS_Props::get()->cache();

		$c = 0;
		$shortcuts = array();
		foreach($cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] == BS_STATUS_ADMIN)
				continue;
			
			$sc = '';
			$upper = FWS_String::strtoupper($gdata['group_title']);
			$glen = FWS_String::strlen($upper);
			for($l = 1;$l <= $glen;$l++)
			{
				$found = true;
				$p = 0;
				do
				{
					if($p + $l > $glen)
					{
						$found = false;
						break;
					}
					
					$sc = FWS_String::substr($upper,$p++,$l);
				} while(isset($shortcuts[$sc]));
				
				if($found)
					break;
			}
			
			if(!$found)
				$sc = $upper.(++$c);
			
			$shortcuts[$sc] = $gdata['id'];
		}
		
		return array_flip($shortcuts);
	}
	
	/**
	 * Builds the forum-permissions of the given forum with given shortcuts
	 * 
	 * @param array $data the forum-data
	 * @param array $shortcuts the shortcuts for the user-groups
	 * @return array the permissions
	 */
	private function _get_forum_permissions($data,$shortcuts)
	{
		$cache = FWS_Props::get()->cache();
		$auth = FWS_Props::get()->auth();

		$usergroups = $cache->get_cache('user_groups');
		$permissions = array();
		
		// build basic permissions
		$permission_map = array(
			'topic' => BS_MODE_START_TOPIC,
			'poll' => BS_MODE_START_POLL,
			'event' => BS_MODE_START_EVENT,
			'answer' => BS_MODE_REPLY
		);
		foreach($permission_map as $tplname => $mode)
		{
			$permissions[$tplname] = '';
			$gids = $auth->get_permissions_in_forum($mode,$data->get_id());
			foreach($gids as $gid)
			{
				if($gid == BS_STATUS_ADMIN)
					continue;
				
				$gdata = $usergroups->get_element($gid);
				if($gdata === null)
					continue;
				
				$permissions[$tplname] .= $shortcuts[$gid].',';
			}
			
			$permissions[$tplname] = FWS_String::substr(
				$permissions[$tplname],0,FWS_String::strlen($permissions[$tplname]) - 1
			);
		}
		
		// calculate access-permissions for this forum
		$intern_more = false;
		$permissions['intern'] = '';
		$intern_perm = $cache->get_cache('intern')->get_elements_with(array('fid' => $data->get_id()));
		foreach($intern_perm as $intern)
		{
			if($intern['access_type'] == 'user')
				$intern_more = true;
			else
				$permissions['intern'] .= $shortcuts[$intern['access_value']].',';
		}
		
		// are there single users?
		if($intern_more)
			$permissions['intern'] .= '(...)';
		else
			$permissions['intern'] = FWS_String::substr($permissions['intern'],0,-1);
		
		return $permissions;
	}
}
?>