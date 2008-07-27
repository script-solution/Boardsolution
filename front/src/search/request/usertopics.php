<?php
/**
 * Contains the user-topics-request-class for the search
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The user-topics-request for the search (searches for all created topics of a user)
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Request_UserTopics extends BS_Front_Search_Request_TPBasic
{
	/**
	 * The username whose topics should be displayed.
	 *
	 * @var string
	 */
	private $_username;
	
	public function get_initial_result_type()
	{
		return 'topics';
	}
	
	public function set_result_type($result)
	{
		// the argument is irrelevant here
		$this->_result = new BS_Front_Search_Result_Topics();
	}
	
	public function get_name()
	{
		return 'user_topics';
	}
	
	public function get_highlight_keywords()
	{
		return array();
	}
	
	public function get_url_params()
	{
		$input = PLIB_Props::get()->input();

		$uid = $input->get_var(BS_URL_PID,'get',PLIB_Input::ID);
		return array(BS_URL_PID => $uid);
	}
	
	public function encode_keywords()
	{
		$input = PLIB_Props::get()->input();

		$uid = $input->get_var(BS_URL_PID,'get',PLIB_Input::ID);
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
		$input = PLIB_Props::get()->input();
		$msgs = PLIB_Props::get()->msgs();
		$locale = PLIB_Props::get()->locale();

		$uid = $input->get_var(BS_URL_PID,'get',PLIB_Input::ID);
		if($uid == null)
		{
			$msgs->add_error($locale->lang('search_user_id_empty'));
			return null;
		}
		
		// TODO allow unlimited results?
		$limit_vals = array(10,25,50,100,250,500);
		$limit = $input->correct_var('limit','post',PLIB_Input::INTEGER,$limit_vals,250);

		$search_cond = ' WHERE t.post_user = '.$uid;
		return $this->get_result_ids_impl('topics',$search_cond,$limit);
	}
	
	public function get_title($search)
	{
		$locale = PLIB_Props::get()->locale();

		return sprintf(
			$locale->lang('search_result_user_topics'),
			count($search->get_result_ids()),
			$this->_username
		);
	}
	
	public function get_order()
	{
		return array('date','DESC');
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>