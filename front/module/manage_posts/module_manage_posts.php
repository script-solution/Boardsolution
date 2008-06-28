<?php
/**
 * Contains the manage-posts-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The manage-posts-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_manage_posts extends BS_Front_Module
{
	public function get_actions()
	{
		$action = $this->doc->load_module_action(
			BS_ACTION_DELETE_POSTS,'delete_post','default','front/module/'
		);
		$this->doc->add_action($action);
		
		return array(
			BS_ACTION_MERGE_POSTS => array('default','merge'),
			BS_ACTION_SPLIT_POSTS => array('default','split')
		);
	}
	
	public function run()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$mode = $this->input->correct_var(BS_URL_MODE,-1,PLIB_Input::STRING,array('delete','split','merge'),'delete');
	
		// check other parameters
		if($fid == null || $tid == null)
		{
			$this->_report_error();
			return;
		}
		
		// forum closed?
		if(!$this->user->is_admin() && $this->forums->forum_is_closed($fid))
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('forum_is_closed'));
			return;
		}
		
		// does the topic exist?
		$topic_data = BS_Front_TopicFactory::get_instance()->get_current_topic();
		if($topic_data == null)
		{
			$this->_report_error();
			return;
		}
	
		// topic closed?
		if($topic_data['thread_closed'] == 1 && !$this->user->is_admin())
		{
			$this->_report_error();
			return;
		}
		
		$form = $this->_request_formular(false,false);
		
		// correct the mode if we don't have the permission to do the selected operation
		if($mode == 'delete' && !$this->auth->has_current_forum_perm(BS_MODE_DELETE_POSTS))
			$mode = 'split';
		else if($mode != 'delete' && !$this->auth->has_current_forum_perm(BS_MODE_SPLIT_POSTS))
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
		
		$keyword = $this->input->get_var('keyword','post',PLIB_Input::STRING);
		if($keyword === null)
		{
			$start = time() - (3600 * 24 * 7);
			$end = time();
		}
		else
		{
			$start = $form->get_date_chooser_timestamp('start_',false,false);
			// we want an inclusive end, so we add the time to get 23:59:59 on the selected day.
			$end = $form->get_date_chooser_timestamp('end_',false,false) + (3600 * 24) - 1;
		}
		
		$split_options = array(
			'selected' => $this->locale->lang('split_selected'),
			'following' => $this->locale->lang('split_following')
		);
		
		$params = '&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid;
		$target_forum = $form->get_input_value('target_forum',0);
		
		$this->tpl->add_variables(array(
			'show_delete' => $this->auth->has_current_forum_perm(BS_MODE_DELETE_POSTS),
			'show_move' => $this->auth->has_current_forum_perm(BS_MODE_SPLIT_POSTS),
			'delete_posts_url' => $this->url->get_url(0,$params.'&amp;'.BS_URL_MODE.'=delete'),
			'split_posts_url' => $this->url->get_url(0,$params.'&amp;'.BS_URL_MODE.'=split'),
			'merge_posts_url' => $this->url->get_url(0,$params.'&amp;'.BS_URL_MODE.'=merge'),
			'delete_bold' => $mode == 'delete' ? ' style="font-weight: bold;"' : '',
			'split_bold' => $mode == 'split' ? ' style="font-weight: bold;"' : '',
			'merge_bold' => $mode == 'merge' ? ' style="font-weight: bold;"' : '',
			'display_merge' => $mode == 'merge' ? 'block' : 'none',
			'display_split' => $mode == 'split' ? 'block' : 'none',
			'symbols' => BS_TopicUtils::get_instance()->get_symbols($form),
			'forum_combo' => BS_ForumUtils::get_instance()->get_recursive_forum_combo(
				'target_forum',$target_forum,0
			),
			'back_url' => $this->url->get_url('posts',$params),
			'target_url' => $this->url->get_url(0,$params),
			'at_merge' => BS_ACTION_MERGE_POSTS,
			'at_delete' => BS_ACTION_DELETE_POSTS,
			'at_split' => BS_ACTION_SPLIT_POSTS,
			'operation' => $mode,
			'action_type' => $action_type,
			'start_date' => $form->get_date_chooser('start_',$start,false),
			'end_date' => $form->get_date_chooser('end_',$end,false),
			'display_target' => $this->url->get_url(0,$params),
			'keyword' => $keyword,
			'merge_split_options' => $split_options
		));
		
		$post_ids = $this->input->get_var('selected_posts','post');
		if(!is_array($post_ids))
			$post_ids = array();
		
		$first_post = BS_DAO::get_posts()->get_first_postid_in_topic($fid,$tid);
		
		$search_add = ' AND p.post_time >= '.$start.' AND p.post_time <= '.$end;
		if($keyword != null)
			$search_add .= ' AND p.text_posted LIKE "%'.$keyword.'%"';
		
		$posts = array();
		$postcon = new BS_Front_Post_Container(
			$fid,$tid,null,null,'post_time ASC',PLIB_String::substr($search_add,5)
		);
		foreach($postcon->get_posts() as $post)
		{
			/* @var $post BS_Front_Post_Data */
			$posts[] = array(
				'post_id' => $post->get_field('bid'),
				'user_name' => $post->get_username(),
				'date' => PLIB_Date::get_date($post->get_field('post_time'),true),
				'selected' => in_array($post->get_field('bid'),$post_ids) ? ' checked="checked"' : '',
				'text' => $post->get_post_text(false,false,false)
			);
		}
		
		$this->tpl->add_variables(array(
			'is_first_post' => count($posts) > 0 && $posts[0]['post_id'] == $first_post ? '1' : '0'
		)); 
		$this->tpl->add_array('posts',$posts);
	}
	
	public function get_location()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		
		$result = array();
		$this->_add_loc_forum_path($result,$fid);
		$this->_add_loc_topic($result);
		
		$url = $this->url->get_url(
			'manage_posts','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid
		);
		$result[$this->locale->lang('manage_posts')] = $url;
		
		return $result;
	}
	
	public function has_access()
	{
		return $this->user->is_loggedin() && $this->auth->has_current_forum_perm(BS_MODE_SPLIT_POSTS);
	}
}
?>