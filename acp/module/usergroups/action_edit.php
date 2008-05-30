<?php
/**
 * Contains the edit-usergroups-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-usergroups-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_usergroups_edit extends BS_ACP_Action_Base
{
	public function perform_action($type = 'edit')
	{
		if($type == 'edit')
		{
			$id = $this->input->get_var('id','get',PLIB_Input::ID);
			if($id == null)
				return 'The id "'.$id.'" is invalid';
		}

		$group_title = $this->input->get_var('group_title','post',PLIB_Input::STRING);
		if(trim($group_title) == '')
			return 'group_title_missing';

		// collect values to update/insert
		$values = array();
		$values['group_title'] = $group_title;
		
		if($type == 'add' || $id != BS_STATUS_GUEST)
		{
			$group_color = $this->input->get_var('group_color','post',PLIB_Input::STRING);
			if(!preg_match('/^[a-f0-9]{6}$/i',$group_color))
				return 'invalid_group_color';

			$overrides_mod = $this->input->get_var('overrides_mod','post',PLIB_Input::INT_BOOL);
			$gr_filled_image = $this->input->get_var('group_rank_filled_image','post',PLIB_Input::STRING);
			$gr_empty_image = $this->input->get_var('group_rank_empty_image','post',PLIB_Input::STRING);
			$is_visible = $this->input->get_var('is_visible','post',PLIB_Input::INT_BOOL);

			$values['group_color'] = $group_color;
			$values['group_rank_filled_image'] = $gr_filled_image;
			$values['group_rank_empty_image'] = $gr_empty_image;
			$values['overrides_mod'] = $overrides_mod;
			if($type == 'edit' && ($id == BS_STATUS_USER || $id == BS_STATUS_ADMIN))
				$values['is_visible'] = 1;
			else
				$values['is_visible'] = $is_visible;
		}

		$helper = BS_ACP_Module_UserGroups_Helper::get_instance();
		$guest_disallowed = $helper->get_guest_disallowed();
		foreach($helper->get_permissions() as $name)
		{
			if($type == 'add' || $id != BS_STATUS_GUEST || !in_array($name,$guest_disallowed))
				$values[$name] = $this->input->get_var($name,'post',PLIB_Input::INT_BOOL);
		}
		
		// update db
		if($type == 'add')
			BS_DAO::get_usergroups()->create($values);
		else
		{
			// made invisible?
			$data = BS_DAO::get_usergroups()->get_by_id($id);
			if($data['is_visible'] == 1 && $values['is_visible'] == 0)
			{
				// ok, we assign all users, that have this group as main-group the group BS_STATUS_USER as
				// main-group
				foreach(BS_DAO::get_profile()->get_users_by_groups(array($id)) as $row)
				{
					$groups = PLIB_Array_Utils::advanced_explode(',',$row['user_group']);
					$groups[0] = BS_STATUS_USER;
					BS_DAO::get_profile()->update_user_by_id(
						array('user_group' => implode(',',$groups).','),$row['id']
					);
				}
			}
			
			BS_DAO::get_usergroups()->update_by_id($id,$values);
		}
		
		// refresh cache
		$this->cache->refresh('user_groups');
		
		// finish
		if($type == 'add')
			$this->set_success_msg($this->locale->lang('group_add_success'));
		else
			$this->set_success_msg($this->locale->lang('group_edit_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>