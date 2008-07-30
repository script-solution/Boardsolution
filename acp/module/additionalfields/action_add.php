<?php
/**
 * Contains the add-additionalfields-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add-additionalfields-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_additionalfields_add extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$helper = BS_ACP_Module_AdditionalFields_Helper::get_instance();
		
		$values = array();
		$result = $helper->retrieve_valid_field_attributes(0,'add',$values);
		if($result != '')
			return $result;

		BS_DAO::get_profile()->add_additional_fields(
			$values['field_name'],$values['field_type'],$values['field_length']
		);

		$values['field_sort'] = $cache->get_cache('user_fields')->get_element_count() + 1;
		BS_DAO::get_addfields()->create($values);
		$cache->refresh('user_fields');
		
		$this->set_success_msg($locale->lang('field_created_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>