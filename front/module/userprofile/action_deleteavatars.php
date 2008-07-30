<?php
/**
 * Contains the delete-avatars-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-avatars-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_deleteavatars extends BS_Front_Action_Base
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

		// check the ids
		$id_str = $input->get_var(BS_URL_DEL,'get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'Invalid id-string got via GET';

		// delete the avatars from the directory images/avatars
		foreach(BS_DAO::get_avatars()->get_by_ids_from_user($ids,$user->get_user_id()) as $data)
			@unlink(FWS_Path::server_app().'images/avatars/'.$data['av_pfad']);

		// delete them in the database
		BS_DAO::get_avatars()->delete_by_ids_from_user($ids,$user->get_user_id());

		// remove the avatar of the user if it has just been deleted
		if(in_array($user->get_profile_val('avatar'),$ids))
		{
			BS_DAO::get_profile()->update_user_by_id(array('avatar' => 0),$user->get_user_id());
			$user->set_profile_val('avatar',0);
		}

		$this->set_action_performed(true);
		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
		$murl = BS_URL::get_url(
			'userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_SITE.'='.$site
		);
		$this->add_link($locale->lang('back'),$murl);

		return '';
	}
}
?>