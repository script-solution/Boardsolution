<?php
/**
 * Contains the send-submodule for massemail
 * 
 * @version			$Id: sub_send.php 713 2008-05-20 21:59:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The send sub-module for the massemail-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_massemail_send extends BS_ACP_SubModule implements PLIB_Progress_Listener
{
	public function get_actions()
	{
		return array();
	}
	
	public function run()
	{
		if($this->user->get_session_data('mail_pos') === false)
		{
			if(!$this->_transfer_to_session())
			{
				$this->_report_error();
				return;
			}
		}

		$storage = new PLIB_Progress_Storage_Session('massemail_');
		$this->_pm = new PLIB_Progress_Manager($storage);
		$this->_pm->set_ops_per_cycle(BS_EMAILS_PER_PAGE);
		$this->_pm->add_listener($this);
		
		$task = new BS_ACP_Module_MassEmail_Tasks_SendDefault();
		$this->_pm->run_task($task);
	}

	/**
	 * @see PLIB_Progress_Listener::cycle_finished()
	 *
	 * @param int $pos
	 * @param int $total
	 */
	public function cycle_finished($pos,$total)
	{
		$this->_populate_template();
	}

	/**
	 * @see PLIB_Progress_Listener::progress_finished()
	 */
	public function progress_finished()
	{
		$error_msgs = $this->user->get_session_data('mail_errors');
		if(count($error_msgs) > 0)
		{
			$lines = implode('<br />',array_keys($error_msgs));
			$this->msgs->add_error(sprintf($this->locale->lang('email_send_error'),$lines));
		}
		else
			$this->msgs->add_notice($this->locale->lang('email_send_success'));
		
		$this->_populate_template();
		
		$this->user->delete_session_data(
			array('mail_pos','mail_total','mail_groups','mail_subject','mail_text','mail_content_type',
						'mail_send_failed','mail_send_success','mail_errors','mail_user')
		);
	}
	
	/**
	 * Adds the variables to the template
	 */
	private function _populate_template()
	{
		$total = $this->user->get_session_data('mail_total');
		$success = $this->user->get_session_data('mail_send_success');
		$failed = $this->user->get_session_data('mail_send_failed');
		$message = sprintf($this->locale->lang('x_mails_sent'),$success + $failed,$total,$success,$failed);
		
		$this->tpl->add_variables(array(
			'not_finished' => !$this->_pm->is_finished(),
			'title' => $this->locale->lang('send_emails_process'),
			'percent' => round($this->_pm->get_percentage(),1),
			'message' => $message,
			'target_url' => $this->url->get_acpmod_url(0,'&action=send','&')
		));
	}

	/**
	 * Transfers the mails to send to the session
	 */
	private function _transfer_to_session()
	{
		$receiver = BS_ACP_Module_MassEmail_Helper::get_instance()->get_receiver();
		if(count($receiver['groups']) == 0 && count($receiver['user']) == 0)
			return false;

		$this->user->set_session_data(
			'mail_subject',$this->input->get_var('subject','post',PLIB_Input::STRING)
		);
		$this->user->set_session_data(
			'mail_text',BS_ACP_Module_MassEmail_Helper::get_instance()->get_mail_text()
		);
		$this->user->set_session_data(
			'mail_content_type',$this->input->get_var('content_type','post',PLIB_Input::STRING)
		);
		$this->user->set_session_data('mail_groups',$receiver['groups']);
		$this->user->set_session_data('mail_user',$receiver['user']);
		$this->user->set_session_data('mail_pos',0);

		$total = BS_DAO::get_user()->get_users_by_groups_count($receiver['groups'],$receiver['user']);
		$this->user->set_session_data('mail_total',$total);
		$this->user->set_session_data('mail_send_success',0);
		$this->user->set_session_data('mail_send_failed',0);

		return true;
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('send_emails_process') => ''
		);
	}
}
?>