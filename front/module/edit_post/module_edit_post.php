<?php
/**
 * Contains the edit-post-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-post-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_edit_post extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_EDIT_POST => 'default'
		);
	}
	
	public function run()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);

		// invalid id?
		if($id == null || $fid == null || $tid == null)
		{
			$this->_report_error();
			return;
		}

		$data = BS_DAO::get_posts()->get_post_from_topic($id,$fid,$tid);

		// data not found?
		if($data === false)
		{
			$this->_report_error();
			return;
		}

		// no permission to edit the post?
		if(!$this->auth->has_current_forum_perm(BS_MODE_EDIT_POST,$data['post_user']))
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
		
		// is the topic closed?
		if($data['thread_closed'] == 1 && !$this->user->is_admin())
		{
			$this->_report_error();
			return;
		}

		// no access because a user with higher status locked the post?
		if(BS_TopicUtils::get_instance()->is_locked($data['locked'],BS_LOCK_TOPIC_POSTS,$data['edit_lock']))
		{
			$this->_report_error(
				PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('no_permission_to_edit_post')
			);
			return;
		}

		$form = $this->_request_formular(true,true);

		if($this->input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview();

		if($data['post_user'] == 0)
			$user_text = $data['post_an_user'];
		else
		{
			$user_text = BS_UserUtils::get_instance()->get_link(
				$data['post_user'],$data['user_name'],$data['user_group']
			);
		}
		
		$add = '&amp;'.BS_URL_ID.'='.$id.'&amp;'.BS_URL_SITE.'='.$site;
		$show_lock = ($this->auth->is_moderator_in_current_forum() || $this->user->is_admin()) &&
								 ($data['locked'] & BS_LOCK_TOPIC_POSTS) == 0;

		$pform = new BS_PostingForm($this->locale->lang('post').':',$data['text_posted'],'posts');
		$pform->set_use_smileys($data['use_smileys']);
		$pform->set_use_bbcode($data['use_bbcode']);
		$pform->set_show_options(true);
		$pform->set_show_attachments(true,$data['id'],true,!$this->input->isset_var('post_update','post'));
		$pform->add_form();
		
		$this->tpl->add_variables(array(
			'action_type' => BS_ACTION_EDIT_POST,
			'user_text' => $user_text,
			'show_lock_post' => $show_lock,
			'lock_post' => $form->get_radio_yesno('lock_post',$data['edit_lock']),
			'target_url' => $this->url->get_url('edit_post','&amp;'.BS_URL_FID.'='.$fid
				.'&amp;'.BS_URL_TID.'='.$tid.$add),
			'back_url' => $this->url->get_url('posts','&amp;'.BS_URL_FID.'='.$fid
				.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_SITE.'='.$site).'#b_'.$id
		));

		$url = $this->url->get_url(
			0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_ID.'='.$id
				.'&amp;'.BS_URL_SITE.'='.$site.'&amp;'.BS_URL_PID.'='
		);
		$topic_data = $this->cache->get_cache('topic')->current();
		BS_PostingUtils::get_instance()->add_topic_review($topic_data,true,$url);
	}

	public function get_location()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$site = ($s = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER)) != null ? '&amp;'.BS_URL_SITE.'='.$s : '';

		$result = array();
		$this->_add_loc_forum_path($result,$fid);
		$this->_add_loc_topic($result);

		$params = '&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_ID.'='.$id.$site;
		$url = $this->url->get_url('edit_post',$params);
		$result[$this->locale->lang('edit_post')] = $url;

		return $result;
	}

	public function has_access()
	{
		return $this->user->is_loggedin();
	}
}
?>