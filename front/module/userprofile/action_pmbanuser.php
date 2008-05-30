<?php
/**
 * Contains the pm-ban-user-action
 *
 * @version			$Id: action_pmbanuser.php 756 2008-05-24 18:20:13Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pm-ban-user-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_pmbanuser extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// check if we are allowed to ban a user
		if(!$this->user->is_loggedin() || $this->cfg['enable_pms'] == 0 ||
				$this->user->get_profile_val('allow_pms') == 0)
			return 'You are a guest, PMs are disabled or you\'ve disabled PMs';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		// if the id does not exist, check if a username does
		if($id == null)
		{
			$user = $this->input->get_var('user_name','post',PLIB_Input::STRING);
			if($user == null)
				return 'banlist_user_not_found';

			// check if a user with this name exists
			$data = BS_DAO::get_user()->get_user_by_name($user);
			if($data === false)
				return 'banlist_user_not_found';

			$id = $data['id'];
		}
		// otherwise check if the id exists
		else if(BS_DAO::get_user()->id_exists($id))
			return 'banlist_user_not_found';

		// we do not want to ban ourself ;)
		if($id == $this->user->get_user_id())
			return 'banlist_self';

		// check if the user is already on our banlist
		if(BS_DAO::get_userbans()->has_baned($this->user->get_user_id(),$id))
			return 'banlist_user_exists';

		// everything ok, so ban the user
		BS_DAO::get_userbans()->create($this->user->get_user_id(),$id);

		$this->set_action_performed(true);
		$this->add_link(
			$this->locale->lang('back'),$this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=pmbanlist')
		);

		return '';
	}
}
?>