<?php
/**
 * Contains the thumbnail-generation-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_download_renderer();
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$user = PLIB_Props::get()->user();
		$auth = PLIB_Props::get()->auth();

		$ipath = $input->get_var('path','get',PLIB_Input::STRING);
		
		$file = basename($ipath);
		$path = dirname($ipath).'/'.$file;
		
		if(!preg_match("/^uploads\\//",$path) || !file_exists(PLIB_Path::server_app().$path))
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
    
    if(!$view_allowed)
		{
			$this->report_error();
			return;
		}
		
		$i_width = $input->get_var('width','get',PLIB_Input::INTEGER);
		$i_height = $input->get_var('height','get',PLIB_Input::INTEGER);
		$i_method = $input->correct_var('method','get',PLIB_Input::INTEGER,
			array('width_fixed','height_fixed','both'),'width_fixed');
		
		// ensure that the parameters are valid
	  if($i_width <= 0 || $i_width > 2000)
	    $i_width = 200;
	  if($i_height <= 0 || $i_height > 2000)
	    $i_height = 150;
	  
	  // check if the gd-library is installed
	  if(!PLIB_PHPConfig::is_gd_installed())
		{
			$this->report_error(PLIB_Document_Messages::ERROR,'GD-Library could not be found!');
			return;
		}
	  
	  // is the image readable?
	  $src_size = @getimagesize(PLIB_Path::server_app().$path);
	  if(!$src_size)
		{
			$this->report_error(PLIB_Document_Messages::ERROR,'The image is not readable!');
			return;
		}
	
		// check if we have to generate the thumbnail
		$real_path = PLIB_Path::server_app().$path;
		$ext = PLIB_FileUtils::get_extension($path,false);
		$filename = 'uploads/'.PLIB_FileUtils::get_name($path,false);
		if(!is_file(PLIB_Path::server_app().$filename.'_thumb.'.$ext))
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
			$dest = new PLIB_GD_Image((int)$width,(int)$height,PLIB_PHPConfig::is_gd2_installed());
		  
			// load the source-image
			switch($src_size[2])
		  {
		    case 1:
					$src = PLIB_GD_Image::load_from($real_path,'gif');
					break;
		    case 2:
					$src = PLIB_GD_Image::load_from($real_path,'jpeg');
					break;
		    case 3:
					$src = PLIB_GD_Image::load_from($real_path,'png');
					break;
		    default:
					$this->report_error(PLIB_Document_Messages::ERROR,'Invalid image-type!');
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
		  $target = PLIB_Path::server_app().$filename.'_thumb.'.$ext;
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
		    	$this->report_error(PLIB_Document_Messages::ERROR,'Unable to create image!');
		    	return;
		  }
		  
		  @chmod($target,0644);
		  
		  $dest->destroy();
		  $src->destroy();
		}
		
		$doc = PLIB_Props::get()->doc();
		$renderer = $doc->use_download_renderer();
		$renderer->set_headers(false);
		$renderer->set_file(PLIB_Path::server_app().$filename.'_thumb.'.$ext);
		
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
