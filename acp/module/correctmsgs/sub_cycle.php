<?php
/**
 * Contains the cycle-submodule for correctmsgs
 * 
 * @version			$Id: sub_cycle.php 725 2008-05-22 15:48:16Z nasmussen $
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
	public function run()
	{
		$helper = BS_ACP_Module_CorrectMsgs_Helper::get_instance();
		
		$pos = $this->input->get_var('pos','get',PLIB_Input::INTEGER);
		if($pos === null)
			$pos = 0;
		
		// load messages from session
		$msgs = $this->user->get_session_data('im_data');
		if($msgs === false)
		{
			$msgs = $helper->get_incorrect_messages();
			$this->user->set_session_data('im_data',$msgs);
		}
		
		// move forward / backwards
		if($this->input->isset_var('prev','post') && $pos > 0)
			$pos--;
		else if($this->input->isset_var('next','post') && $pos < count($msgs) - 1)
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
				break;
			
			case 'pm':
				$data = BS_DAO::get_pms()->get_by_id($id);
				$data['use_bbcode'] = true;
				$data['use_smileys'] = true;
				break;
			
			case 'link':
				$data = BS_DAO::get_links()->get_by_id($id);
				$data['use_bbcode'] = true;
				$data['use_smileys'] = true;
				break;
		}
		
		$this->locale->add_language_file('messages');
		
		$error = '';
		if($this->input->isset_var('test','post'))
			$data['text_posted'] = $this->input->get_var('text','post',PLIB_Input::STRING);

		$err = $this->_update_message($data['id'],$data['text_posted'],$data['use_bbcode'],
			$data['use_smileys'],$type,$this->input->isset_var('test','post'));
		if($err != '')
		{
			$msg = $this->locale->contains_lang('error_'.$err) ? $this->locale->lang('error_'.$err) : $err;
			$error = '<span style="color: #FF0000;"><b>'.$this->locale->lang('error').':</b></span> '.$msg;
		}
		else
			$error = '<span style="color: #008000;"><b>'.$this->locale->lang('edit_messages_success').'</b></span>';

		$data['text_posted'] = stripslashes($data['text_posted']);
		
		switch($type)
		{
			case 'post':
				$url = $this->url->get_frontend_url(
					'&amp;'.BS_URL_ACTION.'=redirect&amp;'.BS_URL_LOC.'=show_post&amp;'.BS_URL_ID.'='.$data['id']
				);
				$type_str = '<a target="_blank" href="'.$url.'">';
				$type_str .= $this->locale->lang('msgs_'.$type).'</a>';
				break;
			
			case 'link':
				$url = $this->url->get_frontend_url(
					'&amp;'.BS_URL_ACTION.'=linklist&amp;'.BS_URL_ID.'='.$data['id']
				);
				$type_str = '<a target="_blank" href="'.$url.'">';
				$type_str .= $this->locale->lang('msgs_'.$type).'</a>';
				break;
			
			default:
				$type_str = $this->locale->lang('msgs_'.$type);
				break;
		}
		
		$this->tpl->add_variables(array(
			'position' => $pos + 1,
			'total' => count($msgs),
			'error' => $error,
			'text' => $data['text_posted'],
			'type' => $type_str,
			'next_disabled' => $pos >= count($msgs) - 1,
			'prev_disabled' => $pos <= 0,
			'target' => $this->url->get_acpmod_url(0,'&amp;action=cycle&amp;pos='.$pos)
		));
	}
	
	public function get_location()
	{
		return array();
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
			case 'link':
				$loc = 'lnkdesc';
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
			}
		}
	
		return '';
	}
}
?>