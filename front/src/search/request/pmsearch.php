<?php
/**
 * Contains the pm-search-request-class.
 *
 * @version			$Id$
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
	
	public function get_url_params()
	{
		$params = array();
		foreach($this->_keywords as $name => $kws)
		{
			$str = '';
			foreach($kws as $kw)
				$str .= '"'.$kw.'" ';
			$params[$name == 'kw' ? BS_URL_KW : BS_URL_UN] = urlencode(rtrim($str));
		}
		return $params;
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
		$input = FWS_Props::get()->input();

		$order_vals = array('subject','folder','date');
		$order = $input->get_var('order','post',FWS_Input::STRING);
		if($order == null)
			$order = $input->get_var(BS_URL_ORDER,'get',FWS_Input::STRING);
		$order = in_array($order,$order_vals) ? $order : 'date';
		
		$ad = $input->get_var('ad','post',FWS_Input::STRING);
		if($ad == null)
			$ad = $input->get_var(BS_URL_AD,'get',FWS_Input::STRING);
		$ad = $ad == 'ASC' ? 'ASC' : 'DESC';
		
		return array($order,$ad);
	}

	public function get_result_ids()
	{
		$search_cond = $this->_get_search_condition();
		if($search_cond === null)
			return null;
		
		return $this->get_result_ids_impl($search_cond);
	}

	public function get_title($search)
	{
		$locale = FWS_Props::get()->locale();

		$num = count($search->get_result_ids());
		
		if(count($this->_keywords['un']) == 0)
		{
			return sprintf(
				$locale->lang('search_result_pms'),
				stripslashes(implode('", "',$this->_keywords['kw'])),
				$num
			);
		}

		return sprintf(
			$locale->lang('search_result_pms_usernames'),
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
		$input = FWS_Props::get()->input();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();

		$keyword = $input->get_var('keyword','post',FWS_Input::STRING);
		if($keyword === null)
			$keyword = $input->get_var(BS_URL_KW,'get',FWS_Input::STRING);
		
		$username = $input->get_var('un','post',FWS_Input::STRING);
		if($username === null)
			$username = $input->get_var(BS_URL_UN,'get',FWS_Input::STRING);
		
		$keyword_mode = $input->get_var('keyword_mode','post',FWS_Input::STRING);
		$keyword_mode = ($keyword_mode == 'and') ? 'AND' : 'OR';
		
		$keyword_len = FWS_String::strlen($keyword);
		if($keyword_len == 0 && $username == '')
		{
			$msgs->add_error(
				sprintf($locale->lang('search_missing_keyword'),BS_SEARCH_MIN_KEYWORD_LEN)
			);
			return null;
		}

		if($keyword_len > 255 || FWS_String::strlen($username) > 255)
		{
			$msgs->add_error($locale->lang('keyword_max_length'));
			return null;
		}

		$keyword = BS_Front_Search_Utils::escape($keyword);
		$username = BS_Front_Search_Utils::escape($username);
		$this->_keywords['kw'] = BS_Front_Search_Utils::extract_keywords($keyword);
		$this->_keywords['un'] = BS_Front_Search_Utils::extract_keywords($username);

		if(count($this->_keywords['kw']) == 0 && count($this->_keywords['un']) == 0)
		{
			$msgs->add_error(
				sprintf($locale->lang('search_missing_keyword'),BS_SEARCH_MIN_KEYWORD_LEN)
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
				$string = str_replace('*','%',trim(FWS_String::strtolower($string)));
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
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>