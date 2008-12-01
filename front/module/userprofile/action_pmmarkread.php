<?php
/**
 * Contains the pm-mark-read-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pm-mark-read-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_pmmarkread extends BS_Front_Action_Base
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
		$delete = $input->get_var('delete','post');
		if($delete == null)
			$delete = FWS_Array_Utils::advanced_explode(',',$input->get_var(BS_URL_DEL,'get',FWS_Input::STRING));

		if(!FWS_Array_Utils::is_integer($delete) || count($delete) == 0)
			return 'Got an invalid id-array via POST';

		// update db
		BS_DAO::get_pms()->set_read_flag($delete,$user->get_user_id(),1);

		// finish
		$this->set_action_performed(true);
		$this->add_link($locale->lang('back'),BS_URL::get_sub_url('userprofile',0));

		return '';
	}
}
?>