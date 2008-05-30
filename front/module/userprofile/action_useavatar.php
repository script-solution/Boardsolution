<?php
/**
 * Contains the use-avatar-action
 *
 * @version			$Id: action_useavatar.php 717 2008-05-21 14:12:53Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The use-avatar-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_useavatar extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// has the user the permission?
		if(!$this->user->is_loggedin() || $this->cfg['enable_avatars'] == 0)
			return 'You are a guest or avatars are disabled';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		// does the avatar exist?
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		if($id == null)
			return 'The id "'.$id.'" is invalid';
		
		$avatar = BS_DAO::get_avatars()->get_by_id($id);
		if($avatar === false)
			return 'The avatar with id "'.$id.'" doesn\'t exist';
		
		if($avatar['user'] != 0 && $avatar['user'] != $this->user->get_user_id())
			return 'The avatar with id "'.$id.'" is not yours';
		
		// change the avatar
		BS_DAO::get_profile()->update_user_by_id(array('avatar' => $id),$this->user->get_user_id());
		$this->user->set_profile_val('avatar',$id);

		$this->set_action_performed(true);
		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		$url = $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_SITE.'='.$site);
		$this->add_link($this->locale->lang('back'),$url);

		return '';
	}
}
?>