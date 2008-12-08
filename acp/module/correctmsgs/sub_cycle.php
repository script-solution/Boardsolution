<?php
/**
 * Contains the cycle-submodule for correctmsgs
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The cycle sub-module for the correctmsgs-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_correctmsgs_cycle extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		
		$pos = $input->get_var('pos','get',FWS_Input::INTEGER);
		if($pos === null)
			$pos = 0;
		
		// load messages from session
		$msgs = $user->get_session_data('im_data');
		if($msgs === false)
		{
			$msgs = BS_ACP_Module_CorrectMsgs_Helper::get_incorrect_messages();
			$user->set_session_data('im_data',$msgs);
		}
		
		// move forward / backwards
		if($input->isset_var('prev','post') && $pos > 0)
			$pos--;
		else if($input->isset_var('next','post') && $pos < count($msgs) - 1)
			$pos++;
		
		// determine data of the message
		$type = $msgs[$pos][0];
		$id = $msgs[$pos][1];
		switch($type)
		{
			case 'post':
				$data = BS_DAO::get_posts()->get_post_by_id($id);
				break;
			
			case 'signature':
				$data = BS_DAO::get_profile()->get_user_by_id($id);
				$data['use_bbcode'] = true;
				$data['use_smileys'] = true;
				$data['text_posted'] = $data['signature_posted'];
				break;
			
			case 'pm':
				$data = BS_DAO::get_pms()->get_by_id($id);
				$data['use_bbcode'] = true;
				$data['use_smileys'] = true;
				$data['text_posted'] = $data['pm_text_posted'];
				break;
			
			case 'link':
				$data = BS_DAO::get_links()->get_by_id($id);
				$data['use_bbcode'] = true;
				$data['use_smileys'] = true;
				$data['text_posted'] = $data['link_desc_posted'];
				break;
			
			case 'event':
				$data = BS_DAO::get_events()->get_by_id($id);
				$data['use_bbcode'] = true;
				$data['use_smileys'] = true;
				$data['text_posted'] = $data['description_posted'];
				break;
		}
		
		$locale->add_language_file('messages');
		
		$error = '';
		if($input->isset_var('test','post'))
			$data['text_posted'] = $input->get_var('text','post',FWS_Input::STRING);

		$err = $this->_update_message($data['id'],$data['text_posted'],$data['use_bbcode'],
			$data['use_smileys'],$type,$input->isset_var('test','post'));
		if($err != '')
		{
			$msg = $locale->contains_lang('error_'.$err) ? $locale->lang('error_'.$err) : $err;
			$error = '<span style="color: #FF0000;"><b>'.$locale->lang('error').':</b></span> '.$msg;
		}
		else
			$error = '<span style="color: #008000;"><b>'.$locale->lang('edit_messages_success').'</b></span>';

		$data['text_posted'] = stripslashes($data['text_posted']);
		
		switch($type)
		{
			case 'post':
				$furl = BS_URL::get_frontend_url('redirect');
				$furl->set(BS_URL_LOC,'show_post');
				$furl->set(BS_URL_ID,$data['id']);
				$type_str = '<a target="_blank" href="'.$furl->to_url().'">';
				$type_str .= $locale->lang('msgs_'.$type).'</a>';
				break;
			
			case 'link':
				$furl = BS_URL::get_frontend_url('linklist');
				$furl->set(BS_URL_ID,$data['id']);
				$type_str = '<a target="_blank" href="'.$furl->to_url().'">';
				$type_str .= $locale->lang('msgs_'.$type).'</a>';
				break;
			
			case 'event':
				if($data['tid'] > 0)
				{
					$furl = BS_URL::get_frontend_url('redirect');
					$furl->set(BS_URL_LOC,'show_topic');
					$furl->set(BS_URL_TID,$data['tid']);
				}
				else
				{
					$furl = BS_URL::get_frontend_url('calendar');
					$furl->set(BS_URL_SUB,'eventdetails');
					$furl->set(BS_URL_ID,$data['id']);
				}
				$type_str = '<a target="_blank" href="'.$furl->to_url().'">';
				$type_str .= $locale->lang('msgs_'.$type).'</a>';
				break;
			
			default:
				$type_str = $locale->lang('msgs_'.$type);
				break;
		}
		
		$url = BS_URL::get_acpsub_url();
		$url->set('pos',$pos);
		$tpl->add_variables(array(
			'position' => $pos + 1,
			'total' => count($msgs),
			'error' => $error,
			'text' => $data['text_posted'],
			'type' => $type_str,
			'next_disabled' => $pos >= count($msgs) - 1,
			'prev_disabled' => $pos <= 0,
			'target' => $url->to_url()
		));
	}
	
	/**
	 * updates a message
	 * 
	 * @param int $id the id of the message
	 * @param string $post_text the text
	 * @param boolean $use_bbcode do you want to use bbcode?
	 * @param boolean $use_smileys do you want to use smileys?
	 * @param string $type the message-type (pm,post,signature,link)
	 * @param boolean $update do you want to update the text in the db?
	 * @return string the error-message or an empty string
	 */
	private function _update_message($id,$post_text,$use_bbcode,$use_smileys,$type,$update)
	{
		switch($type)
		{
			case 'pm':
			case 'post':
				$loc = 'posts';
				break;
			case 'signature':
				$loc = 'sig';
				break;
			case 'event':
			case 'link':
				$loc = 'desc';
				break;
		}
		
		$text = '';
		$error = BS_PostingUtils::get_instance()->prepare_message_for_db($text,$post_text,$loc,1,1);
		if($error != '')
			return $error;
		
		if($update)
		{
			switch($type)
			{
				case 'post':
					BS_DAO::get_posts()->update($id,array(
						'text' => $text,
						'text_posted' => $post_text
					));
					break;
				
				case 'signature':
					BS_DAO::get_profile()->update_user_by_id(
						array('signatur' => $text,'signature_posted' => $post_text),$id
					);
					break;
				
				case 'pm':
					BS_DAO::get_pms()->update_text($id,$text,$post_text);
					break;
				
				case 'link':
					BS_DAO::get_links()->update_text($id,$text,$post_text);
					break;
				
				case 'event':
					BS_DAO::get_events()->update($id,array(
						'description_posted' => $post_text,
						'description' => $text
					));
					break;
			}
		}
	
		return '';
	}
}
?>