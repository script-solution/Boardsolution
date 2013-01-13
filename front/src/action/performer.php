<?php
/**
 * Contains the action-performer
 * 
 * @package			Boardsolution
 * @subpackage	src.action
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
 * The action-performer. We overwrite it to provide a custom get_action_id()
 * method.
 *
 * @package			Boardsolution
 * @subpackage	src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Performer extends FWS_Action_Performer implements FWS_Action_Listener
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->set_mod_folder('front/module/');
		$this->set_listener($this);
	}
	
	/**
	 * @see FWS_Action_Performer::get_action_id()
	 *
	 * @return int
	 */
	protected function get_action_id()
	{
		$input = FWS_Props::get()->input();

		$action_type = $input->get_var('action_type','post',FWS_Input::INTEGER);
		if($action_type === null)
			$action_type = $input->get_var(BS_URL_AT,'get',FWS_Input::INTEGER);

		return $action_type;
	}
	
	/**
	 * @see FWS_Action_Listener::before_action_performed()
	 *
	 * @param int $id
	 * @param FWS_Action_Base $action
	 */
	public function before_action_performed($id,$action)
	{
		$db = FWS_Props::get()->db();
		$locale = FWS_Props::get()->locale();

		// start a transaction for all actions
		$db->start_transaction();
		
		// we have to add the messages-file if an action should be performed
		$locale->add_language_file('messages');
	}
	
	/**
	 * @see FWS_Action_Listener::after_action_performed()
	 *
	 * @param int $id
	 * @param FWS_Action_Base $action
	 * @param string $message
	 */
	public function after_action_performed($id,$action,&$message)
	{
		$db = FWS_Props::get()->db();
		$input = FWS_Props::get()->input();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();

		// commit the transaction
		// note that we do this always because we assume that all actions check all the data
		// BEFORE they do any db-actions
		$db->commit_transaction();
		
		// add / remove attachments
		if($message == '')
		{
			switch($id)
			{
				case BS_ACTION_REPLY:
				case BS_ACTION_EDIT_POST:
				case BS_ACTION_START_TOPIC:
				case BS_ACTION_START_POLL:
				case BS_ACTION_START_EVENT:
				case BS_ACTION_SEND_PM:
					if($input->isset_var('add_attachment','post'))
						$message = $this->_add_attachment();
					if($input->isset_var('remove_attachment','post'))
					{
						$message = $this->_remove_attachment();
						if($message === '')
							$msgs->add_notice($locale->lang('success_remove_attachment'));
					}
					break;
			}
		}
	}
	
	/**
	 * adds an attachment to the post-array
	 *
	 * @return string the error if any occurred otherwise an empty string
	 */
	private function _add_attachment()
	{
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();

		// has the user the permission to attach files?
		if($cfg['attachments_enable'] == 0 ||
				!$auth->has_global_permission('attachments_add'))
			return 'Attachments disabled or you have no permission to add attachments';
	
		// have we reached the maximum number of attachments?
		$total = BS_DAO::get_attachments()->get_attachment_count();
		if($cfg['attachments_max_number'] > 0 && $total >= $cfg['attachments_max_number'])
			return 'attachment_max_number';
	
		// has the user reached his maximum number of attachments?
		if($cfg['attachments_per_user'] > 0)
		{
			$num = BS_DAO::get_attachments()->get_attachment_count_of_user($user->get_user_id());
			if($num >= $cfg['attachments_per_user'])
				return 'attachments_per_user';
		}
	
		// has the user already attached enough files in the current post?
		$file_paths = $input->get_var('attached_file_paths','post');
		if($cfg['attachments_max_per_post'] > 0 && $file_paths != null &&
			 count($file_paths) >= $cfg['attachments_max_per_post'])
			return 'attachment_max_per_post';
	
		// upload-error?
		if($_FILES['attachment']['error'] != 0)
			return 'attachment_upload_failed';
	
		// check if the size of the attachment is valid
		if($cfg['attachments_max_filesize'] > 0 &&
				($_FILES['attachment']['size'] / 1024) > $cfg['attachments_max_filesize'])
			return sprintf($locale->lang('error_attachment_max_filesize'),
										 $cfg['attachments_max_filesize']);
	
		// file-extension allowed?
		if(!$functions->check_attachment_extension($_FILES['attachment']['name']))
			return sprintf($locale->lang('error_attachment_filetypes'),
										 str_replace('|',', ',$cfg['attachments_filetypes']));
	
		// does the file already exist?
		$name = FWS_FileUtils::clean_filename($_FILES['attachment']['name']);
		$ext = FWS_FileUtils::get_extension($name);
		$base = FWS_String::substr($name,0,FWS_String::strlen($name) - FWS_String::strlen($ext) - 1);
		for($i = 1;is_file(FWS_Path::server_app().'uploads/'.$name);$i++)
			$name = $base.$i.'.'.$ext;
	
		// try to move the uploaded file
		$target_path = FWS_Path::server_app().'uploads/'.$name;
		if(!@move_uploaded_file($_FILES['attachment']['tmp_name'],$target_path))
			return 'attachment_upload_failed';
	
		@chmod($target_path,0644);
	
		// add it to the file-list
		if($file_paths != null)
			$file_paths[] = 'uploads/'.$name;
		else
			$file_paths = array('uploads/'.$name);
	
		$input->set_var('attached_file_paths','post',$file_paths);
	
		return '';
	}
	
	/**
	 * removes a attachment
	 *
	 * @return string the error if any occurred or an empty string
	 */
	private function _remove_attachment()
	{
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();

		if($cfg['attachments_enable'] == 0 ||
			 !$auth->has_global_permission('attachments_add'))
			return 'Attachments are disabled or you have no permission to add attachments';
	
		$remove_att = $input->get_var('remove_attachment','post');
		$file_paths = $input->get_var('attached_file_paths','post');
		$keys = array_keys($remove_att);
		$split = explode('|',$keys[0]);
	
		// is it just a file...
		if($split[0] == 'file' && preg_match('/^uploads\//',$file_paths[$split[1]]))
		{
			$file_paths[$split[1]] = str_replace('../','',$file_paths[$split[1]]);
			@unlink(FWS_Path::server_app().$file_paths[$split[1]]);
	
			unset($file_paths[$split[1]]);
			$input->set_var('attached_file_paths','post',$file_paths);
			if(count($file_paths) == 0)
				$input->set_var('attached_file_paths','post',null);
		}
		// or does it already exist in the database?
		else if(FWS_Helper::is_integer($split[1]))
		{
			$path = BS_DAO::get_attachments()->get_by_id($split[1]);
			if(!$path)
				return 'An attachment with id "'.$split[1].'" has not been found';
			
			// is it allowed for the user to remove the attachment?
			if($path['pm_id'] == 0)
			{
				if(!$auth->has_current_forum_perm(BS_MODE_EDIT_POST,$path['poster_id']))
					return 'No permission to edit the post';
			}
			else
				return 'You can\'t remove attachments from PMs';
			
			$functions->delete_attachment($path['attachment_path']);
			
			BS_DAO::get_attachments()->delete_by_ids(array($split[1]));
		}
	
		return '';
	}
}
?>