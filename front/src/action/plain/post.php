<?php
/**
 * Contains the plain-post-action-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The plain-action to create a post (not necessarily a reply!)
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Plain_Post extends BS_Front_Action_Plain
{
	/**
	 * Returns the default instance (variables read from POST and the current user) of this
	 * class.
	 *
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @param boolean $add_topic wether a topic will be added
	 * @return BS_Front_Action_Plain_Post the post-object
	 */
	public static function get_default($fid,$tid = 1,$add_topic = true)
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		
		$post_text = $input->get_var('text','post',FWS_Input::STRING);
		$use_bbcode = $input->isset_var('use_bbcode','post') ? 1 : 0;
		$use_smileys = $input->isset_var('use_smileys','post') ? 1 : 0;
		
		$post = new BS_Front_Action_Plain_Post(
			$fid,$tid,$user->get_user_id(),$user->get_user_ip(),$post_text,
			$use_bbcode,$use_smileys,$add_topic
		);
	
		// support guest-posts
		if(!$user->is_loggedin())
		{
			$user_name = $input->get_var('user_name','post',FWS_Input::STRING);
			$user_email = $input->get_var('email_adr','post',FWS_Input::STRING);
			$post->set_guest($user_name,$user_email);
		}
		
		return $post;
	}
	
	/**
	 * The forum for the topic
	 *
	 * @var int
	 */
	private $_fid;
	
	/**
	 * The id of the topic
	 *
	 * @var int
	 */
	private $_tid;
	
	/**
	 * The created post-id
	 *
	 * @var int
	 */
	private $_post_id = null;
	
	/**
	 * The converted text
	 *
	 * @var string
	 */
	private $_text = null;
	
	/**
	 * The text of the post
	 *
	 * @var string
	 */
	private $_post_text;
	
	/**
	 * Wether posts should be allowed
	 *
	 * @var boolean
	 */
	private $_use_bbcode;
	
	/**
	 * Wether the topic should be important
	 *
	 * @var boolean
	 */
	private $_use_smileys;
	
	/**
	 * Wether a topic has been added
	 *
	 * @var boolean
	 */
	private $_add_topic;
	
	/**
	 * The user-ip to use
	 *
	 * @var string
	 */
	private $_user_ip;
	
	/**
	 * The user-id to use
	 *
	 * @var int
	 */
	private $_user_id;
	
	/**
	 * The name of the user
	 *
	 * @var string
	 */
	private $_user_name = null;
	
	/**
	 * The name of the guest (NULL for users)
	 *
	 * @var string
	 */
	private $_guest_name = null;
	
	/**
	 * The email of the guest (NULL for users)
	 *
	 * @var string
	 */
	private $_guest_email = null;
	
	/**
	 * Constructor
	 *
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id (may be specified later if not known yet)
	 * @param int $user_id the id of the user
	 * @param string $user_ip the ip of the user
	 * @param string $post_text the text to post
	 * @param boolean $use_bbcode use bbcode?
	 * @param boolean $use_smileys use smileys?
	 * @param boolean $add_topic has a topic been created?
	 */
	public function __construct($fid,$tid,$user_id,$user_ip,$post_text,$use_bbcode,
		$use_smileys,$add_topic = true)
	{
		parent::__construct();
		
		if(!FWS_Helper::is_integer($user_id) || $user_id < 0)
			FWS_Helper::def_error('intge0','user_id',$user_id);
		
		$this->_fid = (int)$fid;
		$this->_tid = (int)$tid;
		$this->_user_id = (int)$user_id;
		$this->_user_ip = (string)$user_ip;
		$this->_post_text = (string)$post_text;
		$this->_use_bbcode = (bool)$use_bbcode;
		$this->_use_smileys = (bool)$use_smileys;
		$this->_add_topic = (bool)$add_topic;
	}
	
	/**
	 * @return int the user-id (0 = guest)
	 */
	public function get_user_id()
	{
		return $this->_user_id;
	}
	
	/**
	 * @return string the guest-name (null for users)
	 */
	public function get_guest_name()
	{
		return $this->_guest_name;
	}
	
	/**
	 * @return string the guest-email (null for users)
	 */
	public function get_guest_email()
	{
		return $this->_guest_email;
	}
	
	/**
	 * @return int the forum-id
	 */
	public function get_forum_id()
	{
		return $this->_fid;
	}
	
	/**
	 * @return int the id of the created post (available after the call of perform_action())
	 */
	public function get_post_id()
	{
		return $this->_post_id;
	}
	
	/**
	 * Sets the topic-id that should be used
	 *
	 * @param int $tid the new value
	 */
	public function set_topic_id($tid)
	{
		$this->_tid = $tid;
	}
	
	/**
	 * Sets that a guest is the creator
	 *
	 * @param string $guest_name the name of the guest
	 * @param string $guest_email the email of the guest (optional)
	 */
	public function set_guest($guest_name,$guest_email = null)
	{
		$this->_user_id = 0;
		$this->_guest_name = (string)$guest_name;
		$this->_guest_email = (string)$guest_email;
	}
	
	public function check_data()
	{
		$user = FWS_Props::get()->user();
		$forums = FWS_Props::get()->forums();

		// are all parameters valid?
		if($this->_fid == null || $this->_tid == null)
			return 'The forum-id or topic-id is invalid';
		
		// check the user-id if it is not the current one and no guest
		if($user->get_user_id() != $this->_user_id && $this->_user_id > 0)
		{
			$data = BS_DAO::get_user()->get_user_by_id($this->_user_id);
			if($data === false)
				return 'A user with id "'.$this->_user_id.'" does not exist';
			
			$this->_user_name = $data['user_name'];
		}
		else if($user->get_user_id() == $this->_user_id)
			$this->_user_name = $user->get_user_name();
		
		// does the forum exist?
		$forum_data = $forums->get_node_data($this->_fid);
		if($forum_data === null || $forum_data->get_forum_type() != 'contains_threads')
			return 'The forum with id "'.$this->_fid.'" doesn\'t exist or contains no topics';
		
		// does the topic exist
		if(!$this->_add_topic)
		{
			$topic_data = BS_DAO::get_topics()->get_by_id($this->_tid);
			if($topic_data === false)
				return 'The topic with id "'.$this->_tid.'" does not exist';
		}
		
		// check guest-data
		if($this->_user_id == 0)
		{
			if(!BS_UserUtils::get_instance()->check_username($this->_guest_name))
				return 'invalid_username';
			
			$this->_guest_email = trim($this->_guest_email);
			if($this->_guest_email != '' && !FWS_StringHelper::is_valid_email($this->_guest_email))
				return 'invalid_email';
		}
		
		// convert and check text
		$error = BS_PostingUtils::get_instance()->prepare_message_for_db(
			$this->_text,$this->_post_text,'posts',$this->_use_smileys,$this->_use_bbcode
		);
		if($error != '')
			return $error;
		
		return parent::check_data();
	}
	
	public function perform_action()
	{
		$db = FWS_Props::get()->db();
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		$forums = FWS_Props::get()->forums();
		$cache = FWS_Props::get()->cache();

		parent::perform_action();
		
		$db->start_transaction();
		
		// create the post
		$time = time();
		$fields = array(
			'rubrikid' => $this->_fid,
			'threadid' => $this->_tid,
			'post_user' => $this->_user_id,
			'post_an_user' => $this->_guest_name,
			'post_an_mail' => $this->_guest_email,
			'post_time' => $time,
			'text' => $this->_text,
			'text_posted' => $this->_post_text,
			'use_bbcode' => $this->_use_bbcode,
			'use_smileys' => $this->_use_smileys,
			'ip_adresse' => $this->_user_ip
		);
		$postid = BS_DAO::get_posts()->create($fields);
	
		// send emails to all you subscribed the topic
		if($cfg['enable_email_notification'] == 1)
		{
			$userlist = BS_DAO::get_subscr()->get_subscribed_users($this->_fid,$this->_tid,$this->_user_id);
			if(count($userlist) > 0)
			{
				$name = $this->_user_name !== null ? $this->_user_name : $this->_guest_name;
				$einfo = BS_EmailFactory::get_instance()->get_new_post_texts(
					$this->_fid,$this->_tid,$postid,$this->_post_text,$name
				);
	
				$email = $functions->get_mailer('',$einfo['subject'],'');
				foreach($userlist as $data)
				{
					if($data['emails_include_post'] == 1)
						$email->set_message($einfo['text_post']);
					else
						$email->set_message($einfo['text_def']);
	
					$email->set_recipient($data['user_email']);
					$email->send_mail();
				}
			}
	
			// add the post to the unsent-posts if the user wants to receive the email later
			$uids = array();
			foreach(BS_DAO::get_profile()->get_users_with_delayed_notify($this->_user_id) as $user)
				$uids[] = $user['id'];
			
			if(count($uids) > 0)
				BS_DAO::get_unsentposts()->create($postid,$uids);
		}
	
		// change forum-attributes
		$fields = array(
			'posts' => array('posts + 1'),
			'lastpost_id' => $postid
		);
		if($this->_add_topic)
			$fields['threads'] = array('threads + 1');
		
		BS_DAO::get_forums()->update_by_id($this->_fid,$fields);
	
		// change topic-attributes
		// this is only required (and possible ;)), if the topic exists
		if(!$this->_add_topic)
		{
			BS_DAO::get_topics()->update($this->_tid,array(
				'lastpost_user' => $this->_user_id,
				'lastpost_id' => $postid,
				'lastpost_time' => $time,
			 	'lastpost_an_user' => $this->_guest_name,
				'posts' => array('posts + 1')
			));
		}
	
		// update posts and points in profile, if the user is loggedin
		if($this->_user_id > 0)
		{
			$fields = array('posts' => array('posts + 1'));
			$forum_data = $forums->get_node_data($this->_fid);
			if($forum_data->get_increase_experience())
			{
				$number = $this->_add_topic ? BS_EXPERIENCE_FOR_POST + BS_EXPERIENCE_FOR_TOPIC
					: BS_EXPERIENCE_FOR_POST;
				$fields['exppoints'] = array('exppoints + '.$number);
			}
	
			BS_DAO::get_profile()->update_user_by_id($fields,$this->_user_id);
		}
	
		// store that a post has been done
		$cache->get_cache('stats')->set_element_field(0,'posts_last',time());
		$cache->store('stats');
	
		$this->_post_id = $postid;
		
		$db->commit_transaction();
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>