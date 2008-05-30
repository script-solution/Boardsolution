<?php
/**
 * Contains the edit-forums-action
 *
 * @version			$Id: action_edit.php 781 2008-05-26 11:54:49Z nasmussen $
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
		$helper = BS_ACP_Module_Forums_Helper::get_instance();
		
		if($type == 'edit')
		{
			$id = $this->input->get_var('id','get',PLIB_Input::ID);
			if($id == null)
				return 'Invalid id "'.$id.'"';
		}
		
		$category = $this->input->get_var('forum_name','post',PLIB_Input::STRING);
		$parent = $this->input->get_var('parent','post',PLIB_Input::INTEGER);
		$description = $this->input->get_var('description','post',PLIB_Input::STRING);
		$forum_type = $this->input->correct_var(
			'forum_type','post',PLIB_Input::STRING,array('contains_cats','contains_threads'),'contains_threads'
		);
		$forum_is_intern = $this->input->get_var('is_intern','post',PLIB_Input::INT_BOOL);
		$group_access = $this->input->get_var('group_access','post');
		$selected_user = $this->input->get_var('selectedUsers','post',PLIB_Input::STRING);
		$permission_thread = $this->input->get_var('permission_thread','post');
		$permission_poll = $this->input->get_var('permission_poll','post');
		$permission_event = $this->input->get_var('permission_event','post');
		$permission_post = $this->input->get_var('permission_post','post');
		$increase_experience = $this->input->get_var('increase_experience','post',PLIB_Input::INT_BOOL);
		$display_subforums = $this->input->get_var('display_subforums','post',PLIB_Input::INT_BOOL);
		$forum_is_closed = $this->input->get_var('forum_is_closed','post',PLIB_Input::INT_BOOL);
		
		// check data
		if(trim($category) == '')
			return 'forum_name_missing';

		if($type == 'edit' && !$helper->is_no_sub_category($id,$parent))
			return 'forum_structure_invalid';

		// update parent-id and sort
		if($type == 'edit')
		{
			$data = $this->forums->get_node_data($id);
			if($data === null)
				return 'Forum with id "'.$id.'" could not been found';
			
			// if the parent-id has changed we have to change this first and rebuild the forum-cache
			// otherwise we would get the wrong results for $this->_get_changable_values()
			if($data->get_parent_id() != $parent)
			{
				$sort = $this->forums->get_child_count($parent) + 1;
				BS_DAO::get_forums()->update_by_id($id,array(
					'parent_id' => $parent,
					'sortierung' => $sort
				));
				PLIB_Object::set_prop('forums',new BS_Forums_Manager());
			}
		}
		
		// clear attributes that don't affect categories
		if($forum_type == 'contains_cats')
		{
			$description = '';
			$forum_is_intern = 0;
		}
		else
			$description = PLIB_StringHelper::htmlspecialchars_back($description);
		
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
			$values['sortierung'] = $this->forums->get_child_count($parent) + 1;
		
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
			$this->msgs->add_warning($this->locale->lang('forums_warning_no_rights'));
		
		$helper->refresh_intern_access($id,$selected_user,$group_access,$forum_is_intern);
		
		// refresh forum-cache
		PLIB_Object::set_prop('forums',new BS_Forums_Manager());
		
		if($type == 'edit')
			$this->set_success_msg($this->locale->lang('categorie_edit_success'));
		else
			$this->set_success_msg($this->locale->lang('forum_successfully_created'));
		
		$this->set_action_performed(true);

		return '';
	}
}
?>