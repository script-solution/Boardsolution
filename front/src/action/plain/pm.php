<?php
/**
 * Contains the plain-pm-action-class
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
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		
		$receiver = $input->get_var('receiver','post');
		if(!is_array($receiver))
			$receiver = array();
		// read additional receivers from the textfield if the user hasn't transferred them
		$new_receiver = $input->get_var('new_receiver','post',FWS_Input::STRING);
		if($new_receiver)
		{
			$other = FWS_Array_Utils::advanced_explode(',',$new_receiver);
			$receiver = array_merge($receiver,FWS_Array_Utils::trim($other));
		}
		$title = $input->get_var('pm_title','post',FWS_Input::STRING);
		$post_text = $input->get_var('text','post',FWS_Input::STRING);
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
	private $_receiver_email = array();
	
	/**
	 * The number of PMs in the inboxes of the receivers
	 *
	 * @var array
	 */
	private $_inbox_counts = array();
	
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
		
		if(!FWS_Helper::is_integer($user_id) || $user_id < 0)
			FWS_Helper::def_error('intge0','user_id',$user_id);
		if($att !== null && !($att instanceof BS_Front_Action_Plain_Attachments))
			FWS_Helper::def_error('instance','att','BS_Front_Action_Plain_Attachments',$att);
		
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
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();

		// pms disabled?
		if($cfg['enable_pms'] == 0)
			return 'PMs are disabled!';
		
		//Check for sender "Boardsolution"
		if($this->_user_id != 0)
		{
			// check the user-id if it is not the current one
			if($user->get_user_id() != $this->_user_id)
			{
				$data = BS_DAO::get_user()->get_user_by_id($this->_user_id);
				if($data === false)
					return 'A user with id "'.$this->_user_id.'" does not exist';
			}
	
			// check the total number of pms
			$outbox_num = BS_DAO::get_pms()->get_count_in_folder('outbox',$this->_user_id);
			if($cfg['pm_max_outbox'] > 0 && $outbox_num >= $cfg['pm_max_outbox'])
				return 'maxoutbox';
		}
		
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
			if($cfg['pm_max_inbox'] > 0 && $r_inbox >= $cfg['pm_max_inbox'])
				continue;
			
			// pms disabled?
			if($data['allow_pms'] == 0)
				continue;

			// is the user banned and not Boardsolution?
			if($this->_user_id != 0)
				if(BS_DAO::get_userbans()->has_baned($data['id'],$this->_user_id))
					continue;

			// we don't want to send multiple PMs to one user
			if(in_array($data['id'],$this->_receiver_ids))
				continue;

			$this->_inbox_counts[] = $r_inbox;
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
		$error = BS_PostingUtils::prepare_message_for_db(
			$this->_text,$this->_post_text,'posts',true,true
		);
		if($error != '')
			return $error;
		
		return parent::check_data();
	}
	
	public function perform_action()
	{
		$db = FWS_Props::get()->db();
		$cfg = FWS_Props::get()->cfg();
		$msgs = FWS_Props::get()->msgs();

		parent::perform_action();
		
		$db->start_transaction();
		
		for($i = 0;$i < count($this->_receiver_ids);$i++)
		{
			$pmid = $this->_insert_pm($this->_receiver_ids[$i],'inbox');
			$this->_insert_attachments($this->_receiver_ids[$i],$pmid);
			
			$pmid = $this->_insert_pm($this->_receiver_ids[$i],'outbox');
			$this->_insert_attachments($this->_user_id,$pmid);
			
			// do we have to send the "pm-inbox-full-email"?
			if($this->_inbox_counts[$i] == 0 || $cfg['pm_max_inbox'] == 0)
				$percent = 0;
			else
				$percent = 100 / ($cfg['pm_max_inbox'] / $this->_inbox_counts[$i]);
			if($percent >= BS_PM_INBOX_FULL_EMAIL_SINCE)
			{
				$mail = BS_EmailFactory::get_instance()->get_pm_inbox_full_mail(
					$this->_inbox_counts[$i],$this->_receiver_email[$this->_receiver_ids[$i]]
				);
				if(!$mail->send_mail() && $mail->get_error_message())
					$msgs->add_error($mail->get_error_message());
			}

			// do we have to send an email?
			if(isset($this->_receiver_email[$this->_receiver_ids[$i]]))
			{
				$email = BS_EmailFactory::get_instance()->get_new_pm_mail(
					$this->_receiver_email[$this->_receiver_ids[$i]]
				);
				if(!$email->send_mail() && $email->get_error_message())
					$msgs->add_error($email->get_error_message());
			}
		}
		
		$db->commit_transaction();
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
	 * @param int $receiver_id the user-id of the receiver
	 * @param int $pm_id the pm-id
	 */
	private function _insert_attachments($receiver_id,$pm_id)
	{
		// store attachments for this PM
		if($this->_att !== null && $this->_att->attachments_set())
		{
			$this->_att->set_target(0,0,$pm_id,$receiver_id);
			$this->_att->perform_action();
			$count = $this->_att->get_count();
			if($count > 0)
				BS_DAO::get_pms()->set_attachment_count($pm_id,$count);
		}
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
