<?php
/**
 * Contains the upload-avatar-action
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * The upload-avatar-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_uploadavatar extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// the user has to be loggedin
		if(!$user->is_loggedin() || $cfg['enable_avatars'] == 0)
			return 'You are a guest or avatars are disabled';

		// was the file-upload successfull?
		if($_FILES['bild_upload']['name'] == '')
			return 'errorwhileuploading';

		if($_FILES['bild_upload']['error'] != 0)
			return 'errorwhileuploading';

		// check the image-size
		if($_FILES['bild_upload']['size'] > ($cfg['profile_max_img_filesize'] * 1024))
		{
			return sprintf(
				$locale->lang('error_image_filesize'),$cfg['profile_max_img_filesize'] * 1024,
				$_FILES['bild_upload']['size']
			);
		}

		$image_size_checked = false;
		if($imagesize = @getimagesize($_FILES['bild_upload']['tmp_name']))
		{
			list($img_width,$img_height) = explode('x',$cfg['profile_max_img_size']);
			if($imagesize[0] > $img_width || $imagesize[1] > $img_height)
			{
				return sprintf(
					$locale->lang('error_imagetoobig'),$img_width,$img_height
				);
			}

			$image_size_checked = true;
		}

		// use a unique avatar-name
		$ext = FWS_FileUtils::get_extension($_FILES['bild_upload']['name']);
		$name = $user->get_user_id().'_'.FWS_Date::get_formated_date('YmdHis').'.'.$ext;

		// ensure that every user can't upload more than the specified number of avatars
		$num = BS_DAO::get_avatars()->get_count_of_user($user->get_user_id());
		if($num >= $cfg['profile_max_avatars'])
			return 'toomanyavatarsuploaded';

		// check the file type
		$allowed_types = array('image/gif','image/jpeg','image/png','image/pjpeg','image/jpg','image/x-png');
		$allowed_extensions = array('gif','jpeg','jpg','png');
		if(!in_array($_FILES['bild_upload']['type'],$allowed_types) || !in_array($ext,$allowed_extensions))
			return 'wrongfiletype';

		// upload the file
		$target_path = FWS_Path::server_app().'images/avatars/'.$name;
		if(!@move_uploaded_file($_FILES['bild_upload']['tmp_name'],$target_path))
			return 'errorwhileuploading';

		@chmod($target_path,0644);

		// if we haven't checked the image-size yet do it now
		if(!$image_size_checked)
		{
			$image_too_big = false;

			// we check the local image because the server does not allow to check the image in the temporary
			// directory.
			$imagesize = @getimagesize($target_path);
			if(!$imagesize)
				$image_too_big = true;

			list($img_width,$img_height) = explode('x',$cfg['profile_max_img_size']);
			if($imagesize[0] > $img_width || $imagesize[1] > $img_height)
				$image_too_big = true;

			// the image is too big, so try to delete the file
			if($image_too_big)
			{
				@chmod($target_path,0777);
				@unlink($target_path);
				return sprintf(
					$locale->lang('error_imagetoobig'),$img_width,$img_height
				);
			}
		}

		// create the entry if it does not exist
		BS_DAO::get_avatars()->create($name,$user->get_user_id());

		$this->set_action_performed(true);
		$this->add_link(
			$locale->lang('back'),
			BS_URL::get_sub_url('userprofile','avatars')
		);

		return '';
	}
}
?>