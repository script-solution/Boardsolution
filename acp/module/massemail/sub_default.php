<?php
/**
 * Contains the default-submodule for massemail
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the massemail-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_massemail_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array();
	}
	
	public function run()
	{
		// we have to clear the position here to ensure that we will start again
		// if the last progress hasn't be cleaned up, however.
		$storage = new PLIB_Progress_Storage_Session('massemail_');
		$storage->clear();
		
		// has the form have been submitted?
		$error = false;
		if($this->input->get_var('selectedButton','post',PLIB_Input::STRING) == 'submit')
		{
			if(!$this->_check_formular())
				$error = true;
		}
		
		// show preview?
		$show_preview = $this->input->get_var('selectedButton','post',PLIB_Input::STRING) == 'preview';
		if($show_preview)
		{
			$content_type = $this->input->get_var('content_type','post',PLIB_Input::STRING);
			$res = BS_PostingUtils::get_instance()->get_post_preview_text(
				'posts',$content_type == 'html',$content_type == 'html'
			);
			if($res['error'])
				$this->msgs->add_error($res['error']);
			else
			{
				$this->tpl->add_variables(array(
					'show_preview' => true,
					'preview_text' => $res['text']
				));
			}
		}
		
		$form = $this->_request_formular(false,false);
		if(!$form->get_condition())
			$form->set_condition($error || $show_preview);
		
		// add text form
		$pform = new BS_PostingForm($this->locale->lang('description').':','','posts');
		$pform->set_formular($form);
		$pform->add_form();
		
		// set colspan for the post-form-template
		$this->tpl->set_template('inc_post_form.htm');
		$this->tpl->add_variables(array(
			'colspan_main' => 1
		));
		$this->tpl->restore_template();
		
		// load vars
		$user_ids = PLIB_Array_Utils::advanced_explode(',',$this->input->get_var('recipient_user','post'));
		if(!PLIB_Array_Utils::is_integer($user_ids))
			$user_ids = array();
		
		// grab user-names from db
		$user = array();
		if(count($user_ids) > 0)
		{
			foreach(BS_DAO::get_user()->get_users_by_ids($user_ids) as $data)
				$user[$data['id']] = $data['user_name'];
		}
		
		// collect user-groups
		$groups = array();
		foreach($this->cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] == BS_STATUS_GUEST)
				continue;
				
			$groups[$gdata['id']] = $gdata['group_title'];
		}

		$content_type_options = array(
			'plain' => $this->locale->lang('email_content_type_plain'),
			'html' => $this->locale->lang('email_content_type_html')
		);
		$method_options = array(
			'BCC' => $this->locale->lang('email_method_bcc'),
			'default' => $this->locale->lang('email_method_default')
		);
		
		// build user and group combos
		$user_combo = new PLIB_HTML_ComboBox(
			'user','user_intern',array(),null,5,true
		);
		$user_combo->set_options($user);
		$user_combo->set_css_attribute('width','100%');
		
		$sel_groups = $form->get_condition() ? $this->input->get_var('recipient_groups','post') : array();
		if(!is_array($sel_groups))
			$sel_groups = array();
		
		$group_combo = new PLIB_HTML_ComboBox(
			'recipient_groups[]','recipient_groups',$sel_groups,null,count($groups),true
		);
		$group_combo->set_options($groups);
		$group_combo->set_css_attribute('width','100%');
		
		$this->tpl->add_variables(array(
			'groups_combo' => $group_combo->to_html(),
			'user_combo' => $user_combo->to_html(),
			'content_type_options' => $content_type_options,
			'method_options' => $method_options,
			'search_url' => $this->url->get_standalone_url('acp','user_search','&amp;comboid=user_intern'),
		));
	}
	
	/**
	 * Checks the formular and performs the appropriate action
	 *
	 * @return boolean true if no error has occurred
	 */
	private function _check_formular()
	{
		$subject = $this->input->get_var('subject','post',PLIB_Input::STRING);
		if($subject == '')
		{
			$this->msgs->add_error($this->locale->lang('mass_email_missing_subject'));
			return false;
		}
		
		$text = $this->input->get_var('text','post',PLIB_Input::STRING);
		if($text == '')
		{
			$this->msgs->add_error($this->locale->lang('mass_email_missing_text'));
			return false;
		}
		
		$receiver = BS_ACP_Module_MassEmail_Helper::get_instance()->get_receiver();
		if(count($receiver['user']) == 0 && count($receiver['groups']) == 0)
		{
			$this->msgs->add_error($this->locale->lang('mass_email_missing_receiver'));
			return false;
		}
		
		// everything is ok, so we can continue :)
		if($this->input->get_var('method','post',PLIB_Input::STRING) == 'BCC')
		{
			$res = $this->_send_emails_bcc();
			if($res !== true)
				$this->msgs->add_error($res);
			else
				$this->msgs->add_notice($this->locale->lang('email_send_success'));
		}
		else
		{
			// we have to undo addslashes()
			$postvars = $this->input->get_vars_from_method('post');
			foreach($postvars as $k => $v)
			{
				if(!is_array($v))
					$postvars[$k] = stripslashes($v);
			}
			
			$this->tpl->add_variables(array(
				'show_confirmation' => true,
				'postvars' => $postvars
			));
		}
		
		return true;
	}

	/**
	 * Sends the mail via BCC
	 * 
	 * @return string the error-message or true
	 */
	private function _send_emails_bcc()
	{
		$receiver = BS_ACP_Module_MassEmail_Helper::get_instance()->get_receiver();
		if(count($receiver['groups']) == 0 && count($receiver['user']) == 0)
			return 'No receiver set';

		$subject = $this->input->get_var('subject','post',PLIB_Input::STRING);
		$text = BS_ACP_Module_MassEmail_Helper::get_instance()->get_mail_text();
		$mail = $this->functions->get_mailer('',$subject,$text);

		if($this->input->get_var('content_type','post',PLIB_Input::STRING) == 'html')
			$mail->set_content_type('text/html');

		$user = BS_DAO::get_user()->get_users_by_groups($receiver['groups'],$receiver['user']);
		foreach($user as $data)
		{
			if($data['user_email'] != '')
				$mail->add_bcc_recipient($data['user_email']);
		}

		if(!$mail->send_mail())
			return $mail->get_error_message();
		
		return true;
	}
	
	public function get_location()
	{
		return array();
	}
}
?>