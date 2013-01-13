<?php
/**
 * Contains the new-mail-action
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * The new-mail-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_new_mail_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$ips = FWS_Props::get()->ips();
		$user = FWS_Props::get()->user();
		$functions = FWS_Props::get()->functions();
		$locale = FWS_Props::get()->locale();
		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		if($cfg['enable_emails'] == 0 || !$auth->has_global_permission('send_mails'))
			return 'Sending emails is disabled or you have no permission to do so';

		$spam_email_on = $auth->is_ipblock_enabled('spam_email');
		if($spam_email_on)
		{
			if($ips->entry_exists('mail'))
				return 'ipmail';
		}

		$receiver_id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$subject = $input->get_var('subject','post',FWS_Input::STRING);
		$text = $input->get_var('text','post',FWS_Input::STRING);
		$content_type = $input->correct_var('content_type','post',FWS_Input::STRING,
			array('plain','html'),'plain');
		$email_address = '';

		if(!$user->is_loggedin())
		{
			$user_name = $input->get_var('user_name','post',FWS_Input::STRING);
			$user_name = trim($user_name);
			if($user_name == '' || !BS_UserUtils::check_username($user_name))
				return 'invalid_username';
	
			$user_email = $input->get_var('email_adr','post',FWS_Input::STRING);
			$user_email = trim($user_email);
			if($user_email == '' || !FWS_StringHelper::is_valid_email($user_email))
				return 'invalid_email';
	
			// check security-code
			if($cfg['use_captcha_for_guests'] == 1 && !$functions->check_security_code(false))
				return 'invalid_security_code';
			
			$email_address = $user_email;
		}
		else
			$email_address = $user->get_profile_val('user_email');

		// receiver valid?
		if($receiver_id == null)
			return 'The receiver-id "'.$receiver_id.'" is invalid';

		$data = BS_DAO::get_profile()->get_user_by_id($receiver_id);
		if($data === false)
			return 'An receiver with id "'.$receiver_id.'" doesn\'t exist!';

		// does the user exist and is his/her email not empty
		// well, this should not happen, but i'm not sure if it was possible to leave the email
		// empty in one of the previous board-versions...so it's better to check it :)
		if($data['allow_board_emails'] == '' || $data['user_email'] == '')
			return 'user_not_found';

		// has the receiver enabled board emails?
		if($data['allow_board_emails'] == 0)
			return 'user_disabled_emails';

		// check the title
		if(trim($subject) == '')
			return 'mailtitelleer';

		// check the text
		if(trim($text)== '')
			return 'posttextleer';
		
		// create an html-email
		if($content_type == 'html')
		{
			// convert to html-code
			$bbcode = new BS_BBCode_Parser($text,'posts',true,true);
			try
			{
				$bbcode->get_message_for_db();
			}
			catch(BS_BBCode_Exception $ex)
			{
				return $ex->getMessage();
			}

			// build text for email
			$bbcode->set_board_path(FWS_Path::outer());
			$bbcode->stripslashes();
			$text = $bbcode->get_message_for_output();
		}

		// try to send the email
		$email = $functions->get_mailer($data['user_email'],$subject,$text);
		$email->set_from($email_address);
		$email->set_content_type('text/'.$content_type);
		if(!$email->send_mail())
		{
			$msg = sprintf($locale->lang('error_mail_error'),$email->get_error_message());
			return $msg;
		}

		$ips->add_entry('mail');

		$this->set_action_performed(true);
		$this->add_link($locale->lang('forumindex'),BS_URL::get_forums_url());

		return '';
	}
}
?>