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
		$id_str = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		foreach(BS_DAO::get_avatars()->get_by_ids($ids) as $data)
			@unlink(PLIB_Path::inner().'images/avatars/'.$data['av_pfad']);

		BS_DAO::get_avatars()->delete_by_ids($ids);
			
		$this->set_success_msg($this->locale->lang('avatars_deleted_successfully'));
		$this->set_action_performed(true);

		return '';
	}
}
?>