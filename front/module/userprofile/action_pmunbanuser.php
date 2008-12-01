<?php
/**
 * Contains the pm-unban-user-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pm-unban-user-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_pmunbanuser extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		// check if we are allowed to unban a user
		if(!$user->is_loggedin() || $cfg['enable_pms'] == 0 ||
				$user->get_profile_val('allow_pms') == 0)
			return 'You are a guest, PMs are disabled or you\'ve disabled PMs';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check parameter
		$ids = $input->get_var(BS_URL_DEL,'get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($ids)))
			return 'Invalid id-sstring got via GET';

		// delete the user from our banlist
		BS_DAO::get_userbans()->delete_bans_of_user($user->get_user_id(),$ids);

		$this->set_action_performed(true);
		$this->add_link(
			$locale->lang('back'),BS_URL::get_sub_url('userprofile','pmbanlist')
		);
		
		return '';
	}
}
?>