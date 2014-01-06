<?php
/**
 * Contains the pm-history-request-class.
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
 * The pm-history-request. Searches for all PMs with a similar subject. That means all that have
 * the same subject ignoring the (RE: )* at the beginning.
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Request_PMHistory extends BS_Front_Search_Request_PMBasic
{
	/**
	 * The keyword
	 *
	 * @var string
	 */
	private $_keyword;
	
	public function get_name()
	{
		return 'history';
	}

	public function get_initial_result_type()
	{
		return 'pm_history';
	}

	public function set_result_type($result)
	{
		$this->_result = new BS_Front_Search_Result_PMHistory();
	}
	
	public function get_highlight_keywords()
	{
		return array($this->_keyword);
	}
	
	public function get_url_params()
	{
		$input = FWS_Props::get()->input();

		$id = $input->get_var(BS_URL_KW,'get',FWS_Input::ID);
		return array(BS_URL_KW => $id);
	}
	
	public function encode_keywords()
	{
		return $this->_keyword;
	}
	
	public function decode_keywords($keywords)
	{
		$this->_keyword = $keywords;
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

		return sprintf(
			$locale->lang('search_result_pm_history'),
			$this->_keyword
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
		$user = FWS_Props::get()->user();

		$id = $input->get_var(BS_URL_KW,'get',FWS_Input::ID);
		if($id == null)
		{
			$msgs->add_error($locale->lang('search_pm_id_invalid'));
			return null;
		}

		$data = BS_DAO::get_pms()->get_by_id($id);
		if($data === false)
		{
			$msgs->add_error($locale->lang('search_pm_id_invalid'));
			return null;
		}

		$subject = preg_replace('/^(RE: )*(.*)/','\\2',$data['pm_title']);
		if($data['receiver_id'] == $user->get_user_id())
			$other_user = $data['sender_id'];
		else
			$other_user = $data['receiver_id'];
		$users = 'IN ('.$user->get_user_id().','.$other_user.')';

		$this->_keyword = $subject;
		return ' WHERE p.pm_title LIKE \'%'.addslashes($subject)."' AND p.receiver_id ".$users
			." AND p.sender_id ".$users;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>