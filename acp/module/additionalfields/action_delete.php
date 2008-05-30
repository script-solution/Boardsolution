<?php
/**
 * Contains the delete-additionalfields-action
 *
 * @version			$Id: action_delete.php 714 2008-05-20 22:14:58Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-additionalfields-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_additionalfields_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$id_str = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		foreach($ids as $id)
		{
			$data = $this->cache->get_cache('user_fields')->get_element($id);
			if($data != null)
			{
				if($data['field_name'] == 'birthday')
					continue;
				
				BS_DAO::get_profile()->delete_additional_fields($data['field_name']);
				BS_DAO::get_addfields()->delete($id);
				BS_DAO::get_addfields()->dec_sort($data['field_sort']);
				
				// TODO: improve this!?
				$this->cache->refresh('user_fields');
			}
		}
		
		$this->set_success_msg($this->locale->lang('field_delete_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>