<?php
/**
 * Contains the edit-event-action
 *
 * @version			$Id$
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
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$forums = FWS_Props::get()->forums();
		$locale = FWS_Props::get()->locale();
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);

		// are the parameters valid?
		if($id == null || $fid == null)
			return 'The GET-parameter "id" or "fid" is missing';

		$topic_data = BS_DAO::get_topics()->get_by_id($id);
		// does the topic exist?
		if($topic_data === false)
			return 'A topic with id "'.$id.'" has not been found';

		// is the user loggedin?
		if(!$user->is_loggedin())
			return 'Not loggedin';

		// is the user allowed to edit this topic?
		if(!$auth->has_current_forum_perm(BS_MODE_EDIT_TOPIC,$topic_data['post_user']))
			return 'No permission to edit this topic';
		
		// does the forum exist?
		$forum_data = $forums->get_node_data($fid);
		if($forum_data === null)
			return 'The forum with id "'.$fid.'" doesn\'t exist';

		// forum closed?
		if(!$user->is_admin() && $forums->forum_is_closed($fid))
			return 'You are no admin and the forum is closed';

		// shadow threads can't be edited
		if($topic_data['moved_tid'] > 0)
			return 'shadow_thread_deny';

		// collect variables
		$topic_name = $input->get_var('topic_name','post',FWS_Input::STRING);
		$location = $input->get_var('location','post',FWS_Input::STRING);
		$open_end = $input->get_var('open_end','post',FWS_Input::STRING);
		$max_announcements = $input->get_var('max_announcements','post',FWS_Input::INTEGER);
		$allow_posts = $input->get_var('allow_posts','post',FWS_Input::INT_BOOL);
		$timeout_type = $input->get_var('timeout_type','post',FWS_Input::STRING);
		$important = $input->get_var('important','post',FWS_Input::INT_BOOL);
		$enable_announcements = $input->get_var('enable_announcements','post',FWS_Input::INT_BOOL);

		// topic-name or location empty?
		if(trim($topic_name) == '')
			return 'terminleer';

		$form = new BS_HTML_Formular(false,false);
		
		// the end-time has to be greater than the begin
		$begin = $form->get_date_chooser_timestamp('b_');
		$end = $form->get_date_chooser_timestamp('e_');
		if($end <= $begin && $open_end != 1)
			return 'endekbeginn';

		// check if the topic is locked
		if(BS_TopicUtils::is_locked($topic_data['locked'],BS_LOCK_TOPIC_EDIT))
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
		if($auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT))
			$fields['important'] = $important;

		// update the event
		BS_DAO::get_topics()->update($id,$fields);

		$fields = array(
			'event_location' => $location,
			'event_begin' => $begin,
			'event_end' => $event_end,
			'max_announcements' => $max_announcements,
			'timeout' => $timeout
		);
		BS_DAO::get_events()->update_by_topicid($id,$fields);
		
		// remove announcements, if disabled
		if($max_announcements == -1)
			BS_DAO::get_eventann()->delete_by_events(array($id));

		$this->set_action_performed(true);
		$this->add_link($locale->lang('back_to_forum'),BS_URL::get_topics_url($fid));

		return '';
	}
}
?>