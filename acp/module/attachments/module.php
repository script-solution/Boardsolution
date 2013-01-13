<?php
/**
 * Contains the attachment module for the ACP
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
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
 * The attachment-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_attachments extends BS_ACP_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_DELETE_ATTACHMENTS,'delete');
		$renderer->add_breadcrumb($locale->lang('acpmod_attachments'),BS_URL::build_acpmod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$attachments = $this->_get_attachments();
		$files = $this->_get_files();
		
		$delete = $input->get_var('delete','post');
		if($delete != null)
		{
			$ids = $input->get_var('delete','post');
			$paths = FWS_Array_Utils::advanced_implode(', ',$ids);
			$url = BS_URL::get_acpmod_url();
			$url->set('ids',$ids);
			$url->set('at',BS_ACP_ACTION_DELETE_ATTACHMENTS);
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_files_question'),$paths),
				$url->to_url(),
				BS_URL::build_acpmod_url()
			);
		}

		$search = $input->get_var('search','get',FWS_Input::STRING);
		if($search != '')
		{
			foreach($files as $pos => $file)
			{
				if(stripos($file,$search) !== false)
					continue;
				
				$data = $attachments->get_element('uploads/'.$file);
				if(stripos($data['user_name'],$search) !== false)
					continue;
				if(stripos($data['name'],$search) !== false)
					continue;
				
				unset($files[$pos]);
			}
		}
		
		$end = 15;
		$num = count($files);
		$pagination = new BS_ACP_Pagination($end,$num);
		$site = $pagination->get_page();
		$start = $pagination->get_start();
		
		$aurl = BS_URL::get_frontend_url('redirect');
		$aurl->set(BS_URL_LOC,'show_post');
		
		$tplatt = array();
		$now = time();
		$index = 0;
		$failed = '';
		foreach($files as $filename)
		{
			if($index >= $start && $index < ($start + $end))
			{
				$is_db_attachment = false;

				$data = $attachments->get_element('uploads/'.$filename);
				$orig_data = null;

				$ext = FWS_FileUtils::get_extension($filename);

				// check if it is a thumbnail
				if($data == null && preg_match('/.+_thumb\.(jpeg|jpg|png)$/i',$filename))
				{
					$pos = FWS_String::strpos($filename,'_thumb');
					$startp = FWS_String::substr($filename,0,$pos);
					$target = FWS_String::strtolower('uploads/'.$startp.'.'.$ext);

					// does the original picture exist?
					foreach($attachments as $dba)
					{
						if(FWS_String::strtolower($dba['attachment_path']) == $target)
						{
							$orig_data = &$dba;
							$is_db_attachment = true;
							$attachments->rewind();
							break;
						}
					}
				}
				else if($data != null)
					$is_db_attachment = true;

				$filesize = number_format($data['attachment_size'],0,',','.');

				$attachment_url = '';
				$owner_name = '';
				$topic = '';

				$icon = $functions->get_attachment_icon($ext);
				$title = '<img src="'.$icon.'" alt="'.$ext.'" /> '.$filename;

				// does the attachment exist in the database?
				if($is_db_attachment)
				{
					if($orig_data != null)
						$d = &$orig_data;
					else
						$d = &$data;

					$owner_name = $d['user_name'];

					if($d['post_id'] != '')
					{
						$attachment_url = $aurl->set(BS_URL_ID,$d['post_id'])->to_url();
						list($td,$tc) = BS_TopicUtils::get_displayed_name($d['name'],25);
						$topic = '<a title="'.$tc.'" target="_blank"';
						$topic .= ' href="'.$attachment_url.'">'.$td.'</a>';
					}
					else
						$topic = '<i>'.$locale->lang('pm').'</i>';
				}
				else
				{
					$last_mod = filemtime('uploads/'.$filename);
					$title .= '<br /><div class="a_desc">'.$locale->lang('uploaded').': ';
					if($last_mod > 0)
						$title .= FWS_Date::get_date($last_mod);
					else
						$title .= $locale->lang('notavailable');
					$title .= '</div>';

					// give the user a short periode of time to add an attachment :)
					if(($now - $last_mod) > BS_ATTACHMENT_TIMEOUT)
						$failed .= ($index - $start).',';
				}

				// make thumbnails italic
				if($orig_data != null)
				{
					$title = '<i>'.$title.'</i>';
					$topic = '<i>'.$topic.'</i>';
					$owner_name = '<i>'.$owner_name.'</i>';
					$filesize = '<i>'.number_format(filesize('uploads/'.$filename),0,',','.').'</i>';
					$filename = '<i>'.$filename.'</i>';
				}

				$tplatt[] = array(
					'is_db_attachment' => $is_db_attachment,
					'title' => $title,
					'filename' => $filename,
					'org_filename' => strip_tags($filename),
					'attachment_url' => $attachment_url,
					'filesize' => $filesize,
					'topic' => $topic,
					'owner_name' => BS_ACP_Utils::get_userlink($data['poster_id'],$owner_name)
				);
			}
			$index++;
		}
		
		$tpl->add_variable_ref('attachments',$tplatt);
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'failed' => $failed,
			'site' => $site,
			'search_url' => 'admin.php',
			'hidden' => $hidden,
			'search_val' => $search
		));

		$murl = BS_URL::get_acpmod_url();
		$murl->set('search',$search);
		$pagination->populate_tpl($murl);
	}
	
	/**
	 * Collects all files in the uploads-directory and returns them
	 * 
	 * @return array all files
	 */
	private function _get_files()
	{
		$files = array();
		$dir = opendir(FWS_Path::server_app().'uploads');
		while($file = readdir($dir))
		{
			if($file != '.' && $file != '..' && $file != '.htaccess')
				$files[] = $file;
		}
		closedir($dir);
		
		asort($files);
		return $files;
	}

	/**
	 * initializes the attachment-cache
	 * 
	 * @return FWS_Array_2Dim the attachments
	 */
	private function _get_attachments()
	{
		$content = array();
		foreach(BS_DAO::get_attachments()->get_all_with_names() as $data)
			$content[$data['attachment_path']] = $data;

		return new FWS_Array_2Dim($content);
	}
}
?>