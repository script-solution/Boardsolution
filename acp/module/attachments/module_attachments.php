<?php
/**
 * Contains the attachment module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		$doc->add_action(BS_ACP_ACTION_DELETE_ATTACHMENTS,'delete');
		$doc->add_breadcrumb($locale->lang('acpmod_attachments'),$url->get_acpmod_url());
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$functions = PLIB_Props::get()->functions();
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		$attachments = $this->_get_attachments();
		$files = $this->_get_files();
		
		$delete = $input->get_var('delete','post');
		if($delete != null)
		{
			$ids = $input->get_var('delete','post');
			$paths = PLIB_Array_Utils::advanced_implode('|',$ids);
			$functions->add_delete_message(
				sprintf($locale->lang('delete_files_question'),$paths),
				$url->get_acpmod_url(0,
					'&amp;at='.BS_ACP_ACTION_DELETE_ATTACHMENTS.'&amp;ids='.implode(',',$ids)
				),
				$url->get_acpmod_url()
			);
		}

		$search = $input->get_var('search','get',PLIB_Input::STRING);
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

				$ext = PLIB_FileUtils::get_extension($filename);

				// check if it is a thumbnail
				if($data == null && preg_match('/.+_thumb\.(jpeg|jpg|png)$/',$filename))
				{
					$pos = PLIB_String::strpos($filename,'_thumb');
					$startp = PLIB_String::substr($filename,0,$pos);
					$target = PLIB_String::strtolower('uploads/'.$startp.'.'.$ext);

					// does the original picture exist?
					foreach($attachments as $dba)
					{
						if(PLIB_String::strtolower($dba['attachment_path']) == $target)
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
						$attachment_url = $url->get_frontend_url(
							'&amp;'.BS_URL_ACTION.'=redirect&amp;'.BS_URL_LOC.'=show_post&amp;'
							.BS_URL_ID.'='.$d['post_id']
						);
						$t = BS_TopicUtils::get_instance()->get_displayed_name($d['name'],25);
						$topic = '<a title="'.$t['complete'].'" target="_blank"';
						$topic .= ' href="'.$attachment_url.'">'.$t['displayed'].'</a>';
					}
					else
						$topic = '<i>'.$locale->lang('pm').'</i>';
				}
				else
				{
					$last_mod = filemtime('uploads/'.$filename);
					$title .= '<br /><div class="a_desc">'.$locale->lang('uploaded').': ';
					if($last_mod > 0)
						$title .= PLIB_Date::get_date($last_mod);
					else
						$title .= $locale->lang('notavailable');
					$title .= '</div>';

					// give the user a short periode of time to add an attachment :)
					if(($now - $last_mod) > BS_ATTACHMENT_TIMEOUT)
						$failed .= $index.',';
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
					'owner_name' => BS_ACP_Utils::get_instance()->get_userlink($data['poster_id'],$owner_name)
				);
			}
			$index++;
		}
		
		$tpl->add_array('attachments',$tplatt);
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'failed' => $failed,
			'site' => $site,
			'search_url' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));

		$murl = $url->get_acpmod_url(0,'&amp;search='.$search.'&amp;site={d}');
		$functions->add_pagination($pagination,$murl);
	}
	
	/**
	 * Collects all files in the uploads-directory and returns them
	 * 
	 * @return array all files
	 */
	private function _get_files()
	{
		$files = array();
		$dir = opendir(PLIB_Path::server_app().'uploads');
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
	 * @return PLIB_Array_2Dim the attachments
	 */
	private function _get_attachments()
	{
		$content = array();
		foreach(BS_DAO::get_attachments()->get_all_with_names() as $data)
			$content[$data['attachment_path']] = $data;

		return new PLIB_Array_2Dim($content);
	}
}
?>