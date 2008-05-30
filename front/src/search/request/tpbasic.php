<?php
/**
 * Contains the basic-search-request-class
 *
 * @version			$Id: tpbasic.php 802 2008-05-30 06:51:57Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The basic-implementation for all requests which have a topics- or posts-result.
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_Search_Request_TPBasic extends PLIB_FullObject
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
	 * @return array an array with the found ids
	 */
	protected final function _get_result_ids($type = 'posts',$search_cond,$limit)
	{
		// limit the search to the allowed forums
		$denied = BS_ForumUtils::get_instance()->get_denied_forums(false);
		if(count($denied) > 0)
			$search_cond .= ' AND p.rubrikid NOT IN ('.implode(',',$denied).')';
		
		list($order,$ad) = $this->get_order();
		$sql_order = BS_Front_Search_Utils::get_sql_order($order,$ad,$type);
		
		$ids = array();
		foreach(BS_DAO::get_posts()->get_posts_by_search($search_cond,$sql_order,$type,$limit) as $data)
			$ids[] = $type == 'posts' ? $data['id'] : $data['threadid'];
		
		if(count($ids) == 0)
			$this->msgs->add_notice($this->locale->lang('no_'.$type.'_found'));
		
		return $ids;
	}
}
?>