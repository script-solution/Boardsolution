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
		$input = FWS_Props::get()->input();

		$result_types = array('topics','posts');
		return $input->correct_var('result_type','post',FWS_Input::STRING,$result_types,'topics');
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
		$input = FWS_Props::get()->input();

		$search_cond = $this->_get_search_condition();
		if($search_cond === null)
			return null;
		
		// TODO allow unlimited results?
		$limit_vals = array(10,25,50,100,250,500);
		$limit = $input->correct_var('limit','post',FWS_Input::INTEGER,$limit_vals,250);
		
		$type = $this->_result instanceof BS_Front_Search_Result_Posts ? 'posts' : 'topics';
		return $this->get_result_ids_impl($type,$search_cond,$limit,$this->_keywords['kw']);
	}
	
	public function get_title($search)
	{
		$locale = FWS_Props::get()->locale();

		$num = count($search->get_result_ids());
		$username_count = count($this->_keywords['un']);
		
		if($this->_result instanceof BS_Front_Search_Result_Posts && $username_count == 0)
		{
			return sprintf(
				$locale->lang('search_result_posts'),
				stripslashes(implode('", "',$this->_keywords['kw'])),
				$num
			);
		}
		else if($this->_result instanceof BS_Front_Search_Result_Posts)
		{
			return sprintf(
				$locale->lang('search_result_posts_usernames'),
				stripslashes(implode('", "',$this->_keywords['kw'])),
				stripslashes(implode('", "',$this->_keywords['un'])),
				$num
			);
		}
		else if($this->_result instanceof BS_Front_Search_Result_Topics && $username_count == 0)
		{
			return sprintf(
				$locale->lang('search_result_topics'),
				stripslashes(implode('", "',$this->_keywords['kw'])),
				$num
			);
		}
		else
		{
			return sprintf(
				$locale->lang('search_result_topics_usernames'),
				stripslashes(implode('", "',$this->_keywords['kw'])),
				stripslashes(implode('", "',$this->_keywords['un'])),
				$num
			);
		}
	}
	
	public function get_order()
	{
		$input = FWS_Props::get()->input();

		$order_vals = array('topic_name','topic_type','replies','views','date','relevance');
		$order = $input->get_var('order','post',FWS_Input::STRING);
		if($order == null)
			$order = $input->get_var(BS_URL_ORDER,'get',FWS_Input::STRING);

		if(!in_array($order,$order_vals))
			$order = 'relevance';

		$ad_vals = array('ASC','DESC');
		$ad = $input->get_var('ad','post',FWS_Input::STRING);
		if($ad == null)
			$ad = $input->get_var(BS_URL_AD,'get',FWS_Input::STRING);

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

		$fid = $input->get_var('fid','post');
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
				if(FWS_Helper::is_integer($fid[$i]) && $fid[$i] != 0)
					$fids[] = $fid[$i];
			}
			
			if(count($fids) > 0)
				$sql .= ' AND p.rubrikid IN ('.implode(',',$fids).')';
		}

		return $sql;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>