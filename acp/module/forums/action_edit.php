<?php
/**
 * Contains the edit-forums-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-forums-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_forums_edit extends BS_ACP_Action_Base
{
	public function perform_action($type = 'edit')
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();

		$helper = BS_ACP_Module_Forums_Helper::get_instance();
		
		if($type == 'edit')
		{
			$id = $input->get_var('id','get',FWS_Input::ID);
			if($id == null)
				return 'Invalid id "'.$id.'"';
		}
		
		$category = $input->get_var('forum_name','post',FWS_Input::STRING);
		$parent = $input->get_var('parent','post',FWS_Input::INTEGER);
		$description = $input->get_var('description','post',FWS_Input::STRING);
		$forum_type = $input->correct_var(
			'forum_type','post',FWS_Input::STRING,array('contains_cats','contains_threads'),'contains_threads'
		);
		$forum_is_intern = $input->get_var('is_intern','post',FWS_Input::INT_BOOL);
		$group_access = $input->get_var('group_access','post');
		$selected_user = $input->get_var('selectedUsers','post',FWS_Input::STRING);
		$permission_thread = $input->get_var('permission_thread','post');
		$permission_poll = $input->get_var('permission_poll','post');
		$permission_event = $input->get_var('permission_event','post');
		$permission_post = $input->get_var('permission_post','post');
		$increase_experience = $input->get_var('increase_experience','post',FWS_Input::INT_BOOL);
		$display_subforums = $input->get_var('display_subforums','post',FWS_Input::INT_BOOL);
		$forum_is_closed = $input->get_var('forum_is_closed','post',FWS_Input::INT_BOOL);
		
		// check data
		if(trim($category) == '')
			return 'forum_name_missing';

		if($type == 'edit' && !$helper->is_no_sub_category($id,$parent))
			return 'forum_structure_invalid';

		// update parent-id and sort
		if($type == 'edit')
		{
			$data = $forums->get_node_data($id);
			if($data === null)
				return 'Forum with id "'.$id.'" could not been found';
			
			// if the parent-id has changed we have to change this first and rebuild the forum-cache
			// otherwise we would get the wrong results for $this->_get_changable_values()
			if($data->get_parent_id() != $parent)
			{
				$sort = $forums->get_child_count($parent) + 1;
				BS_DAO::get_forums()->update_by_id($id,array(
					'parent_id' => $parent,
					'sortierung' => $sort
				));
				FWS_Props::get()->reload('forums');
			}
		}
		
		// clear attributes that don't affect categories
		if($forum_type == 'contains_cats')
		{
			$description = '';
			$forum_is_intern = 0;
		}
		else
			$description = FWS_StringHelper::htmlspecialchars_back($description);
		
		$values = array(
			'forum_name' => $category,
			'description' => $description,
			'forum_type' => $forum_type,
			'forum_is_intern' => $forum_is_intern,
			'increase_experience' => $increase_experience,
			'display_subforums' => $display_subforums,
			'forum_is_closed' => $forum_is_closed
		);
		
		if($type == 'add')
			$values['sortierung'] = $forums->get_child_count($parent) + 1;
		
		if($type == 'edit')
			BS_DAO::get_forums()->update_by_id($id,$values);
		else
			$id = BS_DAO::get_forums()->create($values);
		
		// build group-ids
		$gids = array();
		foreach(array('thread','poll','event','post') as $ptype)
		{
			$gids[$ptype] = array();
			foreach(${'permission_'.$ptype} as $gid => $access)
			{
				if($access)
					$gids[$ptype][] = $gid;
			}
		}

		// set permissions
		BS_DAO::get_forums_perm()->delete_by_forums(array($id));
		
		$total = 0;
		$total += BS_DAO::get_forums_perm()->set_permissions($id,'topic',$gids['thread']);
		$total += BS_DAO::get_forums_perm()->set_permissions($id,'poll',$gids['poll']);
		$total += BS_DAO::get_forums_perm()->set_permissions($id,'event',$gids['event']);
		$total += BS_DAO::get_forums_perm()->set_permissions($id,'reply',$gids['post']);
		
		if($forum_type != 'contains_cats' && $total == 0)
			$msgs->add_warning($locale->lang('forums_warning_no_rights'));
		
		$helper->refresh_intern_access($id,$selected_user,$group_access,$forum_is_intern);
		
		// refresh forum-cache
		FWS_Props::get()->reload('forums');
		
		if($type == 'edit')
			$this->set_success_msg($locale->lang('categorie_edit_success'));
		else
			$this->set_success_msg($locale->lang('forum_successfully_created'));
		
		$this->set_action_performed(true);

		return '';
	}
}
?>