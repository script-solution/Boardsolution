<?php
/**
 * Contains the send-default-task for the massemail module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
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
 * The task to send the emails with the default method, that means one email per user.
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_MassEmail_Tasks_SendDefault extends FWS_Object implements FWS_Progress_Task
{
	/**
	 * @see FWS_Progress_Task::get_total_operations()
	 *
	 * @return int
	 */
	public function get_total_operations()
	{
		$user = FWS_Props::get()->user();

		return $user->get_session_data('mail_total');
	}

	/**
	 * @see FWS_Progress_Task::run()
	 *
	 * @param int $pos
	 * @param int $ops
	 */
	public function run($pos,$ops)
	{
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$user = FWS_Props::get()->user();

		$text = $user->get_session_data('mail_text');
		$subject = $user->get_session_data('mail_subject');
		$content_type = $user->get_session_data('mail_content_type');
		$success = $user->get_session_data('mail_send_success');
		$failed = $user->get_session_data('mail_send_failed');
		$groups = $user->get_session_data('mail_groups');
		$ruser = $user->get_session_data('mail_user');

		if(count($groups) == 0 && count($ruser) == 0)
		{
			$msgs->add_error($locale->lang('invalid_page'));
			return;
		}
		
		$error_msgs = $user->get_session_data('mail_errors');
		if($error_msgs === false)
			$error_msgs = array();
		
		$users = BS_DAO::get_user()->get_users_by_groups($groups,$ruser,$pos,$ops);
		foreach($users as $data)
		{
			$m_text = str_replace("{username}",$data['user_name'],$text);
			$mail = $functions->get_mailer($data['user_email'],$subject,$m_text);
			if($content_type == 'html')
				$mail->set_content_type('text/html');

			if($mail->send_mail())
				$success++;
			else
			{
				$error = $mail->get_error_message();
				if(!isset($error_msgs[$error]))
					$error_msgs[$error] = true;
				
				$failed++;
			}
		}
		
		$user->set_session_data('mail_send_success',$success);
		$user->set_session_data('mail_send_failed',$failed);
		$user->set_session_data('mail_errors',$error_msgs);
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>