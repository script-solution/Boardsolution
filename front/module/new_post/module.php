<?php
/**
 * Contains the new-post-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The new-post-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_new_post extends BS_Front_Module
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
		$url = FWS_Props::get()->url();
		$auth = FWS_Props::get()->auth();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($auth->has_current_forum_perm(BS_MODE_REPLY));
		
		$renderer->add_action(BS_ACTION_REPLY,'default');

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
		if(!$site || $site <= 0)
			$site = 1;

		$this->add_loc_forum_path($fid);
		$this->add_loc_topic();
		$renderer->add_breadcrumb(
			$locale->lang('newentry'),
			$url->get_url('new_post','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid
				.'&amp;'.BS_URL_SITE.'='.$site)
		);
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$auth = FWS_Props::get()->auth();
		$tpl = FWS_Props::get()->tpl();
		$url = FWS_Props::get()->url();

		// check parameter
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		if($fid == null || $tid == null)
		{
			$this->report_error();
			return;
		}
		
		// does the forum exist?
		$forum_data = $forums->get_node_data($fid);
		if($forum_data === null || $forum_data->get_forum_type() != 'contains_threads')
		{
			$this->report_error();
			return;
		}
		
		// forum closed?
		if(!$user->is_admin() && $forums->forum_is_closed($fid))
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('forum_is_closed'));
			return;
		}
		
		// check if the topic is valid
		$topicdata = BS_Front_TopicFactory::get_instance()->get_current_topic();
		if($topicdata === null || $topicdata['comallow'] == 0 || 
			 (!$user->is_admin() && $topicdata['thread_closed'] == 1) ||
			 $topicdata['rubrikid'] != $fid)
		{
			$this->report_error();
			return;
		}

		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
		if($site == null)
			$site = $input->set_var(BS_URL_SITE,'get',1);

		if($input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview();
		
		$text = '';
		
		// quote posts
		$ids = $input->get_var(BS_URL_PID,'get',FWS_Input::STRING);
		if($ids != null)
		{
			$aids = FWS_Array_Utils::advanced_explode(',',$ids);
			foreach(BS_DAO::get_posts()->get_posts_by_ids($aids) as $post)
			{
				// check if the post comes from a forum that the user is allowed to view
				if($auth->has_access_to_intern_forum($post['rubrikid']))
				{
					$username = $post['post_user'] != 0 ? $post['user_name'] : $post['post_an_user'];
					$quote = BS_PostingUtils::get_instance()->quote_text($post['text_posted'],$username);
					if($user->use_bbcode_applet())
						$text .= $quote;
					else
						$text .= "\n".$quote."\n";
				}
			}
		}
		
		$form = new BS_PostingForm($locale->lang('post').':',$text);
		$form->set_show_attachments(true);
		$form->set_show_options(true);
		$form->add_form();
		
		$tpl->add_variables(array(
			'target_url' => $url->get_url(0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid
				.'&amp;'.BS_URL_SITE.'='.$site),
			'action_type' => BS_ACTION_REPLY,
			'back_url' => $url->get_url('posts','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid
				.'&amp;'.BS_URL_SITE.'='.$site)
		));

		$murl = $url->get_url(
			0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_SITE.'='.$site
				.'&amp;'.BS_URL_PID.'='
		);
		BS_PostingUtils::get_instance()->add_topic_review($topicdata,true,$murl);
	}
}
?>