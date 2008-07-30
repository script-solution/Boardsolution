<?php
/**
 * Contains the delete-post-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-post-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_delete_post extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$user = PLIB_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($user->is_loggedin());
		
		$renderer->add_action(BS_ACTION_DELETE_POSTS,'default');

		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);

		$this->add_loc_forum_path($fid);
		$this->add_loc_topic();

		$params = '&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_ID.'='.$id;
		$murl = $url->get_url('delete_post',$params);
		$renderer->add_breadcrumb($locale->lang('deletepost'),$murl);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$forums = PLIB_Props::get()->forums();
		$auth = PLIB_Props::get()->auth();
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();

		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);

		// check other parameters
		if($fid == null || $tid == null || $id == null)
		{
			$this->report_error();
			return;
		}

		// forum closed?
		if(!$user->is_admin() && $forums->forum_is_closed($fid))
		{
			$this->report_error();
			return;
		}

		$post_data = BS_DAO::get_posts()->get_post_from_topic($id,$fid,$tid);
		if($post_data === false)
		{
			$this->report_error();
			return;
		}
		
		// topic closed?
		if($post_data['thread_closed'] == 1 && !$user->is_admin())
		{
			$this->report_error();
			return;
		}
		
		// delete not allowed?
		if(!$auth->has_current_forum_perm(BS_MODE_DELETE_POSTS,$post_data['post_user']))
		{
			$this->report_error(PLIB_Document_Messages::NO_ACCESS,$locale->lang('permission_denied'));
			return;
		}
		
		// topic-data available?
		$topic_data = BS_Front_TopicFactory::get_instance()->get_current_topic();
		if($topic_data === null)
		{
			$this->report_error();
			return;
		}
		
		$text = BS_PostingUtils::get_instance()->get_post_text($post_data);

		if($post_data['post_user'] > 0)
		{
			$username = BS_UserUtils::get_instance()->get_link(
				$post_data['post_user'],$post_data['user_name'],$post_data['user_group']
			);
		}
		else
			$username = $post_data['post_an_user'];
		
		$tpl->add_variables(array(
			'title' => sprintf($locale->lang('selected_post_from_topic'),$topic_data['name']),
			'target_url' => $url->get_url(
				0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_ID.'='.$id
			),
			'action_type' => BS_ACTION_DELETE_POSTS,
			'text' => $text,
			'user_name' => $username,
			'date' => PLIB_Date::get_date($post_data['post_time'],true,true),
			'post_id' => $post_data['pid'],
			'back_url' => $url->get_url(
				'posts','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid
			)
		));
	}
}
?>