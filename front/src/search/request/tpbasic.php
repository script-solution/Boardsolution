<?php
/**
 * Contains the basic-search-request-class
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
 * The basic-implementation for all requests which have a topics- or posts-result.
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_Search_Request_TPBasic extends FWS_Object
	implements BS_Front_Search_Request
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
	 * Builds the query to retrieve the result-ids for the topic- and posts-search.
	 *
	 * @param string $type the type: posts or topics
	 * @param string $search_cond the search-condition
	 * @param int $limit the max. number of results
	 * @param array $keywords you may specify an array with keywords for "MATCH(...) AGAINST ..."
	 * @return array an array with the found ids
	 */
	protected final function get_result_ids_impl($type,$search_cond,$limit,$keywords = null)
	{
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();

		// limit the search to the allowed forums
		$denied = BS_ForumUtils::get_denied_forums(false);
		if(count($denied) > 0)
			$search_cond .= ' AND p.rubrikid NOT IN ('.implode(',',$denied).')';
		
		list($order,$ad) = $this->get_order();
		$sql_order = BS_Front_Search_Utils::get_sql_order($order,$ad,$type);
		
		$ids = array();
		$postlist = BS_DAO::get_posts()->get_posts_by_search(
			$search_cond,$sql_order,$type,$limit,$keywords
		);
		foreach($postlist as $data)
			$ids[] = $type == 'posts' ? $data['id'] : $data['threadid'];
		
		if(count($ids) == 0)
			$msgs->add_notice($locale->lang('no_'.$type.'_found'));
		
		return $ids;
	}
}
?>