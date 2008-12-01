<?php
/**
 * Contains the use-avatar-action
 *
 * @version			$Id$
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
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		// has the user the permission?
		if(!$user->is_loggedin() || $cfg['enable_avatars'] == 0)
			return 'You are a guest or avatars are disabled';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// does the avatar exist?
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		if($id == null)
			return 'The id "'.$id.'" is invalid';
		
		$avatar = BS_DAO::get_avatars()->get_by_id($id);
		if($avatar === false)
			return 'The avatar with id "'.$id.'" doesn\'t exist';
		
		if($avatar['user'] != 0 && $avatar['user'] != $user->get_user_id())
			return 'The avatar with id "'.$id.'" is not yours';
		
		// change the avatar
		BS_DAO::get_profile()->update_user_by_id(array('avatar' => $id),$user->get_user_id());
		$user->set_profile_val('avatar',$id);

		$this->set_action_performed(true);
		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
		$url = BS_URL::get_sub_url('userprofile','avatars');
		$url->set(BS_URL_SITE,$site);
		$this->add_link($locale->lang('back'),$url);

		return '';
	}
}
?>