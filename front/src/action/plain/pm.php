<?php
/**
 * Contains the plain-pm-action-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The plain-action to create a PM
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Plain_PM extends BS_Front_Action_Plain
{
	/**
	 * Returns the default instance (variables read from POST and the current user) of this
	 * class.
	 *
	 * @return BS_Front_Action_Plain_PM the PM-object
	 */
	public static function get_default()
	{
		$input = PLIB_Object::get_prop('input');
		$user = PLIB_Object::get_prop('user');
		
		$receiver = $input->get_var('receiver','post');
		$title = $input->get_var('pm_title','post',PLIB_Input::STRING);
		$post_text = $input->get_var('text','post',PLIB_Input::STRING);
		$att = BS_Front_Action_Plain_Attachments::get_default();
		
		return new BS_Front_Action_Plain_PM($user->get_user_id(),$receiver,$title,$post_text,$att);
	}
	
	/**
	 * The user-id to use
	 *
	 * @var int
	 */
	private $_user_id;
	
	/**
	 * An array with all receivers
	 *
	 * @var array
	 */
	private $_receiver;
	
	/**
	 * The PM-title
	 *
	 * @var string
	 */
	private $_title;
	
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
	 * The plain-attachments-action
	 *
	 * @var BS_Front_Action_Plain_Attachments
	 */
	private $_att;
	
	/**
	 * The ids of the receiver
	 *
	 * @var array
	 */
	private $_receiver_ids = array();
	
	/**
	 * All email-addresses of the receiver
	 *
	 * @var array
	 */
	private $_receiver_mails = array();
	
	/**
	 * Constructor
	 *
	 * @param int $user_id the id of the sender
	 * @param array $receiver an array with all user-names that should receive the PM
	 * @param string $title the PM-title
	 * @param string $post_text the text to post
	 * @param BS_Front_Action_Plain_Attachments $att the plain-attachments-action that should be
	 * 	performed for all PMs (the PM-id will be set here)
	 */
	public function __construct($user_id,$receiver,$title,$post_text,$att = null)
	{
		parent::__construct();
		
		if(!PLIB_Helper::is_integer($user_id) || $user_id < 0)
			PLIB_Helper::def_error('intge0','user_id',$user_id);
		if($att !== null && !($att instanceof BS_Front_Action_Plain_Attachments))
			PLIB_Helper::def_error('instance','att','BS_Front_Action_Plain_Attachments',$att);
		
		$this->_user_id = (int)$user_id;
		$this->_title = (string)$title;
		$this->_receiver = $receiver;
		$this->_post_text = (string)$post_text;
		$this->_att = $att;
	}
	
	/**
	 * @return array all receiver-names. After check_data() this contains the valid receivers
	 */
	public function get_receiver_names()
	{
		return $this->_receiver;
	}
	
	public function check_data()
	{
		// pms disabled?
		if($this->cfg['enable_pms'] == 0)
			return 'PMs are disabled!';
		
		// check the user-id if it is not the current one
		if($this->user->get_user_id() != $this->_user_id)
		{
			$data = BS_DAO::get_user()->get_user_by_id($this->_user_id);
			if($data === false)
				return 'A user with id "'.$this->_user_id.'" does not exist';
		}

		// check the total number of pms
		$outbox_num = BS_DAO::get_pms()->get_count_in_folder('outbox',$this->_user_id);
		if($this->cfg['pm_max_outbox'] > 0 && $outbox_num > $this->cfg['pm_max_outbox'])
			return 'maxoutbox';
		
		// check attachments if available
		if($this->_att !== null && $this->_att->attachments_set())
		{
			$res = $this->_att->check_data();
			if($res != '')
				return $res;
		}

		// check the receiver
		if($this->_receiver == null || !is_array($this->_receiver))
			return 'pm_no_receiver';

		// limit the number of receivers
		if(count($this->_receiver) > BS_MAX_PM_RECEIVER)
			return 'pm_too_many_receiver';

		$this->_receiver_email = array();
		$this->_receiver_ids = array();
		$receiver = $this->_receiver;
		$this->_receiver = array();
		foreach(BS_DAO::get_profile()->get_users_by_names($receiver) as $i => $data)
		{
			// inbox full?
			$r_inbox = BS_DAO::get_pms()->get_count_in_folder('inbox',$data['id']);
			if($this->cfg['pm_max_inbox'] > 0 && $r_inbox > $this->cfg['pm_max_inbox'])
				continue;
			
			// pms disabled?
			if($data['allow_pms'] == 0)
				continue;

			// is the user banned?
			if(BS_DAO::get_userbans()->has_baned($data['id'],$this->_user_id))
				continue;

			// we don't want to send multiple PMs to one user
			if(in_array($data['id'],$this->_receiver_ids))
				continue;

			$this->_receiver[] = $receiver[$i];
			$this->_receiver_ids[] = $data['id'];
			if($data['enable_pm_email'] == 1)
				$this->_receiver_email[$data['id']] = $data['user_email'];
		}

		if(count($this->_receiver_ids) == 0)
			return 'pm_no_receiver';

		if(trim($this->_title) == '')
			return 'pmtitelleer';

		$this->_text = '';
		$error = BS_PostingUtils::get_instance()->prepare_message_for_db(
			$this->_text,$this->_post_text,'posts',1,1
		);
		if($error != '')
			return $error;
		
		return parent::check_data();
	}
	
	public function perform_action()
	{
		parent::perform_action();
		
		$this->db->start_transaction();
		
		for($i = 0;$i < count($this->_receiver_ids);$i++)
		{
			$pmid = $this->_insert_pm($this->_receiver_ids[$i],'inbox');
			$this->_insert_attachments($pmid);
			
			$pmid = $this->_insert_pm($this->_receiver_ids[$i],'outbox');
			$this->_insert_attachments($pmid);

			// do we have to send an email?
			if(isset($this->_receiver_email[$this->_receiver_ids[$i]]))
			{
				$email = BS_EmailFactory::get_instance()->get_new_pm_mail(
					$this->_receiver_email[$this->_receiver_ids[$i]]
				);
				if(!$email->send_mail())
					$this->msgs->add_error($email->get_error_message());
			}
		}
		
		$this->db->commit_transaction();
	}
	
	/**
	 * Inserts the PM of the given type
	 *
	 * @param int $receiver_id the receiver-id
	 * @param string $type inbox or outbox
	 * @return int the used id
	 */
	private function _insert_pm($receiver_id,$type)
	{
		$fields = array(
			'receiver_id' => $receiver_id,
			'sender_id' => $this->_user_id,
			'pm_title' => $this->_title,
			'pm_text' => $this->_text,
			'pm_text_posted' => $this->_post_text,
			'pm_type' => $type,
			'pm_date' => time()
		);
		return BS_DAO::get_pms()->create($fields);
	}
	
	/**
	 * Inserts the attachments for the given pm-id
	 *
	 * @param int $pm_id the pm-id
	 */
	private function _insert_attachments($pm_id)
	{
		// store attachments for this PM
		if($this->_att !== null && $this->_att->attachments_set())
		{
			$this->_att->set_target(0,0,$pm_id);
			$this->_att->perform_action();
			$count = $this->_att->get_count();
			if($count > 0)
				BS_DAO::get_pms()->set_attachment_count($pm_id,$count);
		}
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>