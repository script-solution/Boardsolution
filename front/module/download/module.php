<?php
/**
 * Contains the attachment-download-module
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
 * The page to download an attachment
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_download extends BS_Front_Module
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
		$ips = FWS_Props::get()->ips();
		$doc = FWS_Props::get()->doc();

		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		
		if($id === null)
		{
			// ok, then try the path
			$path = $input->get_var('path','get',FWS_Input::STRING);
			if($path === null || !preg_match('/^uploads\//',$path))
			{
				$this->report_error();
				return;
			}
			
			$path = str_replace('../','',$path);
			$data = BS_DAO::get_attachments()->get_attachment_of_user_by_path(
				$path,$user->get_user_id()
			);
		}
		else
			$data = BS_DAO::get_attachments()->get_by_id($id);
		
		// not found?
		if($data === false)
		{
			$this->report_error();
			return;
		}
		
		// do we have got the data?
		if(!$auth->has_global_permission('attachments_download'))
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}

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

		if(!$dl_allowed)
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}
		
		if(!is_file(FWS_Path::server_app().$data['attachment_path']))
		{
			$this->report_error();
			return;
		}
		
		if(!$ips->entry_exists('adl_'.$data['id']))
			BS_DAO::get_attachments()->inc_downloads($data['id']);
		$ips->add_entry('adl_'.$data['id']);
		
		$renderer = $doc->use_download_renderer();
		$renderer->set_file(FWS_Path::server_app().$data['attachment_path']);
	}
}
?>