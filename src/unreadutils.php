<?php
/**
 * Contains the unread-utils-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Contains some utility-methods for the unread-topics / -posts
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_UnreadUtils extends FWS_Singleton
{
	/**
	 * @return BS_UnreadUtils the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Removes all unread-information for the given forums from the unread-table.
	 * Note that this method requires that the forums exist!
	 *
	 * @param array $fids all forums that should be deleted (note that subforums are not included
	 * 	automatically!)
	 */
	public function remove_forums($fids)
	{
		if(!is_array($fids))
			FWS_Helper::def_error('array','fids',$fids);
		
		$this->_remove($fids,'rubrikid');
	}
	
	/**
	 * Removes all unread-information for the given topics from the unread-table.
	 * Note that this method requires that the topics exist!
	 *
	 * @param array $tids all topics that should be deleted
	 */
	public function remove_topics($tids)
	{
		if(!is_array($tids))
			FWS_Helper::def_error('array','tids',$tids);
		
		$this->_remove($tids,'threadid');
	}
	
	/**
	 * The method that retrieves the posts and deletes them from the unread-table.
	 *
	 * @param array $ids the ids
	 * @param string $field the name of the field that should be used for the ids in the posts-table
	 */
	private function _remove($ids,$field)
	{
		// nothing to do?
		if(count($ids) == 0)
			return;
		
		// at first we need to know all post-ids from the topic/forum
		$post_ids = array();
		foreach(BS_DAO::get_unread()->get_all_by_type($field,$ids) as $data)
			$post_ids[] = $data['post_id'];
		
		// nothing found?
		if(count($post_ids) == 0)
			return;
		
		// ok, delete all posts
		BS_DAO::get_unread()->delete_posts($post_ids);
	}
	
	/**
	 * Removes all unread-information for the given posts from the unread-table
	 *
	 * @param array $pids all posts that should be deleted
	 * @param int $tid the topic-id from which the posts are
	 */
	public function remove_posts($pids,$tid)
	{
		if(!is_array($pids))
			FWS_Helper::def_error('array','pids',$pids);
		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		
		// nothing to do?
		if(count($pids) == 0)
			return;
		
		// find the next post-id and replace them
		$rem = array();
		foreach($pids as $pid)
		{
			$next = BS_DAO::get_posts()->get_next_post_id($pid,$tid);
			// is there no next post?
			if($next === false)
			{
				$rem[] = $pid;
				continue;
			}
			
			// otherwise we replace the post-id
			BS_DAO::get_unread()->update_by_post($pid,$next);
		}
		
		// remove all posts that have no next post
		if(count($rem) > 0)
			BS_DAO::get_unread()->delete_posts($rem);
	}
}
?>