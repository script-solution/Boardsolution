<?php
/**
 * Contains the manage-posts-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The manage-posts-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_manage_posts extends BS_Front_Module
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
		$auth = FWS_Props::get()->auth();
		$renderer = $doc->use_default_renderer();

		$renderer->set_has_access($user->is_loggedin() && $auth->has_current_forum_perm(BS_MODE_SPLIT_POSTS));
		
		// add actions
		$renderer->add_action(BS_ACTION_MERGE_POSTS,array('default','merge'));
		$renderer->add_action(BS_ACTION_SPLIT_POSTS,array('default','split'));
		$renderer->add_module_action(
			BS_ACTION_DELETE_POSTS,'delete_post','default','front/module/'
		);
		
		// add bread crumbs
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		
		// don't show thread- and forum-title if its intern
		if($auth->has_access_to_intern_forum($fid))
		{
			$this->add_loc_forum_path($fid);
			$this->add_loc_topic();
		}
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_TID,$tid);
		$renderer->add_breadcrumb($locale->lang('manage_posts'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$forums = FWS_Props::get()->forums();
		$locale = FWS_Props::get()->locale();
		$auth = FWS_Props::get()->auth();
		$tpl = FWS_Props::get()->tpl();
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		$mode = $input->correct_var(BS_URL_MODE,-1,FWS_Input::STRING,array('delete','split','merge'),'delete');
	
		// check other parameters
		if($fid == null || $tid == null)
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
		
		// does the topic exist?
		$topic_data = BS_Front_TopicFactory::get_current_topic();
		if($topic_data == null)
		{
			$this->report_error();
			return;
		}
	
		// topic closed?
		if($topic_data['thread_closed'] == 1 && !$user->is_admin())
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('topic_is_closed'));
			return;
		}
		
		$form = $this->request_formular(false,false);
		if($input->isset_var('change_display','post'))
			$form->set_condition(true);
		
		// correct the mode if we don't have the permission to do the selected operation
		if($mode == 'delete' && !$auth->has_current_forum_perm(BS_MODE_DELETE_POSTS))
			$mode = 'split';
		else if($mode != 'delete' && !$auth->has_current_forum_perm(BS_MODE_SPLIT_POSTS))
			$mode = 'delete';
		
		switch($mode)
		{
			case 'delete':
				$action_type = BS_ACTION_DELETE_POSTS;
				break;
			case 'merge':
				$action_type = BS_ACTION_MERGE_POSTS;
				break;
			case 'split':
				$action_type = BS_ACTION_SPLIT_POSTS;
				break;
		}
		
		$keyword = $input->get_var('keyword','post',FWS_Input::STRING);
		if($keyword === null)
		{
			$start = time() - (3600 * 24 * 7);
			$end = time();
		}
		else
		{
			$start = $form->get_date_chooser_timestamp('start_',false);
			// we want an inclusive end, so we add the time to get 23:59:59 on the selected day.
			$end = $form->get_date_chooser_timestamp('end_',false) + (3600 * 24) - 1;
		}
		
		$split_options = array(
			'selected' => $locale->lang('split_selected'),
			'following' => $locale->lang('split_following')
		);
		
		$target_forum = $form->get_input_value('target_forum',0);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_TID,$tid);
		
		$tpl->add_variables(array(
			'show_delete' => $auth->has_current_forum_perm(BS_MODE_DELETE_POSTS),
			'show_move' => $auth->has_current_forum_perm(BS_MODE_SPLIT_POSTS),
			'delete_posts_url' => $url->set(BS_URL_MODE,'delete')->to_url(),
			'split_posts_url' => $url->set(BS_URL_MODE,'split')->to_url(),
			'merge_posts_url' => $url->set(BS_URL_MODE,'merge')->to_url(),
			'delete_bold' => $mode == 'delete' ? ' style="font-weight: bold;"' : '',
			'split_bold' => $mode == 'split' ? ' style="font-weight: bold;"' : '',
			'merge_bold' => $mode == 'merge' ? ' style="font-weight: bold;"' : '',
			'display_merge' => $mode == 'merge' ? 'block' : 'none',
			'display_split' => $mode == 'split' ? 'block' : 'none',
			'symbols' => BS_TopicUtils::get_symbols($form),
			'forum_combo' => BS_ForumUtils::get_recursive_forum_combo(
				'target_forum',$target_forum,0
			),
			'target_url' => $url->remove(BS_URL_MODE)->to_url(),
			'at_merge' => BS_ACTION_MERGE_POSTS,
			'at_delete' => BS_ACTION_DELETE_POSTS,
			'at_split' => BS_ACTION_SPLIT_POSTS,
			'operation' => $mode,
			'action_type' => $action_type,
			'start_date' => $form->get_date_chooser('start_',$start,false),
			'end_date' => $form->get_date_chooser('end_',$end,false),
			'display_target' => $url->to_url(),
			'keyword' => $keyword,
			'merge_split_options' => $split_options,
			'back_url' => BS_URL::build_posts_url($fid,$tid),
		));
		
		$post_ids = $input->get_var('selected_posts','post');
		if(!is_array($post_ids))
			$post_ids = array();
		
		$first_post = BS_DAO::get_posts()->get_first_postid_in_topic($fid,$tid);
		
		$search_add = ' AND p.post_time >= '.$start.' AND p.post_time <= '.$end;
		if($keyword != null)
			$search_add .= ' AND p.text_posted LIKE "%'.$keyword.'%"';
		
		$posts = array();
		$postcon = new BS_Front_Post_Container(
			$fid,$tid,null,null,'post_time ASC',FWS_String::substr($search_add,5)
		);
		foreach($postcon->get_posts() as $post)
		{
			/* @var $post BS_Front_Post_Data */
			$posts[] = array(
				'post_id' => $post->get_field('bid'),
				'user_name' => $post->get_username(),
				'date' => FWS_Date::get_date($post->get_field('post_time'),true),
				'selected' => in_array($post->get_field('bid'),$post_ids) ? ' checked="checked"' : '',
				'text' => $post->get_post_text(false,false,false)
			);
		}
		
		$tpl->add_variables(array(
			'is_first_post' => count($posts) > 0 && $posts[0]['post_id'] == $first_post ? '1' : '0'
		)); 
		$tpl->add_variable_ref('posts',$posts);
	}
}
?>