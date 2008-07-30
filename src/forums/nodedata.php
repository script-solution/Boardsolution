<?php
/**
 * Contains the forums-nodedata-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.forums
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The data of the forum-nodes
 *
 * @package			Boardsolution
 * @subpackage	src.forums
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Forums_NodeData extends FWS_Tree_NodeData
{
	/**
	 * The data of the forum
	 *
	 * @var array
	 */
	private $_data;
	
	/**
	 * Constructor
	 *
	 * @param array $data the data of the forum
	 */
	public function __construct($data)
	{
		// Note that we don't call the parent-constructor here for performance-issues
		$this->_id = $data['id'];
		$this->_name = $data['forum_name'];
		$this->_parent_id = $data['parent_id'];
		$this->_sort = $data['sortierung'];
		$this->_data = $data;
	}
	
	/**
	 * Builds an array with all attributes of a forum.
	 * 
	 * @return array an associative array with all attributes
	 */
	public function get_attributes()
	{
		$methods = array(
			'description','display_subforums','forum_is_closed','forum_is_intern','forum_type',
			'increase_experience','lastpost_id','posts','threads','lastpost_an_user','lastpost_time',
			'lastpost_topicid','lastpost_topicname','lastpost_topicposts','lastpost_usergroups',
			'lastpost_userid','lastpost_username','id','parent_id','sort'
		);
		$res = array('forum_name' => $this->get_name());
		foreach($methods as $m)
		{
			$method = 'get_'.$m;
			$res[$m] = $this->$method();
		}
		return $res;
	}

	/**
	 * @return string the description of the forum
	 */
	public function get_description()
	{
		return isset($this->_data['description']) ? $this->_data['description'] : '';
	}

	/**
	 * @return boolean wether this forum should directly display the subforums
	 */
	public function get_display_subforums()
	{
		return isset($this->_data['display_subforums']) ? $this->_data['display_subforums'] : true;
	}

	/**
	 * @return boolean wether this forum is closed
	 */
	public function get_forum_is_closed()
	{
		return isset($this->_data['forum_is_closed']) ? $this->_data['forum_is_closed'] : false;
	}

	/**
	 * @return boolean wether the forum is intern
	 */
	public function get_forum_is_intern()
	{
		return isset($this->_data['forum_is_intern']) ? $this->_data['forum_is_intern'] : false;
	}

	/**
	 * @return string the type of the forum (contains_cats,contains_threads)
	 */
	public function get_forum_type()
	{
		return isset($this->_data['forum_type']) ? $this->_data['forum_type'] : 'contains_threads';
	}

	/**
	 * @return boolean wether this forum increases the experience for posts, topics, ...
	 */
	public function get_increase_experience()
	{
		return isset($this->_data['increase_experience']) ? $this->_data['increase_experience'] : true;
	}

	/**
	 * @return int the id of the last post in the forum
	 */
	public function get_lastpost_id()
	{
		return isset($this->_data['lastpost_id']) ? $this->_data['lastpost_id'] : 0;
	}

	/**
	 * @return int the number of posts in this forum (not recursive!)
	 */
	public function get_posts()
	{
		return isset($this->_data['posts']) ? $this->_data['posts'] : 0;
	}

	/**
	 * @return int the number of topics in this forum (not recursive!)
	 */
	public function get_threads()
	{
		return isset($this->_data['threads']) ? $this->_data['threads'] : 0;
	}

	/**
	 * @return string the guest-name of the last post in this forum
	 */
	public function get_lastpost_an_user()
	{
		return isset($this->_data['lastpost_an_user']) ? $this->_data['lastpost_an_user'] : null;
	}

	/**
	 * @return int the time of the last post in this forum
	 */
	public function get_lastpost_time()
	{
		return isset($this->_data['lastpost_time']) ? $this->_data['lastpost_time'] : 0;
	}

	/**
	 * @return int the topic-id of the last post in this forum
	 */
	public function get_lastpost_topicid()
	{
		return isset($this->_data['lastpost_topicid']) ? $this->_data['lastpost_topicid'] : 0;
	}

	/**
	 * @return string the topic-name of the last post in this forum
	 */
	public function get_lastpost_topicname()
	{
		return isset($this->_data['lastpost_topicname']) ? $this->_data['lastpost_topicname'] : '';
	}

	/**
	 * @return int the number of posts in the topic of the last post in this forum
	 */
	public function get_lastpost_topicposts()
	{
		return isset($this->_data['lastpost_topicposts']) ? $this->_data['lastpost_topicposts'] : 0;
	}

	/**
	 * @return string all user-groups of the author of the last post in this forum
	 */
	public function get_lastpost_usergroups()
	{
		return isset($this->_data['lastpost_usergroups']) ? $this->_data['lastpost_usergroups'] : '';
	}

	/**
	 * @return int the user-id of the author of the last post in this forum
	 */
	public function get_lastpost_userid()
	{
		return isset($this->_data['lastpost_userid']) ? $this->_data['lastpost_userid'] : 0;
	}

	/**
	 * @return string the name of the author of the last post in this forum
	 */
	public function get_lastpost_username()
	{
		return isset($this->_data['lastpost_username']) ? $this->_data['lastpost_username'] : '';
	}
	
	protected function get_print_vars()
	{
		return array_merge(parent::get_print_vars(),get_object_vars($this));
	}
}
?>