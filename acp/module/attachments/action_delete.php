<?php
/**
 * Contains the delete-attachments-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-attachments-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_attachments_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$path_str = $input->get_var('ids','get',FWS_Input::STRING);
		$paths = FWS_Array_Utils::advanced_explode('|',$path_str);
		if(count($paths) == 0)
			return 'Got no paths via GET ("ids")';
		
		// ensure that the paths are correct
		foreach($paths as $k => $path)
			$paths[$k] = 'uploads/'.basename($path);
		
		// grab all valid ids from db
		$ids = array();
		foreach(BS_DAO::get_attachments()->get_by_paths($paths) as $data)
			$ids[] = $data['id'];
		
		// delete files
		foreach($paths as $path)
			@unlink(FWS_Path::server_app().$path);
		
		// delete db-attachments
		if(count($ids) > 0)
			BS_DAO::get_attachments()->delete_by_ids($ids);

		$this->set_success_msg($locale->lang('attachments_delete_successfull'));
		$this->set_action_performed(true);

		return '';
	}
}
?>