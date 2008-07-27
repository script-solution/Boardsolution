<?php
/**
 * Contains the attachment-download-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The page to download an attachment
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_download extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->set_output_enabled(false);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$user = PLIB_Props::get()->user();
		$auth = PLIB_Props::get()->auth();
		$ips = PLIB_Props::get()->ips();

		$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		
		if($id === null)
		{
			// ok, then try the path
			$path = $input->get_var('path','get',PLIB_Input::STRING);
			if($path !== null && preg_match('/^uploads\//',$path))
			{
				$path = str_replace('../','',$path);
				$data = BS_DAO::get_attachments()->get_attachment_of_user_by_path(
					$path,$user->get_user_id()
				);
			}
		}
		else
			$data = BS_DAO::get_attachments()->get_by_id($id);
		
		// do we have got the data?
		if(isset($data) && $data['id'] != '')
		{
		  if($auth->has_global_permission('attachments_download'))
		  {
		    // check if the user has the permission to download _this_ file
		    $dl_allowed = false;
		    // pm-attachment?
		    if($data['pm_id'] > 0)
		    	$dl_allowed = $user->is_loggedin() && $data['poster_id'] == $user->get_user_id();
		    // post-attachment?
		    else if($data['post_id'] > 0)
		    {
		    	$postdata = BS_DAO::get_posts()->get_post_by_id($data['post_id']);
		    	$dl_allowed = $auth->has_access_to_intern_forum($postdata['rubrikid']);
		    }
		    
		    if($dl_allowed)
		    {
			    if(is_file(PLIB_Path::server_app().$data['attachment_path']))
			    {
			    	if(!$ips->entry_exists('adl_'.$data['id']))
			      	BS_DAO::get_attachments()->inc_downloads($data['id']);
			      $ips->add_entry('adl_'.$data['id']);
						
			      $fileinfo = @getimagesize(PLIB_Path::server_app().$data['attachment_path']);
			      $filetype = ($fileinfo) ? 'application/'.$fileinfo['mime'] : 'application/octet-stream';
			      header('Content-Description: File Transfer');
			      header('Content-Type: '.$filetype);
			      if($filesize = @filesize(PLIB_Path::server_app().$data['attachment_path']))
			        header('Content-Length: '.$filesize);
			      header('Content-Disposition: attachment; filename="'.basename($data['attachment_path']).'"');
			      readfile(PLIB_Path::server_app().$data['attachment_path']);
			      return;
			    }
		    }
		  }
		}
		
		$this->report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
	}
}
?>