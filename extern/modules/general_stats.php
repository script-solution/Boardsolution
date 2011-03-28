<?php
/**
 * Contains the general-stats-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	extern.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Contains the general statistics of the board. That means the number of forums, topics, the last
 * post and so on.
 * 
 * @package			Boardsolution
 * @subpackage	extern.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_API_Module_general_stats extends BS_API_Module
{
	/**
	 * the number of registered user
	 *
	 * @var integer
	 */
	public $registered_user = -1;
	
	/**
	 * the total number of logins
	 *
	 * @var integer
	 */
	public $logins = -1;
	
	/**
	 * the number of logins today
	 *
	 * @var integer
	 */
	public $logins_today = -1;
	
	/**
	 * the number of logins yesterday
	 *
	 * @var integer
	 */
	public $logins_yesterday = -1;
	
	/**
	 * the maximum number of user who were online at the same time
	 *
	 * @var integer
	 */
	public $max_user_online = -1;
	
	/**
	 * the total number of forums
	 *
	 * @var integer
	 */
	public $forum_count = -1;
	
	/**
	 * the total number of topics
	 *
	 * @var integer
	 */
	public $topic_count = -1;
	
	/**
	 * the total number of posts
	 *
	 * @var integer
	 */
	public $post_count = -1;
	
	/**
	 * the number of posts today
	 *
	 * @var integer
	 */
	public $posts_today = -1;
	
	/**
	 * the number of posts yesterday
	 *
	 * @var integer
	 */
	public $posts_yesterday = -1;
	
	/**
	 * the timestamp of the last-post
	 *
	 * @var integer
	 */
	public $last_post_time = -1;
	
	/**
	 * the timestamp of the last login
	 *
	 * @var integer
	 */
	public $last_login_time = -1;
	
	/**
	 * The name of the newest member
	 *
	 * @var string
	 */
	public $newest_member_name = '';
	
	/**
	 * The id of the newest member
	 *
	 * @var integer
	 */
	public $newest_member_id = 0;
	
	public function run()
	{
		$functions = FWS_Props::get()->functions();
		$cache = FWS_Props::get()->cache();

		$stats = $functions->get_stats();
		$stats_data = $cache->get_cache('stats')->current();
		
		$this->registered_user = $stats['total_users'];
		$this->logins = $stats['logins_total'];
		$this->logins_today = $stats['logins_today'];
		$this->logins_yesterday = $stats['logins_yesterday'];
		$this->max_user_online = $stats['max_online'];
		$this->forum_count = $stats['total_forums'];
		$this->topic_count = $stats['total_topics'];
		$this->post_count = $stats['posts_total'];
		$this->posts_today = $stats['posts_today'];
		$this->posts_yesterday = $stats['posts_yesterday'];
		$this->last_post_time = $stats_data['posts_last'];
		$this->last_login_time = $stats_data['logins_last'];
		
		$nm = BS_DAO::get_profile()->get_newest_user();
		$this->newest_member_id = $nm['id'];
		$this->newest_member_name = $nm['user_name'];
	}
}
?>