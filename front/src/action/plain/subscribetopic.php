<?php
/**
 * Contains the subscribe-topic-plain-action-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The plain-action to subscribe to a topic
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Plain_SubscribeTopic extends BS_Front_Action_Plain
{
	/**
	 * Returns the default instance (variables read from POST and the current user) of this
	 * class.
	 *
	 * @param int $topic_id the topic-id
	 * @param boolean $exists wether the topic already exists
	 * @return BS_Front_Action_Plain_SubscribeTopic the subscribe-topic-object
	 */
	public static function get_default($topic_id,$exists = true)
	{
		$user = FWS_Props::get()->user();
		
		return new BS_Front_Action_Plain_SubscribeTopic($topic_id,$user->get_user_id(),$exists);
	}
	
	/**
	 * The id of the user
	 *
	 * @var int
	 */
	private $_user_id;
	
	/**
	 * The id of the topic
	 *
	 * @var int
	 */
	private $_topic_id;
	
	/**
	 * Wether the topic already exists
	 *
	 * @var boolean
	 */
	private $_exists;
	
	/**
	 * The name of the topic
	 *
	 * @var string
	 */
	private $_name = null;
	
	/**
	 * Constructor
	 *
	 * @param int $topic_id the topic-id
	 * @param int $user_id the user-id
	 * @param boolean $exists wether the topic already exists
	 */
	public function __construct($topic_id,$user_id,$exists = true)
	{
		$this->_topic_id = (int)$topic_id;
		$this->_user_id = (int)$user_id;
		$this->_exists = (bool)$exists;
	}
	
	/**
	 * @return string the name of the topic (available after check_data() and if the topic exists)
	 */
	public function get_topic_name()
	{
		return $this->_name;
	}
	
	public function check_data()
	{
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();

		// check if the user is allowed to subscribe this topic
		if($cfg['enable_email_notification'] == 1 && $this->_user_id > 0)
		{
			// has the user already subscribed this topic?
			if($this->_exists)
			{
				if(BS_DAO::get_subscr()->has_subscribed_topic($this->_user_id,$this->_topic_id))
					return 'already_subscribed_topic';
			
				// check if the topic exists
				$data = BS_DAO::get_topics()->get_by_id($this->_topic_id);
				if($data === false)
					return 'A topic in the selected forum with id "'.$this->_topic_id.'" doesn\'t exist';
			
				// the topic cannot be a shadow topic
				if($data['moved_tid'] > 0)
					return 'You can\'t subscribe to shadow topics';
			
				$this->_name = $data['name'];
			}
			
			if($cfg['max_topic_subscriptions'] > 0)
			{
				$subscriptions = BS_DAO::get_subscr()->get_subscr_topics_count($this->_user_id);
				if($subscriptions >= $cfg['max_topic_subscriptions'])
				{
					return sprintf(
						$locale->lang('error_max_topic_subscriptions'),
						$cfg['max_topic_subscriptions']
					);
				}
			}
		}
		
		return parent::check_data();
	}
	
	public function perform_action()
	{
		$db = FWS_Props::get()->db();
		$cfg = FWS_Props::get()->cfg();

		parent::perform_action();
		
		$db->start_transaction();
		
		// does the user want to subscribe the topic?
		if($cfg['enable_email_notification'] == 1 && $this->_user_id > 0)
			BS_DAO::get_subscr()->subscribe_topic($this->_topic_id,$this->_user_id);
		
		$db->commit_transaction();
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>