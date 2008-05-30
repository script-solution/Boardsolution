<?php
/**
 * Contains the add-moderators-action
 *
 * @version			$Id: action_add.php 784 2008-05-26 11:57:10Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add-moderators-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_moderators_add extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$new_mods = $this->input->get_var('user_add','post');
		$forums = $this->forums->get_all_nodes();
		$mods = array();
		for($i = 0;$i < count($forums);$i++)
		{
			$node = $forums[$i];
			$data = $node->get_data();
			if($data->get_forum_type() == 'contains_threads')
				$mods[$data->get_id()] = PLIB_Array_Utils::advanced_explode(',',$new_mods[$data->get_id()]);
		}

		if(!is_array($mods) || count($mods) == 0)
			return '';
		
		$count = 0;
		foreach($mods as $fid => $user_names)
		{
			PLIB_Array_Utils::trim($user_names);
			foreach(BS_DAO::get_user()->get_users_by_names($user_names) as $data)
			{
				if(!BS_DAO::get_mods()->is_user_mod_in_forum($data['id'],$fid))
				{
					BS_DAO::get_mods()->create($fid,$data['id']);
					$count++;
				}
			}
		}

		if($count > 0)
		{
			$this->cache->refresh('moderators');
			$this->set_success_msg($this->locale->lang('add_moderators_success'));
			$this->set_action_performed(true);
		}

		return '';
	}
}
?>