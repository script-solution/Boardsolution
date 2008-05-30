<?php
/**
 * Contains the new-event-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The new-event-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_new_event extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_START_EVENT => 'default'
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
	
		$form = $this->_request_formular(true,true);
	
		if($this->input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview();
		
		$loggedin = $this->user->is_loggedin();
		$subt_def = $loggedin ? $this->user->get_profile_val('default_email_notification') : 0;
		
		$this->tpl->add_variables(array(
			'action_type' => BS_ACTION_START_EVENT,
			'open_end' => $form->get_checkbox_value('open_end',false),
			'timeout_type_begin' => $form->get_radio_value('timeout_type','begin',true),
			'timeout_type_self' => $form->get_radio_value('timeout_type','self',false),
			'target_url' => $this->url->get_url(0,'&amp;'.BS_URL_FID.'='.$fid),
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
		$url = $this->url->get_url('new_event','&amp;'.BS_URL_FID.'='.$fid);
		$result[$this->locale->lang('newevent')] = $url;
		
		return $result;
	}
	
	public function has_access()
	{
		return $this->auth->has_current_forum_perm(BS_MODE_START_EVENT) &&
			$this->cfg['enable_events'] == 1;
	}
}
?>