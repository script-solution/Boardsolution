<?php
/**
 * Contains the choose-favorite-forums-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The choose-favorite-forums-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_favforums extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$user = FWS_Props::get()->user();
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();

		if(!$user->is_loggedin())
			return 'You are a guest';

		$ids = $input->get_var('favorite','post');
		if(!is_array($ids))
			$ids = array();
		
		// collect ids and invert them
		$fids = array();
		foreach(array_keys($ids) as $fid)
		{
			if(FWS_Helper::is_integer($fid))
				$fids[] = $fid;
		}
		$fids = $forums->get_nodes_with_other_ids($fids,false);
		$uid = $user->get_user_id();
		
		// delete the old ids
		BS_DAO::get_unreadhide()->delete_by_users(array($uid));
		
		// insert new ids
		BS_DAO::get_unreadhide()->create($uid,$fids);
		
		$this->set_action_performed(true);
		$this->add_link(
			$locale->lang('back'),$url->get_url(0,'&amp;'.BS_URL_LOC.'=favforums')
		);

		return '';
	}
}
?>