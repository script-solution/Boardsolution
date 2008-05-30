<?php
/**
 * Contains the unsubscribe-action
 *
 * @version			$Id: action_unsubscribe.php 737 2008-05-23 18:26:46Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The unsubscribe-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_unsubscribe extends BS_Front_Action_Base
{
	public function perform_action($type = 'forums')
	{
		// has the user the permission to unsubscribe forums?
		if(!$this->user->is_loggedin() || $this->cfg['enable_email_notification'] == 0 ||
			 ($type == 'forums' && !$this->auth->has_global_permission('subscribe_forums')))
			return 'You are a guest, subscriptions are disabled or you have no permission
				to subscribe to forums';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check the parameter
		$id_str = $this->input->get_var(BS_URL_DEL,'get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-array via POST';

		BS_DAO::get_subscr()->delete_by_ids_from_user($ids,$this->user->get_user_id());

		$this->set_action_performed(true);
		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		$url = $this->url->get_url(0,'&amp;'.BS_URL_LOC.'='.$type.'&amp;'.BS_URL_SITE.'='.$site);
		$this->add_link($this->locale->lang('back'),$url);

		return '';
	}
}
?>