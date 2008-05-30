<?php
/**
 * Contains the remove-avatar-action
 *
 * @version			$Id: action_removeavatar.php 713 2008-05-20 21:59:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The remove-avatar-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_removeavatar extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// has the user the permission?
		if(!$this->user->is_loggedin() || $this->cfg['enable_avatars'] == 0)
			return 'You are a guest or avatars are disabled';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		// remove the avatar
		BS_DAO::get_profile()->update_user_by_id(array('avatar' => 0),$this->user->get_user_id());
		$this->user->set_profile_val('avatar',0);

		$this->set_action_performed(true);
		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		$url = $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_SITE.'='.$site);
		$this->add_link($this->locale->lang('back'),$url);

		return '';
	}
}
?>