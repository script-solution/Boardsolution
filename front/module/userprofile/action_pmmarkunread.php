<?php
/**
 * Contains the pm-mark-unread-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pm-mark-unread-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_pmmarkunread extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		// allowed to view pms?
		if(!$user->is_loggedin() || $cfg['enable_pms'] == 0 ||
				$user->get_profile_val('allow_pms') == 0)
			return 'You are a guest, PMs are disabled or you\'ve disabled PMs';

		// valid input?
		$delete = $input->get_var("delete","post");
		if($delete == null)
			$delete = FWS_Array_Utils::advanced_explode(',',$input->get_var(BS_URL_DEL,'get',FWS_Input::STRING));

		if(!FWS_Array_Utils::is_integer($delete) || count($delete) == 0)
			return 'Got an invalid id-array via POST';

		// update db
		BS_DAO::get_pms()->set_read_flag($delete,$user->get_user_id(),0);

		// finish
		$this->set_action_performed(true);
		$loc = $input->get_var(BS_URL_LOC,'get',FWS_Input::STRING);
		$murl = BS_URL::get_url('userprofile','&amp;'.BS_URL_LOC.'='.$loc);
		$this->add_link($locale->lang('back'),$murl);

		return '';
	}
}
?>