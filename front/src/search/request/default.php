<?php
/**
 * Contains the default-search-request
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default search-request (searches for keywords and/or users in posts and topic-titles
 * and displays the result as topics or posts)
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Request_Default extends BS_Front_Search_Request_TPBasic
{
	/**
	 * The extracted keywords
	 *
	 * @var array
	 */
	private $_keywords;
	
	public function get_initial_result_type()
	{
		$result_types = array('topics','posts');
		return $this->input->correct_var('result_type','post',PLIB_Input::STRING,$result_types,'topics');
	}
	
	public function set_result_type($result)
	{
		switch($result)
		{
			case 'topics':
				$this->_result = new BS_Front_Search_Result_Topics();
				break;
			case 'posts':
				$this->_result = new BS_Front_Search_Result_Posts();
				break;
		}
	}
	
	public function get_name()
	{
		return 'default';
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
	
	public function get_result_ids()
	{
		$search_cond = $this->_get_search_condition();
		if($search_cond === null)
			return null;
		
		// TODO allow unlimited results?
		$limit_vals = array(10,25,50,100,250,500);
		$limit = $this->input->correct_var('limit','post',PLIB_Input::INTEGER,$limit_vals,250);
		
		$type = $this->_result instanceof BS_Front_Search_Result_Posts ? 'posts' : 'topics';
		return $this->_get_result_ids($type,$search_cond,$limit,$this->_keywords['kw']);
	}
	
	public function get_title($search)
	{
		$num = count($search->get_result_ids());
		$username_count = count($this->_keywords['un']);
		
		if($this->_result instanceof BS_Front_Search_Result_Posts && $username_count == 0)
		{
			return sprintf(
				$this->locale->lang('search_result_posts'),
				stripslashes(implode('", "',$this->_keywords['kw'])),
				$num
			);
		}
		else if($this->_result instanceof BS_Front_Search_Result_Posts)
		{
			return sprintf(
				$this->locale->lang('search_result_posts_usernames'),
				stripslashes(implode('", "',$this->_keywords['kw'])),
				stripslashes(implode('", "',$this->_keywords['un'])),
				$num
			);
		}
		else if($this->_result instanceof BS_Front_Search_Result_Topics && $username_count == 0)
		{
			return sprintf(
				$this->locale->lang('search_result_topics'),
				stripslashes(implode('", "',$this->_keywords['kw'])),
				$num
			);
		}
		else
		{
			return sprintf(
				$this->locale->lang('search_result_topics_usernames'),
				stripslashes(implode('", "',$this->_keywords['kw'])),
				stripslashes(implode('", "',$this->_keywords['un'])),
				$num
			);
		}
	}
	
	public function get_order()
	{
		$order_vals = array('topic_name','topic_type','replies','views','date','relevance');
		$order = $this->input->get_var('order','post',PLIB_Input::STRING);
		if($order == null)
			$order = $this->input->get_var(BS_URL_ORDER,'get',PLIB_Input::STRING);

		if(!in_array($order,$order_vals))
			$order = 'relevance';

		$ad_vals = array('ASC','DESC');
		$ad = $this->input->get_var('ad','post',PLIB_Input::STRING);
		if($ad == null)
			$ad = $this->input->get_var(BS_URL_AD,'get',PLIB_Input::STRING);

		if(!in_array($ad,$ad_vals))
			$ad = 'DESC';
		
		return array($order,$ad);
	}
	
	/**
	 * Builds the search-condition for the query
	 *
	 * @return string the condition
	 */
	private function _get_search_condition()
	{
		$keyword = $this->input->get_var('keyword','post',PLIB_Input::STRING);
		if($keyword === null)
			$keyword = $this->input->get_var(BS_URL_KW,'get',PLIB_Input::STRING);
		
		$username = $this->input->get_var('un','post',PLIB_Input::STRING);
		if($username === null)
			$username = $this->input->get_var(BS_URL_UN,'get',PLIB_Input::STRING);
		
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

		$fid = $this->input->get_var('fid','post');
		$sql = '';

		// parse keywords
		$sql .= BS_Front_Search_Utils::build_search_cond(
			$this->_keywords['kw'],array('p.text_posted','t.name'),$keyword_mode
		);
		
		$un = BS_Front_Search_Utils::build_search_cond(
			$this->_keywords['un'],array('u.`'.BS_EXPORT_USER_NAME.'`','p.post_an_user'),'OR'
		);
		if($un != '')
			$sql .= ($sql != '' ? ' AND ' : '').$un;
		
		$sql = ' WHERE'.$sql;
		if($fid != null)
		{
			$fids = array();
			for($i = 0;$i < count($fid);$i++)
			{
				if(PLIB_Helper::is_integer($fid[$i]) && $fid[$i] != 0)
					$fids[] = $fid[$i];
			}
			
			if(count($fids) > 0)
				$sql .= ' AND p.rubrikid IN ('.implode(',',$fids).')';
		}

		return $sql;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>