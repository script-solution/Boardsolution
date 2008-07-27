<?php
/**
 * Contains the unsubscribe-action
 *
 * @version			$Id$
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
		$user = PLIB_Props::get()->user();
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();
		$functions = PLIB_Props::get()->functions();
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		// has the user the permission to unsubscribe forums?
		if(!$user->is_loggedin() || $cfg['enable_email_notification'] == 0 ||
			 ($type == 'forums' && !$auth->has_global_permission('subscribe_forums')))
			return 'You are a guest, subscriptions are disabled or you have no permission
				to subscribe to forums';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check the parameter
		$id_str = $input->get_var(BS_URL_DEL,'get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-array via POST';

		BS_DAO::get_subscr()->delete_by_ids_from_user($ids,$user->get_user_id());

		$this->set_action_performed(true);
		$site = $input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		$murl = $url->get_url(0,'&amp;'.BS_URL_LOC.'='.$type.'&amp;'.BS_URL_SITE.'='.$site);
		$this->add_link($locale->lang('back'),$murl);

		return '';
	}
}
?>