<?php
/**
 * Contains the topic-request-class for the search
 * 
 * @package			Boardsolution
 * @subpackage	front.src.search
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
	 * @var array
	 */
	private $_keywords;
	
	/**
	 * The topic-id
	 * 
	 * @var int
	 */
	private $_tid;
	
	/**
	 * Constructor
	 * 
	 * @param int $tid the topic-id
	 */
	public function __construct($tid = 0)
	{
		$this->_tid = $tid;
		if(!$this->_tid)
			$this->_tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
	}
	
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
	
	public function get_url_params()
	{
		$params = array();
		$str = '';
		foreach($this->_keywords as $kw)
			$str .= '"'.$kw.'" ';
		$params[BS_URL_KW] = rtrim($str);
		$params[BS_URL_MODE] = 'topic';
		$params[BS_URL_TID] = $this->_tid;
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
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();

		if($this->_tid == null)
		{
			$msgs->add_error($locale->lang('no_posts_found'));
			return null;
		}
		
		$keyword = $input->get_var('keyword','post',FWS_Input::STRING);
		if($keyword === null)
			$keyword = $input->get_var(BS_URL_KW,'get',FWS_Input::STRING);
		
		if(!BS_Front_Search_Utils::is_valid_keyword($keyword))
			return null;
		
		$keyword = BS_Front_Search_Utils::escape($keyword);
		$this->_keywords = BS_Front_Search_Utils::extract_keywords($keyword);
	
		if(count($this->_keywords) == 0)
		{
			$msgs->add_error(
				sprintf($locale->lang('search_missing_keyword'),BS_SEARCH_MIN_KEYWORD_LEN)
			);
			return null;
		}
		
		$sql = ' WHERE'.BS_Front_Search_Utils::build_search_cond(
			$this->_keywords,array('p.text_posted','t.name'),'AND'
		);
		$sql .= ' AND p.threadid = '.$this->_tid;
		
		return $this->get_result_ids_impl('posts',$sql,250,$this->_keywords);
	}
	
	public function get_title($search)
	{
		$locale = FWS_Props::get()->locale();

		return sprintf(
			$locale->lang('search_topic_for_posts'),
			count($search->get_result_ids()),
			stripslashes(implode('", "',$this->_keywords))
		);
	}
	
	public function get_order()
	{
		return array('relevance','DESC');
	}
	
	public function get_keyword_mode(){}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>