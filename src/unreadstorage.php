<?php
/**
 * Contains the unread-storage-interface
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The interface for all unread-storage-implementations. They decide where and how to store
 * the unread data and also the last update timestamp.
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
interface BS_UnreadStorage
{
	/**
	 * Should return all unread news in the following format:
	 * <code>
	 * array(
	 * 	<topicID> => true,
	 * 	...
	 * )
	 * </code>
	 *
	 * @return array the unread news
	 */
	public function get_news();
	
	/**
	 * Should return all unread topics in the following format:
	 * <code>
	 * array(
	 * 	<topicID> => array(<firstPostID>,<forumID>)
	 * )
	 * </code>
	 * 
	 * @return array the unread topics
	 */
	public function get_topics();
	
	/**
	 * Should return all unread forums in the following format:
	 * <code>
	 * array(
	 * 	<forumID> => array(
	 * 		<topicID> => array(
	 * 			<postID1>,...,<postIDn>
	 * 		),
	 * 	)
	 * )
	 * </code>
	 * 
	 * @return array the unread forums
	 */
	public function get_forums();
	
	/**
	 * @return int the timestamp of the last unread update
	 */
	public function get_last_update();
	
	/**
	 * Sets the last unread update to given timestamp
	 *
	 * @param int $time the timestamp
	 */
	public function set_last_update($time);
	
	/**
	 * Adds all given post-ids to the storage
	 *
	 * @param array $ids an array of the form: <code>array(<postID> => <isNews>,...)</code>
	 */
	public function add_post_ids($ids);
	
	/**
	 * Removes the given post-ids from the storage
	 *
	 * @param array $ids the post-ids
	 */
	public function remove_post_ids($ids);
	
	/**
	 * Removes all unread topics, forums and news from the storage
	 */
	public function remove_all();
	
	/**
	 * Removes all unread news from the storage
	 */
	public function remove_all_news();
}
?>