<?php
/**
 * Contains the edit-topic-module
 * 
 * @version			$Id$
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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($user->is_loggedin());
		
		// add actions
		$renderer->add_action(BS_ACTION_EDIT_EVENT,'event');
		$renderer->add_action(BS_ACTION_EDIT_TOPIC,'topic');
		$renderer->add_action(BS_ACTION_EDIT_POLL,'poll');

		// add bread crumbs
		$id = (int)$input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		
		$this->add_loc_forum_path($fid);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_ID,$id);
		$renderer->add_breadcrumb($locale->lang('editthread'),$url->to_url());
		
		// load topic-data
		if($id != null && $fid != null)
		{
			$this->_tdata = BS_DAO::get_topics()->get_by_id($id);
			
			// set template
			$tplname = 'edit_topic.htm';
			if($this->_tdata === null)
				$tplname = 'edit_topic.htm';
			else if($this->_tdata['type'] > 0)
				$tplname = 'edit_poll.htm';
			else if($this->_tdata['type'] == -1)
				$tplname = 'edit_event.htm';
			
			$renderer->set_template($tplname);
		}
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		$forums = FWS_Props::get()->forums();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$functions = FWS_Props::get()->functions();

		$id = (int)$input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
	
		// check if the URL-parameters are valid
		if($fid == null || $id == null || $id <= 0)
		{
			$this->report_error();
			return;
		}
	
		// has the data been found?
		if($this->_tdata === null || $this->_tdata['id'] == '')
		{
			$this->report_error();
			return;
		}
	
		// is the user allowed to edit this topic?
		if(!$auth->has_current_forum_perm(BS_MODE_EDIT_TOPIC,$this->_tdata['post_user']))
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}
		
		// forum closed?
		if(!$user->is_admin() && $forums->forum_is_closed($fid))
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('forum_is_closed'));
			return;
		}
	
		// has this topic been moved?
		if($this->_tdata['moved_tid'] != 0)
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('shadow_thread_deny'));
			return;
		}
	
		// no access because a user with higher status locked the post?
		if(BS_TopicUtils::is_locked($this->_tdata['locked'],BS_LOCK_TOPIC_EDIT))
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('no_permission_locked'));
			return;
		}
	
		$form = $this->request_formular(false);
	
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_ID,$id);
		$target_url = $url->to_url();
		$back_url = BS_URL::build_topics_url($fid);
		$forum_name = BS_ForumUtils::get_forum_path($this->_tdata['rubrikid'],false);
		
		// topic
		if($this->_tdata['type'] == 0)
		{
			$tpl->add_variables(array(
				'target_url' => $target_url,
				'forum_name' => $forum_name,
				'topic_name_def' => $this->_tdata['name'],
				'action_type' => BS_ACTION_EDIT_TOPIC,
				'view_important' => $auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT),
				'important_def' => $this->_tdata['important'],
				'symbols' => BS_TopicUtils::get_symbols(
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
			
			$tpl->add_variables(array(
				'action_type' => BS_ACTION_EDIT_EVENT,
				'target_url' => $target_url,
				'forum_name' => $forum_name,
				'topic_name_def' => $this->_tdata['name'],
				'allow_posts_def' => $this->_tdata['comallow'],
				'view_important' => $auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT),
				'important_def' => $this->_tdata['important'],
				'location_def' => $event['event_location'],
				'event_begin_def' => $event['event_begin'],
				'event_end_def' => $event['event_end'],
				'event_timeout_def' => $event['timeout'],
				'enable_announcements_def' => $event['max_announcements'] >= 0 ? 1 : 0,
				'max_announcements_def' => $event['max_announcements'],
				'open_end' => $form->get_checkbox_value('openend',$event['event_end'] == 0),
				'close_announcements_begin' => ($event['timeout'] == '0') ? 'checked="checked"' : '',
				'close_announcements_custom' => ($event['timeout'] > 0) ? 'checked="checked"' : '',
				'back_url' => $back_url
			));
		}
		// poll
		else
		{
			$can_edit_options = $auth->has_global_permission('always_edit_poll_options');
			if(!$can_edit_options)
			{
				$info = $functions->get_poll_info($this->_tdata['type']);
				if($info['total_votes'] == 0)
					$can_edit_options = true;
			}
			
			$tpl->add_variables(array(
				'action_type' => BS_ACTION_EDIT_POLL,
				'target_url' => $target_url,
				'forum_name' => $forum_name,
				'allow_posts_def' => $this->_tdata['comallow'],
				'view_important' => $auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT),
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
	
				$tpl->add_variables(array(
					'topic_name_def' => $this->_tdata['name'],
					'poll_options_def' => trim($textbox_ins),
					'multichoice_def' => $multichoice,
				));
			}
		}
	}
}
?>