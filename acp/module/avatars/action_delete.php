<?php
/**
 * Contains the delete-avatars-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-avatars-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_avatars_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$id_str = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		foreach(BS_DAO::get_avatars()->get_by_ids($ids) as $data)
			@unlink(FWS_Path::server_app().'images/avatars/'.$data['av_pfad']);

		BS_DAO::get_avatars()->delete_by_ids($ids);
			
		$this->set_success_msg($locale->lang('avatars_deleted_successfully'));
		$this->set_action_performed(true);

		return '';
	}
}
?>