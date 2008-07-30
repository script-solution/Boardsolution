<?php
/**
 * Contains the edit-post-module
 * 
 * @version			$Id: module_edit_post.php 43 2008-07-30 10:47:55Z nasmussen $
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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($user->is_loggedin());
		
		$renderer->add_action(BS_ACTION_EDIT_POST,'default');

		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$site = ($s = $input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER)) != null ? '&amp;'.BS_URL_SITE.'='.$s : '';

		$this->add_loc_forum_path($fid);
		$this->add_loc_topic();
		$params = '&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_ID.'='.$id.$site;
		$renderer->add_breadcrumb(
			$locale->lang('edit_post'),
			$url->get_url('edit_post',$params)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$auth = PLIB_Props::get()->auth();
		$user = PLIB_Props::get()->user();
		$forums = PLIB_Props::get()->forums();
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$site = $input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);

		// invalid id?
		if($id == null || $fid == null || $tid == null)
		{
			$this->report_error();
			return;
		}

		$data = BS_DAO::get_posts()->get_post_from_topic($id,$fid,$tid);

		// data not found?
		if($data === false)
		{
			$this->report_error();
			return;
		}

		// no permission to edit the post?
		if(!$auth->has_current_forum_perm(BS_MODE_EDIT_POST,$data['post_user']))
		{
			$this->report_error(PLIB_Document_Messages::NO_ACCESS);
			return;
		}
		
		// forum closed?
		if(!$user->is_admin() && $forums->forum_is_closed($fid))
		{
			$this->report_error(PLIB_Document_Messages::ERROR,$locale->lang('forum_is_closed'));
			return;
		}
		
		// is the topic closed?
		if($data['thread_closed'] == 1 && !$user->is_admin())
		{
			$this->report_error();
			return;
		}

		// no access because a user with higher status locked the post?
		if(BS_TopicUtils::get_instance()->is_locked($data['locked'],BS_LOCK_TOPIC_POSTS,$data['edit_lock']))
		{
			$this->report_error(
				PLIB_Document_Messages::ERROR,$locale->lang('no_permission_to_edit_post')
			);
			return;
		}
		
		// topic-data available?
		$topic_data = BS_Front_TopicFactory::get_instance()->get_current_topic();
		if($topic_data === null)
		{
			$this->report_error();
			return;
		}

		$form = $this->request_formular(true,true);

		if($input->isset_var('preview','post'))
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
		$show_lock = ($auth->is_moderator_in_current_forum() || $user->is_admin()) &&
								 ($data['locked'] & BS_LOCK_TOPIC_POSTS) == 0;

		$pform = new BS_PostingForm($locale->lang('post').':',$data['text_posted'],'posts');
		$pform->set_use_smileys($data['use_smileys']);
		$pform->set_use_bbcode($data['use_bbcode']);
		$pform->set_show_options(true);
		$pform->set_show_attachments(true,$data['id'],true,!$input->isset_var('post_update','post'));
		$pform->add_form();
		
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_EDIT_POST,
			'user_text' => $user_text,
			'show_lock_post' => $show_lock,
			'lock_post' => $form->get_radio_yesno('lock_post',$data['edit_lock']),
			'target_url' => $url->get_url('edit_post','&amp;'.BS_URL_FID.'='.$fid
				.'&amp;'.BS_URL_TID.'='.$tid.$add),
			'back_url' => $url->get_url('posts','&amp;'.BS_URL_FID.'='.$fid
				.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_SITE.'='.$site).'#b_'.$id
		));

		$murl = $url->get_url(
			0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_ID.'='.$id
				.'&amp;'.BS_URL_SITE.'='.$site.'&amp;'.BS_URL_PID.'='
		);
		BS_PostingUtils::get_instance()->add_topic_review($topic_data,true,$murl);
	}
}
?>