<?php
/**
 * Contains the edit-topic-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-topic-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_edit_topic_topic extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$forums = FWS_Props::get()->forums();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();

		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);

		// are the parameters valid?
		if($id == null || $fid == null)
			return 'The GET-parameter "id" or "fid" is missing';

		// the user has to be loggedin
		if(!$user->is_loggedin())
			return 'Not loggedin';

		// does the topic exist?
		$topic_data = BS_DAO::get_topics()->get_by_id($id);
		if($topic_data === false)
			return 'A topic with id "'.$id.'" has not been found';
		
		// does the forum exist?
		$forum_data = $forums->get_node_data($fid);
		if($forum_data === null)
			return 'The forum with id "'.$fid.'" doesn\'t exist';

		// forum closed?
		if(!$user->is_admin() && $forums->forum_is_closed($fid))
			return 'You are no admin and the forum is closed';

		// has the user the permission to edit this topic?
		if(!$auth->has_current_forum_perm(BS_MODE_EDIT_TOPIC,$topic_data['post_user']))
			return 'No permission to edit this topic';

		// shadow-topics cannot be edited
		if($topic_data['moved_tid'] > 0)
			return 'shadow_thread_deny';

		// check if the topic is locked
		if(BS_TopicUtils::get_instance()->is_locked($topic_data['locked'],BS_LOCK_TOPIC_EDIT))
			return 'no_permission_to_edit_thread';

		$topic_name = $input->get_var('topic_name','post',FWS_Input::STRING);
		if(trim($topic_name) == '')
			return 'threadnameleer';
		
		$important = $input->get_var('important','post',FWS_Input::INT_BOOL);
		$symbol = $input->get_var('symbol','post',FWS_Input::INTEGER);
		$symbol = ($symbol < 0 || $symbol > BS_NUMBER_OF_TOPIC_ICONS) ? 0 : (int)$symbol;
		$allow_posts = $input->get_var('allow_posts','post',FWS_Input::INT_BOOL);

		$fields = array(
			'name' => $topic_name,
			'symbol' => $symbol,
			'comallow' => $allow_posts
		);
		
		// check if the user is allowed to mark a topic important
		if($auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT))
			$fields['important'] = $important;

		// edit topic
		BS_DAO::get_topics()->update($id,$fields);

		$this->set_action_performed(true);
		$this->add_link($locale->lang('back_to_forum'),$url->get_topics_url($fid));

		return '';
	}
}
?>