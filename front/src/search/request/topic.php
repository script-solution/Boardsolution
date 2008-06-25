<?php
/**
 * Contains the topic-request-class for the search
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The topic-request for the search (searches for keywords in one topic)
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Request_Topic extends BS_Front_Search_Request_TPBasic
{
	/**
	 * The entered keywords
	 *
	 * @var string
	 */
	private $_keywords;
	
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
		return 'topic';
	}
	
	public function get_highlight_keywords()
	{
		return $this->_keywords;
	}
	
	public function encode_keywords()
	{
		return serialize($this->_keywords);
	}
	
	public function decode_keywords($keywords)
	{
		$this->_keywords = unserialize($keywords);
	}
	
	public function get_result_ids()
	{
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		if($tid == null)
		{
			$this->msgs->add_error($this->locale->lang('no_posts_found'));
			return null;
		}
		
		$keyword = $this->input->get_var('keyword','post',PLIB_Input::STRING);
		if(!BS_Front_Search_Utils::is_valid_keyword($keyword))
			return null;
		
		$keyword = BS_Front_Search_Utils::escape($keyword);
		$this->_keywords = BS_Front_Search_Utils::extract_keywords($keyword);
	
		if(count($this->_keywords) == 0)
		{
			$this->msgs->add_error(
				sprintf($this->locale->lang('search_missing_keyword'),BS_SEARCH_MIN_KEYWORD_LEN)
			);
			return null;
		}
		
		$sql = ' WHERE'.BS_Front_Search_Utils::build_search_cond(
			$this->_keywords,array('p.text_posted','t.name'),'AND'
		);
		$sql .= ' AND p.threadid = '.$tid;
		
		return $this->_get_result_ids('posts',$sql,250,$this->_keywords);
	}
	
	public function get_title($search)
	{
		return sprintf(
			$this->locale->lang('search_topic_for_posts'),
			count($search->get_result_ids()),
			stripslashes(implode('", "',$this->_keywords))
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