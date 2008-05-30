<?php
/**
 * Contains the pm-search-request-class.
 *
 * @version			$Id: pmsearch.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pm-search-request. Searches for keywords in PMs
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Request_PMSearch extends BS_Front_Search_Request_PMBasic
{
	/**
	 * The extracted keywords
	 *
	 * @var array
	 */
	private $_keywords;
	
	public function get_name()
	{
		return 'pms';
	}

	public function get_initial_result_type()
	{
		return 'pms';
	}

	public function set_result_type($result)
	{
		$this->_result = new BS_Front_Search_Result_PMs();
	}
	
	public function get_highlight_keywords()
	{
		return $this->_keywords['kw'];
	}
	
	public function encode_keywords()
	{
		return serialize($this->_keywords);
	}
	
	public function decode_keywords($keywords)
	{
		$this->_keywords = unserialize($keywords);
	}

	public function get_order()
	{
		$order_vals = array('subject','folder','date');
		$order = $this->input->get_var('order','post',PLIB_Input::STRING);
		if($order == null)
			$order = $this->input->get_var(BS_URL_ORDER,'get',PLIB_Input::STRING);
		$order = in_array($order,$order_vals) ? $order : 'date';
		
		$ad = $this->input->get_var('ad','post',PLIB_Input::STRING);
		if($ad == null)
			$ad = $this->input->get_var(BS_URL_AD,'get',PLIB_Input::STRING);
		$ad = $ad == 'ASC' ? 'ASC' : 'DESC';
		
		return array($order,$ad);
	}

	public function get_result_ids()
	{
		$search_cond = $this->_get_search_condition();
		if($search_cond === null)
			return null;
		
		return $this->_get_result_ids($search_cond);
	}

	public function get_title($search)
	{
		$num = count($search->get_result_ids());
		
		if(count($this->_keywords['un']) == 0)
		{
			return sprintf(
				$this->locale->lang('search_result_pms'),
				stripslashes(implode('", "',$this->_keywords['kw'])),
				$num
			);
		}

		return sprintf(
			$this->locale->lang('search_result_pms_usernames'),
			stripslashes(implode('", "',$this->_keywords['kw'])),
			stripslashes(implode('", "',$this->_keywords['un'])),
			$num
		);
	}
	
	/**
	 * Builds the search-condition for the query
	 *
	 * @return string the condition
	 */
	private function _get_search_condition()
	{
		$keyword = $this->input->get_var('keyword','post',PLIB_Input::STRING);
		$username = $this->input->get_var('un','post',PLIB_Input::STRING);
		$keyword_mode = $this->input->get_var('keyword_mode','post',PLIB_Input::STRING);
		$keyword_mode = ($keyword_mode == 'and') ? 'AND' : 'OR';
		
		$keyword_len = PLIB_String::strlen($keyword);
		if($keyword_len == 0 && $username == '')
		{
			$this->msgs->add_error(
				sprintf($this->locale->lang('search_missing_keyword'),BS_SEARCH_MIN_KEYWORD_LEN)
			);
			return null;
		}

		if($keyword_len > 255 || PLIB_String::strlen($username) > 255)
		{
			$this->msgs->add_error($this->locale->lang('keyword_max_length'));
			return null;
		}

		$keyword = BS_Front_Search_Utils::escape($keyword);
		$username = BS_Front_Search_Utils::escape($username);
		$this->_keywords['kw'] = BS_Front_Search_Utils::extract_keywords($keyword);
		$this->_keywords['un'] = BS_Front_Search_Utils::extract_keywords($username);

		if(count($this->_keywords['kw']) == 0 && count($this->_keywords['un']) == 0)
		{
			$this->msgs->add_error(
				sprintf($this->locale->lang('search_missing_keyword'),BS_SEARCH_MIN_KEYWORD_LEN)
			);
			return null;
		}

		// parse keywords
		$sql = BS_Front_Search_Utils::build_search_cond(
			$this->_keywords['kw'],array('pm_text_posted','pm_title'),$keyword_mode
		);
		
		$un = '';
		if(count($this->_keywords['un']) > 0)
		{
			$un .= '(';
			$i = 0;
			foreach($this->_keywords['un'] as $string)
			{
				$string = str_replace('*','%',trim(PLIB_String::strtolower($string)));
				$un .= ($un != ' (' && $i > 0) ? ' OR ' : '';
				$un .= "((LOWER(u1.`".BS_EXPORT_USER_NAME."`) LIKE '%".$string."%' AND pm_type = 'outbox')";
				$un .= " OR (LOWER(u2.`".BS_EXPORT_USER_NAME."`) LIKE '%".$string."%' AND pm_type = 'inbox'))";
				$i++;
			}
			$un .= ')';
		}
		
		if($un != '')
			$sql .= ($sql != '' ? ' AND ' : '').$un;
		
		$sql = ' WHERE'.$sql;
		return $sql;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>