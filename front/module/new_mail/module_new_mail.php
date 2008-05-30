<?php
/**
 * Contains the new-mail-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The new-mail-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_new_mail extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_SEND_EMAIL => 'default'
		);
	}
	
	public function run()
	{
		// check if the id is valid
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		if($id == null)
		{
			$this->_report_error();
			return;
		}

		$data = BS_DAO::get_profile()->get_user_by_id($id);
		
		// check if the user has been found
		if($data === false)
		{
			$this->_report_error();
			return;
		}

		// check if the user has allowed board emails
		if($data['allow_board_emails'] == 0)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('user_disabled_emails'));
			return;
		}

		$form = $this->_request_formular(false,true);
		
		if($this->input->isset_var('preview','post'))
		{
			$content_type_val = $form->get_input_value('content_type','plain');
			BS_PostingUtils::get_instance()->add_post_preview(
				'posts',$content_type_val == 'html',$content_type_val == 'html'
			);
		}
		
		$pform = new BS_PostingForm($this->locale->lang('text').':');
		$pform->add_form();
		
		$sec_code_field = PLIB_StringHelper::generate_random_key(15);
		$this->user->set_session_data('sec_code_field',$sec_code_field);
		
		$content_type_options = array(
			'plain' => $this->locale->lang('content_type_plain'),
			'html' => $this->locale->lang('content_type_html')
		);
		
		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url('new_mail','&amp;'.BS_URL_ID.'='.$id),
			'receiver' => BS_UserUtils::get_instance()->get_link($id,$data['user_name'],$data['user_group']),
			'action_type' => BS_ACTION_SEND_EMAIL,
			'content_type_options' => $content_type_options
		));
	}

	public function get_location()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$url = $this->url->get_url('new_mail','&amp;'.BS_URL_ID.'='.$id);
		return array($this->locale->lang('email') => $url);
	}

	public function has_access()
	{
		return $this->auth->has_global_permission('send_mails') && $this->cfg['enable_emails'] == 1;
	}
}
?>