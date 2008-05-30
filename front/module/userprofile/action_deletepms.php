<?php
/**
 * Contains the delete-pms-action
 *
 * @version			$Id: action_deletepms.php 765 2008-05-24 21:14:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-pms-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_deletepms extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$del = $this->input->get_var(BS_URL_DEL,"get",PLIB_Input::STRING);
		if(!$this->user->is_loggedin() || $this->cfg['enable_pms'] == 0 || $del == null ||
				$this->user->get_profile_val('allow_pms') == 0)
			return 'You are a guest, PMs are disabled, no ids have been given or you\'ve disabled PMs';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		if(!($ids = PLIB_StringHelper::get_ids($del)))
			return 'Invalid id-string got via GET';

		// collect attachments
		foreach(BS_DAO::get_attachments()->get_by_pmids($ids) as $data)
		{
			// PM-attachments are used for 2 PMs: the PM of the sender and the PM of the receiver
			// Therefore we delete the attachment-file as soon as both PMs have been deleted
			if(BS_DAO::get_attachments()->get_attachment_count_of_path($data['attachment_path']) == 1)
				$this->functions->delete_attachment($data['attachment_path']);
		}

		BS_DAO::get_attachments()->delete_by_pmids($ids);

		BS_DAO::get_pms()->delete_pms_of_user($ids,$this->user->get_user_id());

		// finish
		$this->set_action_performed(true);
		$loc = $this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
		if($loc == 'pmsearch')
		{
			$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
			$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::ID);
			$url = $this->url->get_url(
				0,'&amp;'.BS_URL_LOC.'=pmsearch&amp;'.BS_URL_ID.'='.$id.'&amp;'.BS_URL_SITE.'='.$site
			);
		}
		else
			$url = $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'='.$loc);
		
		$this->add_link($this->locale->lang('back'),$url);

		return '';
	}
}
?>