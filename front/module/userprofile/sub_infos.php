<?php
/**
 * Contains the infos-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The infos submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_infos extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_EDIT_PERS_PROFILE => 'updateinfos'
		);
	}
	
	public function run()
	{
		$loc = $this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
		
		$form = $this->_request_formular(false);
		
		$email_change = BS_DAO::get_changeemail()->get_by_user($this->user->get_user_id());
		
		$this->tpl->add_variables(array(
			'action_type' => BS_ACTION_EDIT_PERS_PROFILE,
			'show_email' => !BS_ENABLE_EXPORT && $this->cfg['allow_email_changes'],
			'email_value' => $form->get_input_value('user_email',$this->user->get_profile_val('user_email')),
			'target_url' => $this->url->get_url('userprofile','&amp;'.BS_URL_LOC."=".$loc),
			'confirm_emails' => $this->cfg['confirm_email_addresses'],
			'new_email_address' => $email_change['user_id'] ? $email_change['email_address'] : ''
		));
		
		$cfields = BS_AddField_Manager::get_instance();

		$additional_fields = array();
		foreach($cfields->get_fields_at(BS_UF_LOC_USER_PROFILE) as $field)
		{
			/* @var $field PLIB_AddField_Field */
			$data = $field->get_data();
			$stored_val = $this->user->get_profile_val('add_'.$data->get_name());
			$value = $field->get_value_from_formular($stored_val);

			$additional_fields[] = array(
				'required_field' => $data->is_required() ? ' *' : '',
				'field_name' => $field->get_title(),
				'field_value' => $field->get_formular_field($form,$value)
			);
		}
		
		$this->tpl->add_array('additional_fields',$additional_fields,false);
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('personal_info') => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=infos')
		);
	}
}
?>