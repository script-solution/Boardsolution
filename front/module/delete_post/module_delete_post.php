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
	public function get_actions()
	{
		return array(
			BS_ACTION_DELETE_POSTS => 'default'
		);
	}
	
	public function run()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);

		// check other parameters
		if($fid == null || $tid == null || $id == null)
		{
			$this->_report_error();
			return;
		}

		// forum closed?
		if(!$this->user->is_admin() && $this->forums->forum_is_closed($fid))
		{
			$this->_report_error();
			return;
		}

		$post_data = BS_DAO::get_posts()->get_post_from_topic($id,$fid,$tid);
		if($post_data === false)
		{
			$this->_report_error();
			return;
		}
		
		// topic closed?
		if($post_data['thread_closed'] == 1 && !$this->user->is_admin())
		{
			$this->_report_error();
			return;
		}
		
		// delete not allowed?
		if(!$this->auth->has_current_forum_perm(BS_MODE_DELETE_POSTS,$post_data['post_user']))
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS,$this->locale->lang('permission_denied'));
			return;
		}

		$text = BS_PostingUtils::get_instance()->get_post_text($post_data);

		if($post_data['post_user'] > 0)
		{
			$user = BS_UserUtils::get_instance()->get_link(
				$post_data['post_user'],$post_data['user_name'],$post_data['user_group']
			);
		}
		else
			$user = $post_data['post_an_user'];

		$topic_data = $this->cache->get_cache('topic')->current();
		$this->tpl->add_variables(array(
			'title' => sprintf($this->locale->lang('selected_post_from_topic'),$topic_data['name']),
			'target_url' => $this->url->get_url(
				0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_ID.'='.$id
			),
			'action_type' => BS_ACTION_DELETE_POSTS,
			'text' => $text,
			'user_name' => $user,
			'date' => PLIB_Date::get_date($post_data['post_time'],true,true),
			'post_id' => $post_data['pid'],
			'back_url' => $this->url->get_url(
				'posts','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid
			)
		));
	}

	public function get_location()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);

		$result = array();
		$this->_add_loc_forum_path($result,$fid);
		$this->_add_loc_topic($result);

		$params = '&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_ID.'='.$id;
		$url = $this->url->get_url('delete_post',$params);
		$result[$this->locale->lang('deletepost')] = $url;

		return $result;
	}

	public function has_access()
	{
		return $this->user->is_loggedin();
	}
}
?>