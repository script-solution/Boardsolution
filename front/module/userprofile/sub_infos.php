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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_EDIT_PERS_PROFILE,'updateinfos');

		$renderer->add_breadcrumb($locale->lang('personal_info'),$url->get_url(0,'&amp;'.BS_URL_LOC.'=infos'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$msgs = PLIB_Props::get()->msgs();
		$locale = PLIB_Props::get()->locale();
		$user = PLIB_Props::get()->user();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$url = PLIB_Props::get()->url();

		$loc = $input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
		
		if($input->get_var(BS_URL_MODE,'get') == 1)
			$msgs->add_notice($locale->lang('fill_required_fields_notice'));
		
		$form = $this->request_formular(false);
		
		$email_change = BS_DAO::get_changeemail()->get_by_user($user->get_user_id());
		
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_EDIT_PERS_PROFILE,
			'show_email' => !BS_ENABLE_EXPORT && $cfg['allow_email_changes'],
			'email_value' => $form->get_input_value('user_email',$user->get_profile_val('user_email')),
			'target_url' => $url->get_url('userprofile','&amp;'.BS_URL_LOC."=".$loc),
			'confirm_emails' => $cfg['confirm_email_addresses'],
			'new_email_address' => $email_change['user_id'] ? $email_change['email_address'] : ''
		));
		
		$cfields = BS_AddField_Manager::get_instance();

		$additional_fields = array();
		foreach($cfields->get_fields_at(BS_UF_LOC_USER_PROFILE) as $field)
		{
			/* @var $field PLIB_AddField_Field */
			$data = $field->get_data();
			$stored_val = $user->get_profile_val('add_'.$data->get_name());
			$value = $field->get_value_from_formular($stored_val);

			$additional_fields[] = array(
				'required_field' => $data->is_required() ? ' *' : '',
				'field_name' => $field->get_title(),
				'field_value' => $field->get_formular_field($form,$value)
			);
		}
		
		$tpl->add_array('additional_fields',$additional_fields,false);
	}
}
?>