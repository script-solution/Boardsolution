<?php
/**
 * Contains the edit-post-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-post-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_edit_post_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$cfg = FWS_Props::get()->cfg();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();
		// nothing to do?
		if(!$input->isset_var('submit','post',FWS_Input::STRING))
			return '';

		// the user has to be logged in
		if(!$user->is_loggedin())
			return 'nichteingeloggt';

		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);

		// parameters valid?
		if($id == null || $fid == null || $tid == null)
			return 'One of the GET-parameters "id","fid" and "tid" is missing';

		// does the post exist?
		$post_data = BS_DAO::get_posts()->get_post_from_topic($id,$fid,$tid);
		if($post_data === false)
			return 'The post with id "'.$id.'" has not been found';

		// check if the user is allowed to view this topic
		if(!$auth->has_access_to_intern_forum($fid))
			return 'No access to intern forum "'.$fid.'"';

		// is the user allowed to edit this post?
		if(!$auth->has_current_forum_perm(BS_MODE_EDIT_POST,$post_data['post_user']))
			return 'No permission to edit this post';

		// topic closed?
		if($post_data['thread_closed'] == 1 && !$user->is_admin())
			return 'You are no admin and the topic is closed';

		// has the post been locked?
		$locked = BS_TopicUtils::is_locked(
			$post_data['locked'],BS_LOCK_TOPIC_POSTS,$post_data['edit_lock']
		);
		if($locked)
			return 'no_permission_to_edit_post';

		$post_text = $input->get_var('text','post',FWS_Input::STRING);

		$text = '';
		$error = BS_PostingUtils::prepare_message_for_db($text,$post_text);
		if($error != '')
			return $error;

		$lock_post = $input->get_var('lock_post','post',FWS_Input::INT_BOOL);
		$use_bbcode = $input->isset_var('use_bbcode','post') ? 1 : 0;
		$use_smileys = $input->isset_var('use_smileys','post') ? 1 : 0;

		$fields = array(
			'text' => $text,
			'text_posted' => $post_text,
			'use_smileys' => $use_smileys,
			'use_bbcode' => $use_bbcode
		);
		
		if(($post_data['locked'] & BS_LOCK_TOPIC_POSTS) == 0)
		{
			if(($auth->is_moderator_in_current_forum() || $user->is_admin()) &&
					$post_data['edit_lock'] == 0 && $lock_post == 1)
				$fields['edit_lock'] = 1;
			else if(($auth->is_moderator_in_current_forum() || $user->is_admin()) &&
					$lock_post == 0)
				$fields['edit_lock'] = 0;
		}

		if($text != $post_data['text'] && $cfg['post_show_edited'] != 'never')
		{
			$tdata = BS_DAO::get_topics()->get_by_id($tid);
			if($cfg['post_show_edited'] == 'always' || $tdata['lastpost_id'] != $id)
			{
				$fields['edited_times'] = array('edited_times + 1');
				$fields['edited_date'] = time();
				$fields['edited_user'] = $user->get_user_id();
			}
		}
		
		// check attachments
		$attachments = $user->is_loggedin() && $auth->has_global_permission('attachments_add');
		if($attachments)
		{
			$att = BS_Front_Action_Plain_Attachments::get_default($id,$tid);
			$res = $att->check_data();
			// we don't want to abort here, we just skip the attachments
			if($res != '')
				$attachments = false;
		}

		// update the post
		BS_DAO::get_posts()->update($id,$fields);
		
		// add attachments
		if($attachments)
			$att->perform_action();

		$cache->get_cache('stats')->set_element_field(0,'last_edit',time());
		$cache->store('stats');

		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::ID);
		$murl = BS_URL::get_mod_url('posts');
		$murl->set(BS_URL_FID,$fid);
		$murl->set(BS_URL_TID,$tid);
		$murl->set(BS_URL_SITE,$site);
		$murl->set_anchor('b_'.$id);
		$murl->set_sef(true);
		$this->add_link($locale->lang('go_to_post'),$murl);
		$this->set_action_performed(true);
		
		return '';
	}
}
?>