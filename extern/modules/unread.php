<?php
/**
 * Contains the unread-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	extern.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Contains the unread topics and forums for the current user
 * 
 * @package			Boardsolution
 * @subpackage	extern.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_API_Module_unread extends BS_API_Module
{
	/**
	 * a numeric array with the unread forum-ids
	 *
	 * @var array
	 */
	public $unread_forums = array();
	
	/**
	 * a numeric array with the unread topics of the following form:
	 * <code>
	 * array(
	 * 	'id' => <topicID>,
	 * 	'forum_id' => <forumID>,
	 * 	'post_id' => <idOfTheFirstUnreadPost>
	 * )
	 * </code>
	 *
	 * @var array
	 */
	public $unread_topics = array();
	
	/**
	 * the number of unread pms
	 *
	 * @var integer
	 */
	public $unread_pms = 0;
	
	public function run()
	{
		if($this->user->is_loggedin())
		{
			$this->unread_pms = $this->user->get_profile_val('unread_pms');
			$this->unread_forums = $this->unread->get_unread_forums();
			
			$topics = $this->unread->get_unread_topics();
			if(is_array($topics))
			{
				foreach($topics as $tid => $udata)
				{
					$this->unread_topics[] = array(
						'id' => $tid,
						'forum_id' => $udata[1],
						'post_id' => $udata[0]
					);
				}
			}
		}
	}
}
?>