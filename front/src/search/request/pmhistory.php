<?php
/**
 * Contains the pm-history-request-class.
 *
 * @version			$Id: pmhistory.php 724 2008-05-22 14:37:18Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	 * @var array
	 */
	private $_keyword;
	
	public function get_name()
	{
		return 'pms';
	}

	public function get_initial_result_type()
	{
		return 'pmhistory';
	}

	public function set_result_type($result)
	{
		$this->_result = new BS_Front_Search_Result_PMHistory();
	}
	
	public function get_highlight_keywords()
	{
		return array($this->_keyword);
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
		return sprintf(
			$this->locale->lang('search_result_pm_history'),
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
		$id = $this->input->get_var(BS_URL_KW,'get',PLIB_Input::ID);
		if($id == null)
		{
			$this->msgs->add_error($this->locale->lang('search_pm_id_invalid'));
			return null;
		}

		$data = BS_DAO::get_pms()->get_by_id($id);
		if($data === false)
		{
			$this->msgs->add_error($this->locale->lang('search_pm_id_invalid'));
			return null;
		}

		$subject = preg_replace('/^(RE: )*(.*)/','\\2',$data['pm_title']);
		if($data['receiver_id'] == $this->user->get_user_id())
			$other_user = $data['sender_id'];
		else
			$other_user = $data['receiver_id'];
		$users = 'IN ('.$this->user->get_user_id().','.$other_user.')';

		$this->_keyword = $subject;
		return ' WHERE p.pm_title LIKE \'%'.addslashes($subject)."' AND p.receiver_id ".$users
			." AND p.sender_id ".$users;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>