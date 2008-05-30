<?php
/**
 * Contains the edit-event-action
 *
 * @version			$Id: action_event.php 770 2008-05-25 13:41:44Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-event-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_edit_topic_event extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);

		// are the parameters valid?
		if($id == null || $fid == null)
			return 'The GET-parameter "id" or "fid" is missing';

		$topic_data = BS_DAO::get_topics()->get_by_id($id);
		// does the topic exist?
		if($topic_data === false)
			return 'A topic with id "'.$id.'" has not been found';

		// is the user loggedin?
		if(!$this->user->is_loggedin())
			return 'Not loggedin';

		// is the user allowed to edit this topic?
		if(!$this->auth->has_current_forum_perm(BS_MODE_EDIT_TOPIC,$topic_data['post_user']))
			return 'No permission to edit this topic';
		
		// does the forum exist?
		$forum_data = $this->forums->get_node_data($fid);
		if($forum_data === null)
			return 'The forum with id "'.$fid.'" doesn\'t exist';

		// forum closed?
		if(!$this->user->is_admin() && $this->forums->forum_is_closed($fid))
			return 'You are no admin and the forum is closed';

		// shadow threads can't be edited
		if($topic_data['moved_tid'] > 0)
			return 'shadow_thread_deny';

		// collect variables
		$topic_name = $this->input->get_var('topic_name','post',PLIB_Input::STRING);
		$location = $this->input->get_var('location','post',PLIB_Input::STRING);
		$open_end = $this->input->get_var('open_end','post',PLIB_Input::STRING);
		$max_announcements = $this->input->get_var('max_announcements','post',PLIB_Input::INTEGER);
		$allow_posts = $this->input->get_var('allow_posts','post',PLIB_Input::INT_BOOL);
		$timeout_type = $this->input->get_var('timeout_type','post',PLIB_Input::STRING);
		$description = $this->input->get_var('text','post',PLIB_Input::STRING);
		$important = $this->input->get_var('important','post',PLIB_Input::INT_BOOL);
		$enable_announcements = $this->input->get_var('enable_announcements','post',PLIB_Input::INT_BOOL);

		// topic-name or location empty?
		if(trim($topic_name) == '' || trim($location) == '')
			return 'terminleer';

		$form = new BS_HTML_Formular(false,false);
		
		// the end-time has to be greater than the begin
		$begin = $form->get_date_chooser_timestamp('b_');
		$end = $form->get_date_chooser_timestamp('e_');
		if($end <= $begin && $open_end != 1)
			return 'endekbeginn';

		// check if the topic is locked
		if(BS_TopicUtils::get_instance()->is_locked($topic_data['locked'],BS_LOCK_TOPIC_EDIT))
			return 'no_permission_to_edit_thread';

		if($timeout_type == 'begin')
			$timeout = 0;
		else
			$timeout = $form->get_date_chooser_timestamp('c_');

		if(!$enable_announcements)
			$max_announcements = -1;
		else
			$max_announcements = ($max_announcements < 0) ? 0 : $max_announcements;

		$event_end = ($open_end == 1) ? 0 : $end;

		$fields = array(
			'name' => $topic_name,
			'comallow' => $allow_posts
		);
		
		// check if the user is allowed to mark a topic important
		if($this->auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT))
			$fields['important'] = $important;

		// update the event
		BS_DAO::get_topics()->update($id,$fields);

		$fields = array(
			'event_location' => $location,
			'event_begin' => $begin,
			'event_end' => $event_end,
			'max_announcements' => $max_announcements,
			'description' => $description,
			'timeout' => $timeout
		);
		BS_DAO::get_events()->update_by_topicid($id,$fields);
		
		// remove announcements, if disabled
		if($max_announcements == -1)
			BS_DAO::get_eventann()->delete_by_events(array($id));

		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('back_to_forum'),$this->url->get_topics_url($fid));

		return '';
	}
}
?>