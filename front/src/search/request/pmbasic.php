<?php
/**
 * Contains the basic search-request-class for the PMs
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The basic-implementation for all PM-requests
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_Search_Request_PMBasic extends PLIB_Object implements BS_Front_Search_Request
{
	/**
	 * The result-object
	 *
	 * @var BS_Front_Search_Result
	 */
	protected $_result;
	
	public final function get_result()
	{
		return $this->_result;
	}
	
	/**
	 * Builds the query to retrieve the result-ids for PM-requests
	 *
	 * @param string $search_cond the search-condition
	 * @return array an array with the found ids
	 */
	protected final function get_result_ids_impl($search_cond)
	{
		$user = PLIB_Props::get()->user();
		$msgs = PLIB_Props::get()->msgs();
		$locale = PLIB_Props::get()->locale();

		$ids = BS_DAO::get_pms()->get_pm_ids_by_search($user->get_user_id(),$search_cond);
		if(count($ids) == 0)
			$msgs->add_notice($locale->lang('no_pms_found'));
		
		return $ids;
	}
}
?>