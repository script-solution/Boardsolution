<?php
/**
 * Contains the similar-topics-request-class for the search
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The similar-topics-request for the search (searches for similar topics which contain one
 * of a set of keywords)
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Request_SimilarTopics extends BS_Front_Search_Request_TPBasic
{
	/**
	 * The entered keywords
	 *
	 * @var string
	 */
	private $_keywords;
	
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
		return 'similar_topics';
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
		$keyword = $this->input->get_var(BS_URL_KW,'get',PLIB_Input::STRING);
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

		// parse keywords
		$sql = ' WHERE'.BS_Front_Search_Utils::build_search_cond(
			$this->_keywords,array('p.text_posted','t.name'),'OR'
		);
		
		return $this->_get_result_ids('topics',$sql,250);
	}
	
	public function get_title($search)
	{
		return sprintf(
			$this->locale->lang('search_result_topics'),
			stripslashes(implode('", "',$this->_keywords)),
			count($search->get_result_ids())
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