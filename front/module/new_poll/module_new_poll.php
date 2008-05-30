<?php
/**
 * Contains the new-poll-module
 * 
 * @version			$Id: module_new_poll.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The new-poll-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_new_poll extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_START_POLL => 'default'
		);
	}
	
	public function run()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		if($fid == null)
		{
			$this->_report_error();
			return;
		}
		
		// does the forum exist?
		$forum_data = $this->forums->get_node_data($fid);
		if($forum_data === null || $forum_data->get_forum_type() != 'contains_threads')
		{
			$this->_report_error();
			return;
		}
		
		// forum closed?
		if(!$this->user->is_admin() && $this->forums->forum_is_closed($fid))
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('forum_is_closed'));
			return;
		}
	
		if($this->input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview();
		
		$this->_request_formular(true,true);
		
		$loggedin = $this->user->is_loggedin();
		$subt_def = $loggedin ? $this->user->get_profile_val('default_email_notification') : 0;
		
		$this->tpl->add_variables(array(
			'action_type' => BS_ACTION_START_POLL,
			'target_url' => $this->url->get_url(0,'&amp;'.BS_URL_FID.'='.$fid).'#bottom',
			'subscribe_topic_def' => $subt_def,
			'enable_email_notification' => $this->cfg['enable_email_notification'] && $loggedin,
			'important_allowed' => $this->auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT),
			'back_url' => $this->url->get_topics_url($fid)
		));
		
		$pform = new BS_PostingForm($this->locale->lang('post').':');
		$pform->set_show_attachments(true,0,false,$this->input->isset_var('action_type','post'));
		$pform->set_show_options(true);
		$pform->add_form();
	}
	
	public function get_location()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		
		$result = array();
		$this->_add_loc_forum_path($result,$fid);
		$url = $this->url->get_url('new_poll','&amp;'.BS_URL_FID.'='.$fid);
		$result[$this->locale->lang('newpoll')] = $url;
		
		return $result;
	}
	
	public function has_access()
	{
		return $this->auth->has_current_forum_perm(BS_MODE_START_POLL) && $this->cfg['enable_polls'] == 1;
	}
}
?>