<?php
/**
 * Contains the ugroups-user-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The ugroups-user-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_user_ugroups extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();
		$user = PLIB_Props::get()->user();

		$main_group = $input->get_var('main_group','post');
		$other_groups = $input->get_var('other_groups','post');
		$idstr = $input->get_var('delete','post',PLIB_Input::STRING);
		$ids = PLIB_Array_Utils::advanced_explode(',',$idstr);
		
		if(!is_array($ids) || count($ids) == 0)
			return 'No valid ids got via POST';
		
		// add the main-group to the users
		$users = array();
		foreach($ids as $uid)
		{
			if(!PLIB_Helper::is_integer($uid))
				return 'The id "'.$uid.'" is invalid';
			
			$uid = (int)$uid;
			if($uid == $user->get_user_id())
				$users[$uid] = array($user->get_user_group());
			else
			{
				$gdata = $cache->get_cache('user_groups')->get_element($main_group[$uid]);
				if($gdata === null)
					return 'The group "'.$main_group[$uid].'" doesn\'t exist!';
				if($gdata['is_visible'] == 0)
					return 'You can\'t choose invisible groups as main-group!';
				
				$users[$uid] = array($main_group[$uid]);
			}
		}

		// add the other groups
		if(is_array($other_groups))
		{
			foreach($other_groups as $uid => $groups)
			{
				$uid = (int)$uid;
				foreach($groups as $gid)
				{
					if($cache->get_cache('user_groups')->key_exists($gid))
						$users[$uid][] = $gid;
				}
			}
		}

		// now update the groups
		$count = 0;
		foreach($users as $id => $groups)
		{
			$groups = array_unique($groups);
			BS_DAO::get_profile()->update_user_by_id(array('user_group' => implode(',',$groups).','),$id);
			$count++;
		}

		$this->set_success_msg($locale->lang('user_groups_edited_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>