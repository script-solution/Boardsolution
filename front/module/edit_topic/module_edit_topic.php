<?php
/**
 * Contains the edit-topic-module
 * 
 * @version			$Id: module_edit_topic.php 735 2008-05-23 07:49:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-topic-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_edit_topic extends BS_Front_Module
{
	/**
	 * The data of the topic
	 *
	 * @var array
	 */
	private $_tdata = null;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$id = (int)$this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		
		if($id != null && $fid != null)
			$this->_tdata = BS_DAO::get_topics()->get_by_id($id);
	}
	
	public function get_actions()
	{
		return array(
			BS_ACTION_EDIT_EVENT => 'event',
			BS_ACTION_EDIT_TOPIC => 'topic',
			BS_ACTION_EDIT_POLL => 'poll'
		);
	}
	
	public function get_template()
	{
		if($this->_tdata === null)
			return 'edit_topic.htm';
		
		if($this->_tdata['type'] == 0)
			return 'edit_topic.htm';
		if($this->_tdata['type'] == -1)
			return 'edit_event.htm';
		
		return 'edit_poll.htm';
	}
	
	public function run()
	{
		$id = (int)$this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
	
		// check if the URL-parameters are valid
		if($fid == null || $id == null || $id <= 0)
		{
			$this->_report_error();
			return;
		}
	
		// has the data been found?
		if($this->_tdata === null || $this->_tdata['id'] == '')
		{
			$this->_report_error();
			return;
		}
	
		// is the user allowed to edit this topic?
		if(!$this->auth->has_current_forum_perm(BS_MODE_EDIT_TOPIC,$this->_tdata['post_user']))
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}
		
		// forum closed?
		if(!$this->user->is_admin() && $this->forums->forum_is_closed($fid))
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('forum_is_closed'));
			return;
		}
	
		// has this topic been moved?
		if($this->_tdata['moved_tid'] != 0)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('shadow_thread_deny'));
			return;
		}
	
		// no access because a user with higher status locked the post?
		if(BS_TopicUtils::get_instance()->is_locked($this->_tdata['locked'],BS_LOCK_TOPIC_EDIT))
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('no_permission_locked'));
			return;
		}
	
		$form = $this->_request_formular(false);
	
		$target_url = $this->url->get_url(
			'edit_topic','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$id
		);
		$back_url = $this->url->get_topics_url($fid);
		$forum_name = BS_ForumUtils::get_instance()->get_forum_path($this->_tdata['rubrikid'],false);
		
		// topic
		if($this->_tdata['type'] == 0)
		{
			$this->tpl->add_variables(array(
				'target_url' => $target_url,
				'forum_name' => $forum_name,
				'topic_name_def' => $this->_tdata['name'],
				'action_type' => BS_ACTION_EDIT_TOPIC,
				'view_important' => $this->auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT),
				'important_def' => $this->_tdata['important'],
				'symbols' => BS_TopicUtils::get_instance()->get_symbols(
					$form,$this->_tdata['symbol']
				),
				'allow_posts_def' => $this->_tdata['comallow'],
				'back_url' => $back_url
			));
		}
		// event
		else if($this->_tdata['type'] == -1)
		{
			$event = BS_DAO::get_events()->get_by_topic_id($id);
			
			$this->tpl->add_variables(array(
				'action_type' => BS_ACTION_EDIT_EVENT,
				'target_url' => $target_url,
				'forum_name' => $forum_name,
				'topic_name_def' => $this->_tdata['name'],
				'allow_posts_def' => $this->_tdata['comallow'],
				'view_important' => $this->auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT),
				'important_def' => $this->_tdata['important'],
				'location_def' => $event['event_location'],
				'event_begin_def' => $event['event_begin'],
				'event_end_def' => $event['event_end'],
				'event_timeout_def' => $event['timeout'],
				'enable_announcements_def' => $event['max_announcements'] >= 0 ? 1 : 0,
				'max_announcements_def' => $event['max_announcements'],
				'text_def' => $event['description'],
				'open_end' => $form->get_checkbox_value('openend',$event['event_end'] == 0),
				'close_announcements_begin' => ($event['timeout'] == '0') ? 'checked="checked"' : '',
				'close_announcements_custom' => ($event['timeout'] > 0) ? 'checked="checked"' : '',
				'back_url' => $back_url
			));
		}
		// poll
		else
		{
			$can_edit_options = $this->auth->has_global_permission('always_edit_poll_options');
			if(!$can_edit_options)
			{
				$info = $this->functions->get_poll_info($this->_tdata['type']);
				if($info['total_votes'] == 0)
					$can_edit_options = true;
			}
			
			$this->tpl->add_variables(array(
				'action_type' => BS_ACTION_EDIT_POLL,
				'target_url' => $target_url,
				'forum_name' => $forum_name,
				'allow_posts_def' => $this->_tdata['comallow'],
				'view_important' => $this->auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT),
				'important_def' => $this->_tdata['important'],
				'view_options' => $can_edit_options,
				'back_url' => $back_url
			));
			
			$textbox_ins = '';
			if($can_edit_options)
			{
				$multichoice = 0;
				foreach(BS_DAO::get_polls()->get_options_by_id($this->_tdata['type']) as $mypdaten)
				{
					$multichoice = $mypdaten['multichoice'];
					$textbox_ins .= $mypdaten['option_name']."\n";
				}
	
				$this->tpl->add_variables(array(
					'topic_name_def' => $this->_tdata['name'],
					'poll_options_def' => trim($textbox_ins),
					'multichoice_def' => $multichoice,
				));
			}
		}
	}
	
	public function get_location()
	{
		$id = (int)$this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		
		$result = array();
		$this->_add_loc_forum_path($result,$fid);
		
		$url = $this->url->get_url('edit_topic','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$id);
		$result[$this->locale->lang('editthread')] = $url;
		
		return $result;
	}
	
	public function has_access()
	{
		return $this->user->is_loggedin();
	}
}
?>