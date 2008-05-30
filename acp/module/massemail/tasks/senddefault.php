<?php
/**
 * Contains the send-default-task for the massemail module
 *
 * @version			$Id: senddefault.php 713 2008-05-20 21:59:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The task to send the emails with the default method, that means one email per user.
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_MassEmail_Tasks_SendDefault extends PLIB_FullObject implements PLIB_Progress_Task
{
	/**
	 * @see PLIB_Progress_Task::get_total_operations()
	 *
	 * @return int
	 */
	public function get_total_operations()
	{
		return $this->user->get_session_data('mail_total');
	}

	/**
	 * @see PLIB_Progress_Task::run()
	 *
	 * @param int $pos
	 * @param int $ops
	 */
	public function run($pos,$ops)
	{
		$text = $this->user->get_session_data('mail_text');
		$subject = $this->user->get_session_data('mail_subject');
		$content_type = $this->user->get_session_data('mail_content_type');
		$success = $this->user->get_session_data('mail_send_success');
		$failed = $this->user->get_session_data('mail_send_failed');
		$groups = $this->user->get_session_data('mail_groups');
		$user = $this->user->get_session_data('mail_user');

		if(count($groups) == 0 && count($user) == 0)
		{
			$this->msgs->add_error($this->locale->lang('invalid_page'));
			return;
		}
		
		$error_msgs = $this->user->get_session_data('mail_errors');
		if($error_msgs === false)
			$error_msgs = array();
		
		$users = BS_DAO::get_user()->get_users_by_groups($groups,$user,$pos,$ops);
		foreach($users as $data)
		{
			$m_text = str_replace("{username}",$data['user_name'],$text);
			$mail = $this->functions->get_mailer($data['user_email'],$subject,$m_text);
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
		
		$this->user->set_session_data('mail_send_success',$success);
		$this->user->set_session_data('mail_send_failed',$failed);
		$this->user->set_session_data('mail_errors',$error_msgs);
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>