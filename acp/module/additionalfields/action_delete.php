<?php
/**
 * Contains the delete-additionalfields-action
 *
 * @version			$Id$
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
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$id_str = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		foreach($ids as $id)
		{
			$data = $cache->get_cache('user_fields')->get_element($id);
			if($data != null)
			{
				if($data['field_name'] == 'birthday')
					continue;
				
				BS_DAO::get_profile()->delete_additional_fields($data['field_name']);
				BS_DAO::get_addfields()->delete($id);
				BS_DAO::get_addfields()->dec_sort($data['field_sort']);
				
				// TODO: improve this!?
				$cache->refresh('user_fields');
			}
		}
		
		$this->set_success_msg($locale->lang('field_delete_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>