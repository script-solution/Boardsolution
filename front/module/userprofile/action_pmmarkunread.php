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
		// allowed to view pms?
		if(!$this->user->is_loggedin() || $this->cfg['enable_pms'] == 0 ||
				$this->user->get_profile_val('allow_pms') == 0)
			return 'You are a guest, PMs are disabled or you\'ve disabled PMs';

		// valid input?
		$delete = $this->input->get_var("delete","post");
		if($delete == null)
			$delete = PLIB_Array_Utils::advanced_explode(',',$this->input->get_var(BS_URL_DEL,'get',PLIB_Input::STRING));

		if(!PLIB_Array_Utils::is_integer($delete) || count($delete) == 0)
			return 'Got an invalid id-array via POST';

		// update db
		BS_DAO::get_pms()->set_read_flag($delete,$this->user->get_user_id(),0);

		// finish
		$this->set_action_performed(true);
		$loc = $this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
		$url = $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'='.$loc);
		$this->add_link($this->locale->lang('back'),$url);

		return '';
	}
}
?>