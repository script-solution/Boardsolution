<?php
/**
 * Contains the pm-unban-user-action
 *
 * @version			$Id: action_pmunbanuser.php 756 2008-05-24 18:20:13Z nasmussen $
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
		// check if we are allowed to unban a user
		if(!$this->user->is_loggedin() || $this->cfg['enable_pms'] == 0 ||
				$this->user->get_profile_val('allow_pms') == 0)
			return 'You are a guest, PMs are disabled or you\'ve disabled PMs';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check parameter
		$ids = $this->input->get_var(BS_URL_DEL,'get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($ids)))
			return 'Invalid id-sstring got via GET';

		// delete the user from our banlist
		BS_DAO::get_userbans()->delete_bans_of_user($this->user->get_user_id(),$ids);

		$this->set_action_performed(true);
		$this->add_link(
			$this->locale->lang('back'),$this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=pmbanlist')
		);
		
		return '';
	}
}
?>