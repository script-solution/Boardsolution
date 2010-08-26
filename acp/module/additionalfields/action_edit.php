<?php
/**
 * Contains the edit-additionalfields-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-additionalfields-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_additionalfields_edit extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';
		
		$values = array();
		$result = BS_ACP_Module_AdditionalFields_Helper::retrieve_valid_field_attributes($id,'edit',$values);
		if($result != '')
			return $result;

		$data = $cache->get_cache('user_fields')->get_element($id);
		if($data['field_name'] != 'birthday' && 
				($data['field_name'] != $values['field_name'] ||
			 	$data['field_type'] != $values['field_type'] ||
			 	$data['field_length'] != $values['field_length']))
		{
			BS_DAO::get_profile()->change_additional_field(
				$data['field_name'],$values['field_name'],$values['field_type'],$values['field_length']
			);
		}

		BS_DAO::get_addfields()->update($id,$values);
		$cache->refresh('user_fields');
		
		$this->set_success_msg($locale->lang('field_edit_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>