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
		// nothing to do?
		if(!$this->input->isset_var('submit','post',PLIB_Input::STRING))
			return '';

		// the user has to be logged in
		if(!$this->user->is_loggedin())
			return 'nichteingeloggt';

		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);

		// parameters valid?
		if($id == null || $fid == null || $tid == null)
			return 'One of the GET-parameters "id","fid" and "tid" is missing';

		// does the post exist?
		$post_data = BS_DAO::get_posts()->get_post_from_topic($id,$fid,$tid);
		if($post_data === false)
			return 'The post with id "'.$id.'" has not been found';

		// check if the user is allowed to view this topic
		if(!$this->auth->has_access_to_intern_forum($fid))
			return 'No access to intern forum "'.$fid.'"';

		// is the user allowed to edit this post?
		if(!$this->auth->has_current_forum_perm(BS_MODE_EDIT_POST,$post_data['post_user']))
			return 'No permission to edit this post';

		// topic closed?
		if($post_data['thread_closed'] == 1 && !$this->user->is_admin())
			return 'You are no admin and the topic is closed';

		// has the post been locked?
		$locked = BS_TopicUtils::get_instance()->is_locked(
			$post_data['locked'],BS_LOCK_TOPIC_POSTS,$post_data['edit_lock']
		);
		if($locked)
			return 'no_permission_to_edit_post';

		$post_text = $this->input->get_var('text','post',PLIB_Input::STRING);

		$text = '';
		$error = BS_PostingUtils::get_instance()->prepare_message_for_db($text,$post_text);
		if($error != '')
			return $error;

		$lock_post = $this->input->get_var('lock_post','post',PLIB_Input::INT_BOOL);
		$use_bbcode = $this->input->isset_var('use_bbcode','post') ? 1 : 0;
		$use_smileys = $this->input->isset_var('use_smileys','post') ? 1 : 0;

		$fields = array(
			'text' => $text,
			'text_posted' => $post_text,
			'use_smileys' => $use_smileys,
			'use_bbcode' => $use_bbcode
		);
		
		if(($post_data['locked'] & BS_LOCK_TOPIC_POSTS) == 0)
		{
			if(($this->auth->is_moderator_in_current_forum() || $this->user->is_admin()) &&
					$post_data['edit_lock'] == 0 && $lock_post == 1)
				$fields['edit_lock'] = 1;
			else if(($this->auth->is_moderator_in_current_forum() || $this->user->is_admin()) &&
					$lock_post == 0)
				$fields['edit_lock'] = 0;
		}

		if($text != $post_data['text'] && $this->cfg['post_show_edited'] != 'never')
		{
			$tdata = BS_DAO::get_topics()->get_by_id($tid);
			if($this->cfg['post_show_edited'] == 'always' || $tdata['lastpost_id'] != $id)
			{
				$fields['edited_times'] = array('edited_times + 1');
				$fields['edited_date'] = time();
				$fields['edited_user'] = $this->user->get_user_id();
			}
		}
		
		// check attachments
		$attachments = $this->user->is_loggedin() && $this->auth->has_global_permission('attachments_add');
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

		$this->cache->get_cache('stats')->set_element_field(0,'last_edit',time());
		$this->cache->store('stats');

		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::ID);
		$url = $this->url->get_url(
			'posts',
			'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_SITE.'='.$site
		).'#b_'.$id;
		$this->add_link($this->locale->lang('go_to_post'),$url);
		$this->set_action_performed(true);
		
		return '';
	}
}
?>