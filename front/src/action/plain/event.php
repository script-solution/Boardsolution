<?php
/**
 * Contains the plain-event-action-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The plain-action to create a event (without the topic)
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Plain_Event extends BS_Front_Action_Plain
{
	/**
	 * Returns the default instance (variables read from POST and the current user) of this
	 * class. Assumes that set_topic_id() will be called!
	 *
	 * @return BS_Front_Action_Plain_Event the plain-action-object
	 */
	public static function get_default()
	{
		$input = PLIB_Props::get()->input();
		$user = PLIB_Props::get()->user();
		
		// grab variables from POST
		$topic_name = $input->get_var('topic_name','post',PLIB_Input::STRING);
		$location = $input->get_var('location','post',PLIB_Input::STRING);
		$open_end = $input->get_var('open_end','post',PLIB_Input::STRING);
		$max_announcements = $input->get_var('max_announcements','post',PLIB_Input::INTEGER);
		$timeout_type = $input->correct_var(
			'timeout_type','post',PLIB_Input::STRING,array('begin','self'),'begin'
		);
		$enable_announcements = $input->get_var('enable_announcements','post',PLIB_Input::INT_BOOL);
		$description = $input->get_var('text','post',PLIB_Input::STRING);

		$form = new BS_HTML_Formular(true,true);
		
		// build begin, end and timeout
		$begin = $form->get_date_chooser_timestamp('b_');
		if($open_end == 1)
			$end = 0;
		else
			$end = $form->get_date_chooser_timestamp('e_');

		if($timeout_type == 'begin')
			$timeout = 0;
		else
			$timeout = $form->get_date_chooser_timestamp('c_');

		// build announcements
		if(!$enable_announcements)
			$max_announcements = -1;
		else
			$max_announcements = ($max_announcements < 0) ? 0 : $max_announcements;
		
		// return object
		return new BS_Front_Action_Plain_Event(
			0,$user->get_user_id(),$topic_name,$location,$begin,$description,$end,$timeout,
			$max_announcements
		);
	}
	
	/**
	 * The id of the topic
	 *
	 * @var int
	 */
	private $_tid = null;
	
	/**
	 * The user-id to use
	 *
	 * @var int
	 */
	private $_user_id;
	
	/**
	 * The name of the event
	 *
	 * @var string
	 */
	private $_name;
	
	/**
	 * The location of the event
	 *
	 * @var string
	 */
	private $_location;
	
	/**
	 * The description of the event
	 *
	 * @var string
	 */
	private $_description;
	
	/**
	 * The posted description
	 *
	 * @var string
	 */
	private $_description_posted;
	
	/**
	 * The timestamp for the event-begin
	 *
	 * @var int
	 */
	private $_begin;
	
	/**
	 * The timestamp for the event-end
	 *
	 * @var int
	 */
	private $_end;
	
	/**
	 * The timestamp for the announce-timeout
	 *
	 * @var int
	 */
	private $_timeout;
	
	/**
	 * The maximum announcements: -1 = no announcements, 0 = unlimited
	 *
	 * @var int
	 */
	private $_max_announcements;
	
	/**
	 * Constructor
	 *
	 * @param int $topic_id The id of the topic
	 * @param int $user_id The user-id to use
	 * @param string $name The name of the event
	 * @param string $location The location of the event
	 * @param int $begin The timestamp for the event-begin
	 * @param string $description The description of the event
	 * @param int $end The timestamp for the event-end (0 = open end)
	 * @param int $timeout The timestamp for the announce-timeout (0 = event-begin)
	 * @param int $max_announcements The maximum announcements: -1 = no announcements, 0 = unlimited
	 */
	public function __construct($topic_id,$user_id,$name,$location,$begin,$description = '',$end = 0,
		$timeout = 0,$max_announcements = 0)
	{
		parent::__construct();
		
		$this->_tid = (int)$topic_id;
		$this->_user_id = (int)$user_id;
		$this->_name = (string)$name;
		$this->_location = (string)$location;
		$this->_description_posted = (string)$description;
		$this->_begin = (int)$begin;
		$this->_end = (int)$end;
		$this->_timeout = (int)$timeout;
		$this->_max_announcements = (int)$max_announcements;
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
	
	public function check_data()
	{
		$user = PLIB_Props::get()->user();

		if($this->_tid === null || $this->_tid < 0)
			return 'Invalid topic-id "'.$this->_tid.'"';
		
		// is the topic or the location empty?
		if(trim($this->_name) == '' || trim($this->_location) == '')
			return 'terminleer';
		
		// check the user-id if it is not the current one and no guest
		if($user->get_user_id() != $this->_user_id && $this->_user_id > 0)
		{
			$data = BS_DAO::get_user()->get_user_by_id($this->_user_id);
			if($data === false)
				return 'A user with id "'.$this->_user_id.'" does not exist';
		}
		
		// check timestamps
		if($this->_begin < 0 || $this->_end < 0 || $this->_timeout < 0)
			return 'Invalid event-begin, -end or -timeout';

		if($this->_end > 0 && $this->_end <= $this->_begin)
			return 'endekbeginn';
		
		if($this->_max_announcements < -1)
			return 'Invalid number of max-announcements';
		
		// convert and check text
		if($this->_tid == 0)
		{
			$error = BS_PostingUtils::get_instance()->prepare_message_for_db(
				$this->_description,$this->_description_posted,'desc',true,true
			);
			if($error != '')
				return $error;
		}
		else
		{
			$this->_description = '';
			$this->_description_posted = '';
		}

		return parent::check_data();
	}
	
	public function perform_action()
	{
		$db = PLIB_Props::get()->db();

		parent::perform_action();
		
		$db->start_transaction();
		
		$fields = array(
			'tid' => $this->_tid,
			'user_id' => $this->_user_id,
			'event_title' => $this->_name,
			'event_begin' => $this->_begin,
			'event_end' => $this->_end,
			'max_announcements' => $this->_max_announcements,
			'description' => $this->_description,
			'description_posted' => $this->_description_posted,
			'event_location' => $this->_location,
			'timeout' => $this->_timeout
		);
		BS_DAO::get_events()->create($fields);
		
		$db->commit_transaction();
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>