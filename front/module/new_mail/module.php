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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$auth = FWS_Props::get()->auth();
		$cfg = FWS_Props::get()->cfg();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($auth->has_global_permission('send_mails') && $cfg['enable_emails'] == 1);
		
		$renderer->add_action(BS_ACTION_SEND_EMAIL,'default');

		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$renderer->add_breadcrumb(
			$locale->lang('email'),
			BS_URL::get_url('new_mail','&amp;'.BS_URL_ID.'='.$id)
		);
	}
	
	/**
	 * @see BS_Front_Module::is_guest_only()
	 *
	 * @return boolean
	 */
	public function is_guest_only()
	{
		return true;
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		// check if the id is valid
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		if($id == null)
		{
			$this->report_error();
			return;
		}

		$data = BS_DAO::get_profile()->get_user_by_id($id);
		
		// check if the user has been found
		if($data === false)
		{
			$this->report_error();
			return;
		}

		// check if the user has allowed board emails
		if($data['allow_board_emails'] == 0)
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('user_disabled_emails'));
			return;
		}

		$form = $this->request_formular(false,true);
		
		if($input->isset_var('preview','post'))
		{
			$content_type_val = $form->get_input_value('content_type','plain');
			BS_PostingUtils::get_instance()->add_post_preview(
				'posts',$content_type_val == 'html',$content_type_val == 'html'
			);
		}
		
		$pform = new BS_PostingForm($locale->lang('text').':');
		$pform->add_form();
		
		$sec_code_field = FWS_StringHelper::generate_random_key(15);
		$user->set_session_data('sec_code_field',$sec_code_field);
		
		$content_type_options = array(
			'plain' => $locale->lang('content_type_plain'),
			'html' => $locale->lang('content_type_html')
		);
		
		$tpl->add_variables(array(
			'target_url' => BS_URL::get_url('new_mail','&amp;'.BS_URL_ID.'='.$id),
			'receiver' => BS_UserUtils::get_instance()->get_link($id,$data['user_name'],$data['user_group']),
			'action_type' => BS_ACTION_SEND_EMAIL,
			'content_type_options' => $content_type_options
		));
	}
}
?>