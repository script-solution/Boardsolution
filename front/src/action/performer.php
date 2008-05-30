<?php
/**
 * Contains the action-performer
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The action-performer. We overwrite it to provide a custom get_action_type()
 * method.
 *
 * @package			Boardsolution
 * @subpackage	src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Performer extends PLIB_Actions_Performer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->set_mod_folder('front/module/');
	}
	
	public function get_action_type()
	{
		$action_type = $this->input->get_var('action_type','post',PLIB_Input::INTEGER);
		if($action_type === null)
			$action_type = $this->input->get_var(BS_URL_AT,'get',PLIB_Input::INTEGER);

		return $action_type;
	}
	
	protected function _before_action_performed($id,$action)
	{
		parent::_before_action_performed($id,$action);
		
		// start a transaction for all actions
		$this->db->start_transaction();
		
		// we have to add the messages-file if an action should be performed
		$this->locale->add_language_file('messages');
	}
	
	protected function _after_action_performed($id,$action,&$message)
	{
		parent::_after_action_performed($id,$action,$message);
		
		// commit the transaction
		// note that we do this always because we assume that all actions check all the data
		// BEFORE they do any db-actions
		$this->db->commit_transaction();
		
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
					if($this->input->isset_var('add_attachment','post'))
						$message = $this->_add_attachment();
					if($this->input->isset_var('remove_attachment','post'))
					{
						$message = $this->_remove_attachment();
						if($message === '')
							$this->msgs->add_notice('success_remove_attachment');
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
		// has the user the permission to attach files?
		if($this->cfg['attachments_enable'] == 0 ||
				!$this->auth->has_global_permission('attachments_add'))
			return 'Attachments disabled or you have no permission to add attachments';
	
		// have we reached the maximum number of attachments?
		$total = BS_DAO::get_attachments()->get_attachment_count();
		if($this->cfg['attachments_max_number'] > 0 && $total >= $this->cfg['attachments_max_number'])
			return 'attachment_max_number';
	
		// has the user reached his maximum number of attachments?
		if($this->cfg['attachments_per_user'] > 0)
		{
			$num = BS_DAO::get_attachments()->get_attachment_count_of_user($this->user->get_user_id());
			if($num >= $this->cfg['attachments_per_user'])
				return 'attachments_per_user';
		}
	
		// has the user already attached enough files in the current post?
		$file_paths = $this->input->get_var('attached_file_paths','post');
		if($this->cfg['attachments_max_per_post'] > 0 && $file_paths != null &&
			 count($file_paths) >= $this->cfg['attachments_max_per_post'])
			return 'attachment_max_per_post';
	
		// upload-error?
		if($_FILES['attachment']['error'] != 0)
			return 'attachment_upload_failed';
	
		// check if the size of the attachment is valid
		if($this->cfg['attachments_max_filesize'] > 0 &&
				($_FILES['attachment']['size'] / 1024) > $this->cfg['attachments_max_filesize'])
			return sprintf($this->locale->lang('error_attachment_max_filesize'),
										 $this->cfg['attachments_max_filesize']);
	
		// file-extension allowed?
		if(!$this->functions->check_attachment_extension($_FILES['attachment']['name']))
			return sprintf($this->locale->lang('error_attachment_filetypes'),
										 str_replace('|',', ',$this->cfg['attachments_filetypes']));
	
		// does the file already exist?
		$name = $_FILES['attachment']['name'];
		$ext = PLIB_FileUtils::get_extension($name);
		$base = PLIB_String::substr($name,0,PLIB_String::strlen($name) - PLIB_String::strlen($ext) - 1);
		for($i = 1;is_file(PLIB_Path::inner().'uploads/'.$name);$i++)
			$name = $base.$i.'.'.$ext;
	
		// try to move the uploaded file
		if(!@move_uploaded_file($_FILES['attachment']['tmp_name'],PLIB_Path::inner().'uploads/'.$name))
			return 'attachment_upload_failed';
	
		@chmod(PLIB_Path::inner().'uploads/'.$name,0644);
	
		// add it to the file-list
		if($file_paths != null)
			$file_paths[] = 'uploads/'.$name;
		else
			$file_paths = array('uploads/'.$name);
	
		$this->input->set_var('attached_file_paths','post',$file_paths);
	
		return '';
	}
	
	/**
	 * removes a attachment
	 *
	 * @return string the error if any occurred or an empty string
	 */
	private function _remove_attachment()
	{
		if($this->cfg['attachments_enable'] == 0 ||
			 !$this->auth->has_global_permission('attachments_add'))
			return 'Attachments are disabled or you have no permission to add attachments';
	
		$remove_att = $this->input->get_var('remove_attachment','post');
		$file_paths = $this->input->get_var('attached_file_paths','post');
		$keys = array_keys($remove_att);
		$split = explode('|',$keys[0]);
	
		// is it just a file...
		if($split[0] == 'file' && preg_match('/^uploads\//',$file_paths[$split[1]]))
		{
			$file_paths[$split[1]] = str_replace('../','',$file_paths[$split[1]]);
			@unlink(PLIB_Path::inner().$file_paths[$split[1]]);
	
			unset($file_paths[$split[1]]);
			$this->input->set_var('attached_file_paths','post',$file_paths);
			if(count($file_paths) == 0)
				$this->input->set_var('attached_file_paths','post',null);
		}
		// or does it already exist in the database?
		else if(PLIB_Helper::is_integer($split[1]))
		{
			$path = BS_DAO::get_attachments()->get_by_id($split[1]);
			if(!$path)
				return 'An attachment with id "'.$split[1].'" has not been found';
			
			// is it allowed for the user to remove the attachment?
			if($path['pm_id'] == 0)
			{
				if(!$this->auth->has_current_forum_perm(BS_MODE_EDIT_POST,$path['poster_id']))
					return 'No permission to edit the post';
			}
			else
				return 'You can\'t remove attachments from PMs';
			
			$this->functions->delete_attachment($path['attachment_path']);
			
			BS_DAO::get_attachments()->delete_by_ids(array($split[1]));
		}
	
		return '';
	}
}
?>