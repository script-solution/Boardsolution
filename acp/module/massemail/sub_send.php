<?php
/**
 * Contains the send-submodule for massemail
 * 
 * @version			$Id$
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
final class BS_ACP_SubModule_massemail_send extends BS_ACP_SubModule implements FWS_Progress_Listener
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_ACPACCESS_MODULE,'module');
		$renderer->add_breadcrumb($locale->lang('send_emails_process'));
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$user = FWS_Props::get()->user();

		if($user->get_session_data('mail_pos') === false)
		{
			if(!$this->_transfer_to_session())
			{
				$this->report_error();
				return;
			}
		}

		$storage = new FWS_Progress_Storage_Session('massemail_');
		$this->_pm = new FWS_Progress_Manager($storage);
		$this->_pm->set_ops_per_cycle(BS_EMAILS_PER_PAGE);
		$this->_pm->add_listener($this);
		
		$task = new BS_ACP_Module_MassEmail_Tasks_SendDefault();
		$this->_pm->run_task($task);
	}

	/**
	 * @see FWS_Progress_Listener::cycle_finished()
	 *
	 * @param int $pos
	 * @param int $total
	 */
	public function cycle_finished($pos,$total)
	{
		$this->_populate_template();
	}

	/**
	 * @see FWS_Progress_Listener::progress_finished()
	 */
	public function progress_finished()
	{
		$user = FWS_Props::get()->user();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();

		$error_msgs = $user->get_session_data('mail_errors');
		if(count($error_msgs) > 0)
		{
			$lines = implode('<br />',array_keys($error_msgs));
			$msgs->add_error(sprintf($locale->lang('email_send_error'),$lines));
		}
		else
			$msgs->add_notice($locale->lang('email_send_success'));
		
		$this->_populate_template();
		
		$user->delete_session_data(
			array('mail_pos','mail_total','mail_groups','mail_subject','mail_text','mail_content_type',
						'mail_send_failed','mail_send_success','mail_errors','mail_user')
		);
	}
	
	/**
	 * Adds the variables to the template
	 */
	private function _populate_template()
	{
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$url = FWS_Props::get()->url();

		$total = $user->get_session_data('mail_total');
		$success = $user->get_session_data('mail_send_success');
		$failed = $user->get_session_data('mail_send_failed');
		$message = sprintf($locale->lang('x_mails_sent'),$success + $failed,$total,$success,$failed);
		
		$tpl->add_variables(array(
			'not_finished' => !$this->_pm->is_finished(),
			'title' => $locale->lang('send_emails_process'),
			'percent' => round($this->_pm->get_percentage(),1),
			'message' => $message,
			'target_url' => $url->get_acpmod_url(0,'&action=send','&')
		));
	}

	/**
	 * Transfers the mails to send to the session
	 */
	private function _transfer_to_session()
	{
		$user = FWS_Props::get()->user();
		$input = FWS_Props::get()->input();

		$receiver = BS_ACP_Module_MassEmail_Helper::get_instance()->get_receiver();
		if(count($receiver['groups']) == 0 && count($receiver['user']) == 0)
			return false;

		$user->set_session_data(
			'mail_subject',$input->get_var('subject','post',FWS_Input::STRING)
		);
		$user->set_session_data(
			'mail_text',BS_ACP_Module_MassEmail_Helper::get_instance()->get_mail_text()
		);
		$user->set_session_data(
			'mail_content_type',$input->get_var('content_type','post',FWS_Input::STRING)
		);
		$user->set_session_data('mail_groups',$receiver['groups']);
		$user->set_session_data('mail_user',$receiver['user']);
		$user->set_session_data('mail_pos',0);

		$total = BS_DAO::get_user()->get_users_by_groups_count($receiver['groups'],$receiver['user']);
		$user->set_session_data('mail_total',$total);
		$user->set_session_data('mail_send_success',0);
		$user->set_session_data('mail_send_failed',0);

		return true;
	}
}
?>