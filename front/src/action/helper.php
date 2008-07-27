<?php
/**
 * Contains the helper class for the BS_Actions-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Some helper-methods for the BS_Actions-class
 * 
 * @package			Boardsolution
 * @subpackage	src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Helper extends PLIB_Singleton
{
	/**
	 * @return BS_Front_Action_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Adjusts the last-post-time if necessary
	 *
	 * @param int $max_post_time the maximum post time of the posts which are deleted
	 */
	public function adjust_last_post_time($max_post_time)
	{
		$cache = PLIB_Props::get()->cache();

		$stats_data = $cache->get_cache('stats')->current();
	
		// refresh lastpost?
		if($max_post_time == $stats_data['posts_last'])
		{
			$lastpost_time = BS_DAO::get_topics()->get_last_post_time();
			$cache->get_cache('stats')->set_element_field(
				0,'posts_last',$lastpost_time
			);
			$cache->store('stats');
		}
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>