<?php
/**
 * Contains the standalone-class for the attachment-download
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The page to download an attachment
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_download extends BS_Standalone
{
	public function run()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		
		if($id === null)
		{
			// ok, then try the path
			$path = $this->input->get_var('path','get',PLIB_Input::STRING);
			if($path !== null && preg_match('/^uploads\//',$path))
			{
				$path = str_replace('../','',$path);
				$data = BS_DAO::get_attachments()->get_attachment_of_user_by_path(
					$path,$this->user->get_user_id()
				);
			}
		}
		else
			$data = BS_DAO::get_attachments()->get_by_id($id);
		
		// do we have got the data?
		if(isset($data) && $data['id'] != '')
		{
		  if($this->auth->has_global_permission('attachments_download'))
		  {
		    // check if the user has the permission to download _this_ file
		    $dl_allowed = false;
		    // pm-attachment?
		    if($data['pm_id'] > 0)
		    	$dl_allowed = $this->user->is_loggedin() && $data['poster_id'] == $this->user->get_user_id();
		    // post-attachment?
		    else if($data['post_id'] > 0)
		    {
		    	$postdata = BS_DAO::get_posts()->get_post_by_id($data['post_id']);
		    	$dl_allowed = $this->auth->has_access_to_intern_forum($postdata['rubrikid']);
		    }
		    
		    if($dl_allowed)
		    {
			    if(is_file(PLIB_Path::inner().$data['attachment_path']))
			    {
			    	if(!$this->ips->entry_exists('adl_'.$data['id']))
			      	BS_DAO::get_attachments()->inc_downloads($data['id']);
			      $this->ips->add_entry('adl_'.$data['id']);
						
			      $fileinfo = @getimagesize(PLIB_Path::inner().$data['attachment_path']);
			      $filetype = ($fileinfo) ? 'application/'.$fileinfo['mime'] : 'application/octet-stream';
			      header('Content-Description: File Transfer');
			      header('Content-Type: '.$filetype);
			      if($filesize = @filesize(PLIB_Path::inner().$data['attachment_path']))
			        header('Content-Length: '.$filesize);
			      header('Content-Disposition: attachment; filename="'.basename($data['attachment_path']).'"');
			      readfile(PLIB_Path::inner().$data['attachment_path']);
			      exit;
			    }
		    }
		  }
		}
		
		// redirect to error-page
		$url = $this->url->get_frontend_url('&'.BS_URL_ACTION.'=download_failed','&');
		$this->doc->redirect($url);
	}
}
?>