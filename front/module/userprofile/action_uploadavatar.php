<?php
/**
 * Contains the upload-avatar-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The upload-avatar-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_uploadavatar extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// nothing to do?
		if(!$this->input->isset_var('submit','post'))
			return '';

		// the user has to be loggedin
		if(!$this->user->is_loggedin() || $this->cfg['enable_avatars'] == 0)
			return 'You are a guest or avatars are disabled';

		// was the file-upload successfull?
		if($_FILES['bild_upload']['name'] == '')
			return 'errorwhileuploading';

		if($_FILES['bild_upload']['error'] != 0)
			return 'errorwhileuploading';

		// check the image-size
		if($_FILES['bild_upload']['size'] > ($this->cfg['profile_max_img_filesize'] * 1024))
		{
			// TODO the error-message is wrong here ;)
			list($img_width,$img_height) = explode('x',$this->cfg['profile_max_img_size']);
			return sprintf(
				$this->locale->lang('error_imagetoobig'),$img_width,$img_height
			);
		}

		$image_size_checked = false;
		if($imagesize = @getimagesize($_FILES['bild_upload']['tmp_name']))
		{
			list($img_width,$img_height) = explode('x',$this->cfg['profile_max_img_size']);
			if($imagesize[0] > $img_width || $imagesize[1] > $img_height)
			{
				return sprintf(
					$this->locale->lang('error_imagetoobig'),$img_width,$img_height
				);
			}

			$image_size_checked = true;
		}

		// use a unique avatar-name
		$ext = PLIB_FileUtils::get_extension($_FILES['bild_upload']['name']);
		$name = $this->user->get_user_id().'_'.PLIB_Date::get_formated_date('YmdHis').'.'.$ext;

		// ensure that every user can't upload more than the specified number of avatars
		$num = BS_DAO::get_avatars()->get_count_of_user($this->user->get_user_id());
		if($num >= $this->cfg['profile_max_avatars'])
			return 'toomanyavatarsuploaded';

		// check the file type
		$allowed_types = array('image/gif','image/jpeg','image/png','image/pjpeg','image/jpg','image/x-png');
		$allowed_extensions = array('gif','jpeg','jpg','png');
		if(!in_array($_FILES['bild_upload']['type'],$allowed_types) || !in_array($ext,$allowed_extensions))
			return 'wrongfiletype';

		// upload the file
		if(!@move_uploaded_file($_FILES['bild_upload']['tmp_name'],PLIB_Path::inner().'images/avatars/'.$name))
			return 'errorwhileuploading';

		@chmod(PLIB_Path::inner().'images/avatars/'.$name,0644);

		// if we haven't checked the image-size yet do it now
		if(!$image_size_checked)
		{
			$image_too_big = false;

			// we check the local image because the server does not allow to check the image in the temporary
			// directory.
			$imagesize = @getimagesize(PLIB_Path::inner().'images/avatars/'.$name);
			if(!$imagesize)
				$image_too_big = true;

			list($img_width,$img_height) = explode('x',$this->cfg['profile_max_img_size']);
			if($imagesize[0] > $img_width || $imagesize[1] > $img_height)
				$image_too_big = true;

			// the image is too big, so try to delete the file
			if($image_too_big)
			{
				@chmod(PLIB_Path::inner().'images/avatars/'.$name,0777);
				@unlink(PLIB_Path::inner().'images/avatars/'.$name);
				return sprintf(
					$this->locale->lang('error_imagetoobig'),$img_width,$img_height
				);
			}
		}

		// create the entry if it does not exist
		BS_DAO::get_avatars()->create($name,$this->user->get_user_id());

		$this->set_action_performed(true);
		$this->add_link(
			$this->locale->lang('back'),
			$this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=avatars')
		);

		return '';
	}
}
?>