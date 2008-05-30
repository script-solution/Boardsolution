<?php
/**
 * Contains the user-posts-request-class for the search
 *
 * @version			$Id: userposts.php 713 2008-05-20 21:59:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The user-posts-request for the search (searches for all created posts of a user)
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Request_UserPosts extends BS_Front_Search_Request_TPBasic
{
	public function get_initial_result_type()
	{
		return 'posts';
	}
	
	public function set_result_type($result)
	{
		// the argument is irrelevant here
		$this->_result = new BS_Front_Search_Result_Posts();
	}
	
	public function get_name()
	{
		return 'user_posts';
	}
	
	public function get_highlight_keywords()
	{
		return array();
	}
	
	public function encode_keywords()
	{
		$uid = $this->input->get_var(BS_URL_PID,'get',PLIB_Input::ID);
		if($uid == null)
			return null;
		
		$user = BS_DAO::get_user()->get_user_by_id($uid);
		if($user === false)
			return null;
		
		$this->_username = $user['user_name'];
		return $user['user_name'];
	}
	
	public function decode_keywords($keywords)
	{
		$this->_username = $keywords;
	}
	
	public function get_result_ids()
	{
		$uid = $this->input->get_var(BS_URL_PID,'get',PLIB_Input::ID);
		if($uid == null)
		{
			$this->msgs->add_error($this->locale->lang('search_user_id_empty'));
			return null;
		}
		
		// TODO allow unlimited results?
		$limit_vals = array(10,25,50,100,250,500);
		$limit = $this->input->correct_var('limit','post',PLIB_Input::INTEGER,$limit_vals,250);

		$search_cond = ' WHERE p.post_user = '.$uid;
		return $this->_get_result_ids('posts',$search_cond,$limit);
	}
	
	public function get_title($search)
	{
		return sprintf(
			$this->locale->lang('search_result_user_posts'),
			count($search->get_result_ids()),
			$this->_username
		);
	}
	
	public function get_order()
	{
		return array('date','DESC');
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>