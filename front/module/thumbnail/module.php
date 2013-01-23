<?php
/**
 * Contains the thumbnail-generation-module
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
 * Generates a thumbnail (if not already done) and sends it to the browser
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_thumbnail extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_download_renderer();
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();

		$ipath = $input->get_var('path','get',FWS_Input::STRING);
		
		$file = basename($ipath);
		$path = dirname($ipath).'/'.$file;
		
		if(!preg_match("/^uploads\\//",$path) || !file_exists(FWS_Path::server_app().$path))
		{
			$this->report_error();
			return;
		}
		
		$data = BS_DAO::get_attachments()->get_attachment_of_user_by_path(
			$path,$user->get_user_id()
		);
		if(!$data || $data['id'] == '')
		{
			$this->report_error();
			return;
		}
		
		// check if the user has the permission to view _this_ file
		$view_allowed = false;
			// pm-attachment?
		if($data['pm_id'] > 0)
			$view_allowed = $user->is_loggedin() && $data['poster_id'] == $user->get_user_id();
		// post-attachment?
		else if($data['post_id'] > 0)
		{
			$postdata = BS_DAO::get_posts()->get_post_by_id($data['post_id']);
			$view_allowed = $auth->has_access_to_intern_forum($postdata['rubrikid']);
		}

		if(!$auth->has_global_permission('attachments_download') || !$view_allowed)
		{
			$this->report_error();
			return;
		}
		
		$i_width = $input->get_var('width','get',FWS_Input::INTEGER);
		$i_height = $input->get_var('height','get',FWS_Input::INTEGER);
		$i_method = $input->correct_var('method','get',FWS_Input::INTEGER,
			array('width_fixed','height_fixed','both'),'width_fixed');
		
		// ensure that the parameters are valid
		if($i_width <= 0 || $i_width > 2000)
			$i_width = 200;
		if($i_height <= 0 || $i_height > 2000)
			$i_height = 150;

		// check if the gd-library is installed
		if(!FWS_PHPConfig::is_gd_installed())
		{
			$this->report_error(FWS_Document_Messages::ERROR,'GD-Library could not be found!');
			return;
		}
		
		// is the image readable?
		$src_size = @getimagesize(FWS_Path::server_app().$path);
		if(!$src_size)
		{
			$this->report_error(FWS_Document_Messages::ERROR,'The image is not readable!');
			return;
		}
	
		// check if we have to generate the thumbnail
		$real_path = FWS_Path::server_app().$path;
		$ext = FWS_FileUtils::get_extension($path,false);
		$filename = 'uploads/'.FWS_FileUtils::get_name($path,false);
		if(!is_file(FWS_Path::server_app().$filename.'_thumb.'.$ext))
		{
			// determine size
			switch($i_method)
			{
				case 'width_fixed':
					$width = $i_width;
					$height = ($src_size[1] / $src_size[0]) * $width;
					break;
				case 'height_fixed':
					$height = $i_height;
					$width = ($src_size[0] / $src_size[1]) * $height;
					break;
				default:
					$width = $i_width;
					$height = $i_height;
					break;
			}
			
			// make sure that we don't increase the image-size
			if($width > $src_size[0] && $height > $src_size[1])
			{
				$width = $src_size[0];
				$height = $src_size[1];
			}
		
			// create the destination image
			$dest = new FWS_GD_Image((int)$width,(int)$height,FWS_PHPConfig::is_gd2_installed());
			
			// load the source-image
			switch($src_size[2])
			{
				case 1:
					$src = FWS_GD_Image::load_from($real_path,'gif');
					break;
				case 2:
					$src = FWS_GD_Image::load_from($real_path,'jpeg');
					break;
				case 3:
					$src = FWS_GD_Image::load_from($real_path,'png');
					break;
				default:
					$this->report_error(FWS_Document_Messages::ERROR,'Invalid image-type!');
					return;
			}
			
			// copy the source-image to the target-image and resize it
			// take care of the GD-version
			if(function_exists('imagecopyresampled'))
			{
				$res = @imagecopyresampled(
					$dest->get_image(),$src->get_image(),0,0,0,0,$width,$height,$src_size[0],$src_size[1]
				);
				if(!$res)
				{
					imagecopyresized(
						$dest->get_image(),$src->get_image(),0,0,0,0,$width,$height,$src_size[0],$src_size[1]
					);
				}
			}
			else
			{
				imagecopyresized(
					$dest->get_image(),$src->get_image(),0,0,0,0,$width,$height,$src_size[0],$src_size[1]
				);
			}
			
			// finally create the image
			$target = FWS_Path::server_app().$filename.'_thumb.'.$ext;
			switch($src_size[2])
			{
				case 1:
					$dest->save($target,'gif',100);
					break;
				
				case 2:
					$dest->save($target,'jpeg',100);
					break;
				
				case 3:
					$dest->save($target,'png',100);
					break;
				
				default:
					$this->report_error(FWS_Document_Messages::ERROR,'Unable to create image!');
					return;
			}
			
			@chmod($target,0644);
			
			$dest->destroy();
			$src->destroy();
		}
		
		$doc = FWS_Props::get()->doc();
		$renderer = $doc->use_download_renderer();
		$renderer->set_headers(false);
		$renderer->set_file(FWS_Path::server_app().$filename.'_thumb.'.$ext);
		
		// set the appropriate header
		switch($ext)
		{
			case 'jpg':
			case 'jpeg':
				$doc->set_header('Content-type','image/jpeg');
				break;
			
			case 'gif':
				$doc->set_header('Content-type','image/gif');
				break;
			
			case 'png':
				$doc->set_header('Content-type','image/png');
				break;
		}
	}
}
?>
