<?php
/**
 * Contains the pmcompose-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pmcompose submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_pmcompose extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_SEND_PM => 'sendpm'
		);
	}
	
	public function run()
	{
		$helper = BS_Front_Module_UserProfile_Helper::get_instance();
		if($helper->get_pm_permission() < 1)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}
		
		if($this->input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview('posts',1,1);

		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$pid = $this->input->get_var(BS_URL_PID,'get',PLIB_Input::ID);

		// quote a pm
		if($pid != null)
			$edaten = BS_DAO::get_pms()->get_pm_in_folder($pid,'inbox',$this->user->get_user_id());
		// select the receiver
		else if($id != null)
			$udaten = BS_DAO::get_user()->get_user_by_id($id);

		$show_post_vars = $this->input->isset_var('remove_pm_recv','post') ||
											$this->input->isset_var('add_receiver','post');
		$form = new BS_HTML_Formular(true,true);
		if(!$form->get_condition())
			$form->set_condition($show_post_vars || $this->input->isset_var('quote','post'));

		$receiver = $this->input->get_var('receiver','post');
		if($receiver == null || !is_array($receiver))
			$receiver = array();

		$new_receiver = $this->input->get_var('new_receiver','post',PLIB_Input::STRING);
		if($new_receiver === null)
		{
			if($pid != null && $edaten['user_name'] != '')
				$receiver = array($edaten['user_name']);
			else if($id != null && $udaten['user_name'] != '')
				$receiver = array($udaten['user_name']);
		}

		if($pid != null && $edaten['pm_title'] != '')
			$default_title = 'RE: '.$edaten['pm_title'];
		else
			$default_title = '';

		if($pid != null && $edaten['pm_text_posted'] != '')
		{
			$default_text = BS_PostingUtils::get_instance()->quote_text(
				$edaten['pm_text_posted'],$edaten['user_name']
			);
			$default_text .= "\n";
		}
		else
			$default_text = '';

		// remove a receiver, if the user requests it
		$remove_pm_recv = $this->input->get_var('remove_pm_recv','post');
		if($remove_pm_recv != null && is_array($remove_pm_recv))
		{
			foreach(array_keys($remove_pm_recv) as $user_name)
			{
				if($receiver != null && ($key = array_search($user_name,$receiver)) !== false)
					unset($receiver[$key]);
			}
		}

		$error_msg = $this->_add_new_receiver($receiver,$new_receiver);

		// display an error-message if not all receiver have been added
		if(is_string($error_msg) && $error_msg != 'no_receivers_assigned')
		{
			$this->locale->add_language_file('messages');
			$this->msgs->add_error($this->locale->lang($error_msg));
		}

		$target_add = $pid != null ? '&amp;'.BS_URL_PID.'='.$pid : '';

		// textfield
		$pform = new BS_PostingForm($this->locale->lang('text').':',$default_text,'pm');
		$pform->set_show_attachments(true);
		$pform->set_formular($form);
		$pform->add_form();

		// pm-review
		if($pid != null)
			$this->_add_pm_review($edaten['sender_id'],$edaten['user_name']);
		
		$this->_request_formular();
		$this->tpl->add_variables(array(
			'action_param' => BS_URL_ACTION,
			'action_type' => BS_ACTION_SEND_PM,
			'target_url' => $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=pmcompose'.$target_add),
			'receivers' => $receiver != null ? $receiver : array(),
			'receiver_num' => $receiver != null && is_array($receiver) && count($receiver),
			'user_search_url' => $this->url->get_standalone_url('front','user_search'),
			'title_value' => $form->get_input_value('pm_title',$default_title)
		));
	}

	/**
	 * adds the given receiver if possible
	 *
	 * @param array $receiver the receiver-list (will be modified)
	 * @param array $new_receiver an array with the usernames of the receivers to add
	 * @return mixed the error-message if an error occurred otherwise true or false (=ignore)
	 */
	private function _add_new_receiver(&$receiver,$new_receiver)
	{
		if($new_receiver == null)
			return false;

		if($receiver === null || !is_array($receiver))
			return 'no_receivers_assigned';

		if(count($receiver) > BS_MAX_PM_RECEIVER)
			return 'error_pm_too_many_receiver';

		$found_user = array();
		$add_error = false;

		$user_names = explode(',',$new_receiver);
		PLIB_Array_Utils::trim($user_names);

		$un_str = PLIB_Array_Utils::advanced_implode("','",$user_names);
		if(count($un_str) == 0)
		{
			if(count($receiver) == 0)
				return 'no_receivers_assigned';

			return false;
		}

		// make sure that the user exist
		foreach(BS_DAO::get_profile()->get_users_by_names($user_names) as $data)
		{
			// the user has to allow pms
			if($data['allow_pms'] == 0)
				continue;
			
			// have we reached the maximum?
			if(count($found_user) >= BS_MAX_PM_RECEIVER)
			{
				$add_error = true;
				break;
			}

			// check if the user has already been added
			if($receiver != null && array_search($data['user_name'],$receiver) !== false)
			{
				$add_error = true;
				continue;
			}

			// check if the inbox of the receiver is full
			$r_inbox = BS_DAO::get_pms()->get_count_in_folder('inbox',$data['id']);
			if($this->cfg['pm_max_inbox'] > 0 && $r_inbox > $this->cfg['pm_max_inbox'])
			{
				$add_error = true;
				continue;
			}

			// check if the receiver has banned this user
			if(BS_DAO::get_userbans()->has_baned($data['id'],$this->user->get_user_id()))
			{
				$add_error = true;
				continue;
			}

			$found_user[] = $data['user_name'];
		}

		if(count($found_user) != count($user_names))
			$add_error = true;

		// no user found?
		if(count($found_user) == 0 && count($receiver) == 0)
			return 'no_receivers_assigned';

		// add the found receiver
		foreach($found_user as $user_name)
			$receiver[] = $user_name;

		return $add_error ? 'error_pm_add_receiver' : true;
	}

	/**
	 * Adds the last X pms of $this->user->get_user_id() and the given user
	 *
	 * @param int $user_id the user-id
	 * @param string $user_name the user-name
	 */
	private function _add_pm_review($user_id,$user_name)
	{
		$this->tpl->set_template('inc_message_review.htm');
	
		$enable_bbcode = BS_PostingUtils::get_instance()->get_message_option('enable_bbcode');
		$enable_smileys = BS_PostingUtils::get_instance()->get_message_option('enable_smileys');
		
		$messages = array();
		$pmlist = BS_DAO::get_pms()->get_last_pms_with_user(
			$this->user->get_user_id(),$user_id,BS_PM_REVIEW_MESSAGE_COUNT
		);
		foreach($pmlist as $data)
		{
			$bbcode = new BS_BBCode_Parser($data['pm_text'],'posts',$enable_bbcode,$enable_smileys);
			$text = $bbcode->get_message_for_output();
	
			$user = BS_UserUtils::get_instance()->get_link(
				$data['sender_id'],$data['sender_name'],$data['user_group']
			);
	
			$messages[] = array(
				'text' => $text,
				'user_name' => $user,
				'subject' => '<span style="font-weight: normal;">'.$data['pm_title'].'</span>',
				'date' => PLIB_Date::get_date($data['pm_date'],true,true),
				'post_id' => $data['id']
			);
		}
	
		$this->tpl->add_array('messages',$messages);
		$this->tpl->add_variables(array(
			'show_quote' => true,
			'field_id' => 'bbcode_area',
			'request_url' => $this->url->get_standalone_url(
				'front','ajax_quote_message','&id=%d%&type=pm','&'
			),
			'topic_title' => sprintf($this->locale->lang('pm_review'),BS_PM_REVIEW_MESSAGE_COUNT,$user_name),
			'limit_height' => false
		));
		$this->tpl->restore_template();
	}
	
	public function get_location()
	{
		$uid = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$pid = $this->input->get_var(BS_URL_PID,'get',PLIB_Input::ID);
		$id = ($uid != null) ? '&amp;'.BS_URL_ID.'='.$uid : '';
		$pid = ($pid != null) ? '&amp;'.BS_URL_PID.'='.$pid : '';
		return array(
			$this->locale->lang('newpm') => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=pmcompose'.$id.$pid)
		);
	}
}
?>