<?php
/**
 * Contains the add-moderators-action
 *
 * @version			$Id$
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
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();
		$forums = PLIB_Props::get()->forums();

		$new_mods = $input->get_var('user_add','post');
		$nodes = $forums->get_all_nodes();
		$mods = array();
		for($i = 0;$i < count($nodes);$i++)
		{
			$node = $nodes[$i];
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
			$cache->refresh('moderators');
			$this->set_success_msg($locale->lang('add_moderators_success'));
			$this->set_action_performed(true);
		}

		return '';
	}
}
?>