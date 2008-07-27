<?php
/**
 * Contains the post-container-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.post
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * A container for posts. Grabs the posts from the database and provides access them and some
 * stuff around.
 *
 * @package			Boardsolution
 * @subpackage	front.src.post
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Post_Container extends PLIB_Object
{
	/**
	 * All posts in BS_Front_Post_Data objects
	 *
	 * @var array
	 */
	private $_posts;
	
	/**
	 * All attachments of the posts:
	 * <code>
	 * 	array(
	 * 		<postid> => array(
	 * 			<attachment1>,
	 * 			...
	 * 		),
	 * 		...
	 * 	)
	 * </code>
	 * 
	 * @var array
	 */
	private $_attachments = null;
	
	/**
	 * The pagination
	 *
	 * @var PLIB_Pagination
	 */
	private $_pagination;
	
	/**
	 * The additional fields
	 *
	 * @var array
	 */
	private $_add_fields = null;
	
	/**
	 * The search-keywords
	 *
	 * @var array
	 */
	private $_keywords = false;
	
	/**
	 * Constructor
	 *
	 * @param int $fid the forum-id (0 = ignore)
	 * @param int $tid the topic-id (0 = ignore)
	 * @param array $ids optionally you can specify all post-ids
	 * @param PLIB_Pagination $pagination the pagination (null = all posts)
	 * @param string $order the value for the ORDER BY statement
	 * @param string $search additional search-conditions for WHERE (not beginning with AND)
	 * @param array $keywords an numeric array with all keywords
	 */
	public function __construct($fid,$tid,$ids,$pagination = null,$order = 'p.id DESC',$search = '',
		$keywords = null)
	{
		if(!$ids && !$fid && !$tid && !$search)
			PLIB_Helper::error('Please provide at least one of the parameters $fid,$tid,$search and $ids!');
		if($keywords !== null && !is_array($keywords))
			PLIB_Helper::def_error('array','keywords',$keywords);
		
		$this->_pagination = $pagination;
		$this->_keywords = $keywords;
		
		// build where-condition
		$where = '';
		$denied = BS_ForumUtils::get_instance()->get_denied_forums(false);
		if(count($denied) > 0)
			$where .= 'p.rubrikid NOT IN ('.implode(',',$denied).') AND ';
		if($ids)
			$where .= 'p.id IN ('.implode(',',$ids).') AND ';
		if($fid)
			$where .= 'p.rubrikid = '.$fid.' AND ';
		if($tid)
			$where .= 'p.threadid = '.$tid.' AND ';
		if($search)
			$where .= $search.' AND ';
		
		// perform query
		$start = $count = 0;
		if($pagination !== null)
		{
			$start = $pagination->get_start();
			$count = $pagination->get_per_page();
		}
		$where = 'WHERE '.PLIB_String::substr($where,0,-5);
		
		$postlist = BS_DAO::get_posts()->get_posts_for_topic(
			$where,$order,$start,$count,$this->_keywords
		);
		
		// set pagination if not already done
		if($pagination === null)
		{
			$num = count($postlist);
			$pagination = new BS_Pagination($num == 0 ? 1 : $num,$num);
		}
		
		// save the result
		$this->_posts = array();
		foreach($postlist as $i => $data)
			$this->_posts[] = new BS_Front_Post_Data($i,$this,$data);
	}
	
	/**
	 * @return array the additional fields which should be displayed at the posts
	 */
	public function get_additional_fields()
	{
		if($this->_add_fields === null)
		{
			$cfields = BS_AddField_Manager::get_instance();
			$this->_add_fields = $cfields->get_fields_at(BS_UF_LOC_POSTS);
		}
		
		return $this->_add_fields;
	}
	
	/**
	 * @return array all keywords to highlight
	 */
	public function get_highlight_keywords()
	{
		return $this->_keywords;
	}
	
	/**
	 * @return array all attachments of the posts:
	 * 	<code>
	 * 	array(
	 * 		<postid> => array(
	 * 			<attachment1>,
	 * 			...
	 * 		),
	 * 		...
	 * 	)
	 *	</code>
	 */
	public function get_attachments()
	{
		if($this->_attachments === null)
			$this->_load_attachments();
		
		return $this->_attachments;
	}
	
	/**
	 * @return PLIB_Pagination the pagination
	 */
	public function get_pagination()
	{
		return $this->_pagination;
	}
	
	/**
	 * @return array All posts in BS_Front_Post_Data objects
	 */
	public function get_posts()
	{
		return $this->_posts;
	}
	
	/**
	 * @return int the number of posts
	 */
	public function get_post_count()
	{
		return count($this->_posts);
	}
	
	/**
	 * Loads all attachments of the loaded posts
	 */
	private function _load_attachments()
	{
		$this->_attachments = array();
		$topic = BS_Front_TopicFactory::get_instance()->get_current_topic();
		if($topic !== null && $topic['attachment_num'] > 0)
		{
			foreach(BS_DAO::get_attachments()->get_by_postids($this->_get_post_ids()) as $adata)
			{
				if(isset($this->_attachments[$adata['post_id']]))
					$this->_attachments[$adata['post_id']][] = $adata;
				else
					$this->_attachments[$adata['post_id']] = array($adata);
			}
		}
	}
	
	/**
	 * Collects all ids of the loaded posts
	 *
	 * @return array all post-ids
	 */
	private function _get_post_ids()
	{
		$ids = array();
		foreach($this->_posts as $p)
			$ids[] = $p->get_field('bid');
		return $ids;
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>