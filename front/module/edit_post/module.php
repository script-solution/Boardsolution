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
		
		$renderer->add_action(BS_ACTION_EDIT_POST,'default');

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		
		$this->add_loc_forum_path($fid);
		$this->add_loc_topic();
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_TID,$tid);
		$url->set(BS_URL_ID,$id);
		if(($s = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER)) != null)
			$url->set(BS_URL_SITE,$s);
		$renderer->add_breadcrumb($locale->lang('edit_post'),$url->to_url());
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
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);

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
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}
		
		// forum closed?
		if(!$user->is_admin() && $forums->forum_is_closed($fid))
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('forum_is_closed'));
			return;
		}
		
		// is the topic closed?
		if($data['thread_closed'] == 1 && !$user->is_admin())
		{
			$this->report_error();
			return;
		}

		// no access because a user with higher status locked the post?
		if(BS_TopicUtils::is_locked($data['locked'],BS_LOCK_TOPIC_POSTS,$data['edit_lock']))
		{
			$this->report_error(
				FWS_Document_Messages::ERROR,$locale->lang('no_permission_to_edit_post')
			);
			return;
		}
		
		// topic-data available?
		$topic_data = BS_Front_TopicFactory::get_current_topic();
		if($topic_data === null)
		{
			$this->report_error();
			return;
		}

		$form = $this->request_formular(true,true);

		if($input->isset_var('preview','post'))
			BS_PostingUtils::add_post_preview();

		if($data['post_user'] == 0)
			$user_text = $data['post_an_user'];
		else
		{
			$user_text = BS_UserUtils::get_link(
				$data['post_user'],$data['user_name'],$data['user_group']
			);
		}
		
		$show_lock = ($auth->is_moderator_in_current_forum() || $user->is_admin()) &&
								 ($data['locked'] & BS_LOCK_TOPIC_POSTS) == 0;

		$pform = new BS_PostingForm($locale->lang('post').':',$data['text_posted'],'posts');
		$pform->set_use_smileys($data['use_smileys']);
		$pform->set_use_bbcode($data['use_bbcode']);
		$pform->set_show_options(true);
		$pform->set_show_attachments(true,$data['id'],true,!$input->isset_var('post_update','post'));
		$pform->add_form();
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_TID,$tid);
		$url->set(BS_URL_ID,$id);
		$url->set(BS_URL_SITE,$site);
		
		$purl = BS_URL::get_mod_url('posts');
		$purl->copy_params($url,array(BS_URL_FID,BS_URL_TID,BS_URL_SITE));
		$purl->set_anchor('b_'.$id);
		$purl->set_sef(true);
		
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_EDIT_POST,
			'user_text' => $user_text,
			'show_lock_post' => $show_lock,
			'lock_post' => $form->get_radio_yesno('lock_post',$data['edit_lock']),
			'target_url' => $url->to_url(),
			'back_url' => $purl->to_url()
		));
		
		BS_PostingUtils::add_topic_review($topic_data,true,$url);
	}
}
?>