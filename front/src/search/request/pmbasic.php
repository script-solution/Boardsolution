<?php
/**
 * Contains the basic search-request-class for the PMs
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
 * The basic-implementation for all PM-requests
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_Search_Request_PMBasic extends FWS_Object implements BS_Front_Search_Request
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
		$user = FWS_Props::get()->user();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();

		$ids = BS_DAO::get_pms()->get_pm_ids_by_search($user->get_user_id(),$search_cond);
		if(count($ids) == 0)
			$msgs->add_notice($locale->lang('no_pms_found'));
		
		return $ids;
	}
}
?>