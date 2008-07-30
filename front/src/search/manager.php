<?php
/**
 * Contains the search-manager-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The manager for the search which controls everything
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Manager extends FWS_Object
{
	/**
	 * An array of all found ids
	 *
	 * @var array
	 */
	private $_result_ids = array();
	
	/**
	 * Our search-id
	 *
	 * @var int
	 */
	private $_search_id = 0;
	
	/**
	 * Our object which builds the search-request and retrieves the found ids
	 *
	 * @var BS_Front_Search_Request
	 */
	private $_request;
	
	/**
	 * Constructor
	 * 
	 * @param string the id of the search (0 if not initialized)
	 * @param BS_Front_Search_Request the request-object
	 */
	public function __construct($search_id,$request)
	{
		parent::__construct();
		
		if(!($request instanceof BS_Front_Search_Request))
			FWS_Helper::def_error('instance','request','BS_Front_Search_Request',$request);
		
		$this->_search_id = $search_id;
		$this->_request = $request;
		
		// delete old search-queries
		BS_DAO::get_search()->delete_timedout(3600);
		
		// init the search
		if(!$this->_search_id)
			$this->_perform_search();
		else
		{
			if(!$this->_init_existing_search($this->_search_id))
				$this->_perform_search();
		}
	}
	
	/**
	 * @return int the search-id
	 */
	public function get_search_id()
	{
		return $this->_search_id;
	}
	
	/**
	 * @return array an array with the result-ids
	 */
	public function get_result_ids()
	{
		return $this->_result_ids;
	}
	
	/**
	 * Adds the result to the template
	 */
	public function add_result()
	{
		$this->_request->get_result()->display_result($this,$this->_request);
	}

	/**
	 * Searches the database for the entered keywords and stores the result in the search-table
	 */
	private function _perform_search()
	{
		$auth = FWS_Props::get()->auth();
		$ips = FWS_Props::get()->ips();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();

		$spam_enabled = $auth->is_ipblock_enabled('spam_search');
		
		if($spam_enabled)
		{
			// at first delete the "dead" entries
			// check if an entry exists for the current user
			$ip_entry = $ips->get_entry('search');
			if($ip_entry['date'] != '')
			{
				$msgs->add_error(sprintf(
					$locale->lang('spam_search_detected'),
					$cfg['spam_search'] - (time() - $ip_entry['date'])
				));
				return;
			}
		}
		
		$result_type = $this->_request->get_initial_result_type();
		$this->_request->set_result_type($result_type);

		$ids = $this->_request->get_result_ids();
		if($ids === null)
			return;

		// create ip-table entry
		$ips->add_entry('search');
		
		// store all found ids in the db
		$mode = $this->_request->get_name();
		$search = array(
			'session_id' => $user->get_session_id(),
			'search_date' => time(),
			'search_mode' => $mode,
			'result_ids' => implode(',',$ids),
			'result_type' => $result_type,
			'keywords' => $this->_request->encode_keywords()
		);

		$this->_search_id = BS_DAO::get_search()->create($search);
		
		$this->_result_ids = $ids;
	}

	/**
	 * grabs the search-data with given id from the database and inits the private fields
	 *
	 * @param int $search_id the id of the search
	 * @return boolean true if successfull
	 */
	private function _init_existing_search($search_id)
	{
		$user = FWS_Props::get()->user();

		$data = BS_DAO::get_search()->get_by_id($search_id);
		if($data === false || $data['session_id'] != $user->get_session_id())
			return false;

		if($this->_request->get_name() != $data['search_mode'])
			FWS_Helper::def_error('Invalid search-mode. "'.$this->_request->get_name().'" expected!');
		
		$this->_result_ids = explode(',',$data['result_ids']);
		$this->_request->set_result_type($data['result_type']);
		$this->_request->decode_keywords($data['keywords']);
		return true;
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>