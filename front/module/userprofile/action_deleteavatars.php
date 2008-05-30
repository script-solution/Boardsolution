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
		// has the user the permission?
		if(!$this->user->is_loggedin() || $this->cfg['enable_avatars'] == 0)
			return 'You are a guest or avatars are disabled';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check the ids
		$id_str = $this->input->get_var(BS_URL_DEL,'get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Invalid id-string got via GET';

		// delete the avatars from the directory images/avatars
		foreach(BS_DAO::get_avatars()->get_by_ids_from_user($ids,$this->user->get_user_id()) as $data)
			@unlink(PLIB_Path::inner().'images/avatars/'.$data['av_pfad']);

		// delete them in the database
		BS_DAO::get_avatars()->delete_by_ids_from_user($ids,$this->user->get_user_id());

		// remove the avatar of the user if it has just been deleted
		if(in_array($this->user->get_profile_val('avatar'),$ids))
		{
			BS_DAO::get_profile()->update_user_by_id(array('avatar' => 0),$this->user->get_user_id());
			$this->user->set_profile_val('avatar',0);
		}

		$this->set_action_performed(true);
		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		$url = $this->url->get_url(
			'userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_SITE.'='.$site
		);
		$this->add_link($this->locale->lang('back'),$url);

		return '';
	}
}
?>