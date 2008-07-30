<?php
/**
 * Contains the basic-search-request-class
 *
 * @version			$Id$
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
	protected final function get_result_ids_impl($type = 'posts',$search_cond,$limit,$keywords = null)
	{
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();

		// limit the search to the allowed forums
		$denied = BS_ForumUtils::get_instance()->get_denied_forums(false);
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