<?php
/**
 * Contains the plain-topic-action-class
 * 
 * @package			Boardsolution
 * @subpackage	front.src.action
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
 * The plain-action to create a new topic
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Plain_Topic extends BS_Front_Action_Plain
{
	/**
	 * Returns the default instance (variables read from POST and the current user) of this
	 * class.
	 *
	 * @param BS_Front_Action_Plain_Post $post the plain post-action
	 * @param int $type the type of the topic: 0 = default, -1 = event, positive number = poll-id
	 * @return BS_Front_Action_Plain_Topic the topic-object
	 */
	public static function get_default($post,$type = 0)
	{
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		
		$topic_name = $input->get_var('topic_name','post',FWS_Input::STRING);
		$allow_posts = $input->get_var('allow_posts','post',FWS_Input::INT_BOOL);
		if($auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT) &&
			 $input->get_var('important','post',FWS_Input::INT_BOOL) == 1)
			$important = true;
		else
			$important = false;
		$symbol = $input->get_var('symbol','post',FWS_Input::INTEGER);
		$symbol = ($symbol > BS_NUMBER_OF_TOPIC_ICONS || $symbol < 0) ? 0 : (int)$symbol;
		
		return new BS_Front_Action_Plain_Topic(
			$topic_name,$post,$type,$symbol,$allow_posts,$important
		);
	}
	
	/**
	 * The created topic-id
	 *
	 * @var int
	 */
	private $_tid = null;
	
	/**
	 * The name of the topic
	 *
	 * @var string
	 */
	private $_name;
	
	/**
	 * The type of the topic (0 = default, -1 = event, positive number = poll-id)
	 *
	 * @var int
	 */
	private $_type;
	
	/**
	 * The symbol of the topic
	 *
	 * @var int
	 */
	private $_symbol;
	
	/**
	 * Wether posts should be allowed
	 *
	 * @var boolean
	 */
	private $_allow_posts;
	
	/**
	 * Wether the topic should be important
	 *
	 * @var boolean
	 */
	private $_important;
	
	/**
	 * The plain-post-object
	 *
	 * @var BS_Front_Action_Plain_Post
	 */
	private $_post;
	
	/**
	 * Constructor. Note that the forum- and user-id (including guest-data) will be taken from
	 * the post
	 *
	 * @param string $name the name of the topic
	 * @param BS_Front_Action_Plain_Post $post the plain post-action
	 * @param int $type the type of the topic: 0 = default, -1 = event, positive number = poll-id
	 * @param int $symbol the symbol of the topic
	 * @param boolean $allow_posts do you want to allow posts?
	 * @param boolean $important should the topic be important?
	 * @see set_guest()
	 */
	public function __construct($name,$post,$type = 0,$symbol = 0,$allow_posts = true,
		$important = false)
	{
		parent::__construct();
		
		if(!FWS_Helper::is_integer($type))
			FWS_Helper::def_error('type','numeric',$type);
		if(!($post instanceof BS_Front_Action_Plain_Post))
			FWS_Helper::def_error('instance','post','BS_Front_Action_Plain_Post',$post);
		
		$this->_name = (string)$name;
		$this->_type = (int)$type;
		$this->_post = $post;
		$this->_symbol = $type != 0 ? 0 : (int)$symbol;
		$this->_allow_posts = (bool)$allow_posts;
		$this->_important = (bool)$important;
	}
	
	/**
	 * The created topic-id (available after the call of check_data())
	 *
	 * @return int
	 */
	public function get_topic_id()
	{
		return $this->_tid;
	}
	
	public function check_data()
	{
		// Note that the forum and the user-id will be checked in the plain-post
		
		// topic-name empty?
		if(trim($this->_name) == '')
			return 'threadstartfeldleer';
		
		// symbol valid?
		if($this->_symbol < 0 || $this->_symbol > BS_NUMBER_OF_TOPIC_ICONS)
			return 'Invalid symbol "'.$this->_symbol.'"';
		
		// grab the next auto-increment id from the database
		$this->_tid = BS_DAO::get_topics()->get_next_id();

		// add the post
		$this->_post->set_topic_id($this->_tid);
		$res = $this->_post->check_data();
		if($res != '')
			return $res;
		
		return parent::check_data();
	}
	
	public function perform_action()
	{
		$db = FWS_Props::get()->db();

		parent::perform_action();
		
		$db->start_transaction();
		
		// create post
		$this->_post->perform_action();

		// create topic
		$fields = array(
			'rubrikid' => $this->_post->get_forum_id(),
			'name' => $this->_name,
			'posts' => 0,
			'post_user' => $this->_post->get_user_id(),
			'post_time' => time(),
			'post_an_user' => $this->_post->get_guest_name(),
			'post_an_mail' => $this->_post->get_guest_email(),
			'lastpost_user' => $this->_post->get_user_id(),
			'lastpost_time' => time(),
			'lastpost_an_user' => $this->_post->get_guest_name(),
			'symbol' => $this->_symbol,
			'type' => $this->_type,
			'comallow' => $this->_allow_posts,
			'important' => $this->_important,
			'lastpost_id' => $this->_post->get_post_id()
		);
		BS_DAO::get_topics()->create($fields);
		
		$db->commit_transaction();
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>