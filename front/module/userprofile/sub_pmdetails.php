<?php
/**
 * Contains the pmdetails-userprofile-submodule
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
 * The pmdetails submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_pmdetails extends BS_Front_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$input = FWS_Props::get()->input();
		$renderer = $doc->use_default_renderer();
		
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$url = BS_URL::get_sub_url();
		$url->set(BS_URL_ID,$id);
		$renderer->add_breadcrumb($locale->lang('details'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$cfg = FWS_Props::get()->cfg();
		$tpl = FWS_Props::get()->tpl();
		$helper = BS_Front_Module_UserProfile_Helper::get_instance();
		if($helper->get_pm_permission() < 1)
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}

		// pm-id valid?
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		if($id == null)
		{
			$this->report_error();
			return;
		}

		// grab the data from the db
		$data = BS_DAO::get_pms()->get_pm_details($id,$user->get_user_id());
		if($data === false)
		{
			$this->report_error();
			return;
		}
		
		$helper->add_pm_delete_message();

		// mark it read?
		if($data['pm_read'] == 0)
		{
			BS_DAO::get_pms()->set_read_flag(array($id),$user->get_user_id(),1);
			$user->set_profile_val('unread_pms',$user->get_profile_val('unread_pms') - 1);
		}

		if($data['pm_type'] == 'inbox' && $data['sender_name'] != '')
		{
			$user_name = $locale->lang('From').' ';
			$user_name .= BS_UserUtils::get_link(
				$data['sender_id'],$data['sender_name'],$data['sender_user_group']
			);
		}
		else if($data['pm_type'] == 'inbox')
			$user_name = 'Boardsolution';
		else
		{
			$user_name = $locale->lang('pm_to').' ';
			$user_name .= BS_UserUtils::get_link(
				$data['receiver_id'],$data['receiver_name'],$data['receiver_user_group']
			);
		}

		$enable_bbcode = BS_PostingUtils::get_message_option('enable_bbcode');
		$enable_smileys = BS_PostingUtils::get_message_option('enable_smileys');
		$bbcode = new BS_BBCode_Parser($data['pm_text'],'posts',$enable_bbcode,$enable_smileys);
		$text = $bbcode->get_message_for_output();

		$keywords = BS_Front_Search_Utils::get_keywords();
		if($keywords != null)
		{
			$kwhl = new FWS_KeywordHighlighter($keywords,'<span class="bs_highlight">');
			$text = $kwhl->highlight($text);
		}

		// determine avatar
		$avatar = '';
		if($cfg['enable_avatars'] == 1)
		{
			if($data['pm_type'] == 'inbox')
			{
				$avatar_data = array(
					'av_pfad' => $data['sender_avatar'],
					'aowner' => $data['sender_av_owner'],
					'post_user' => $data['sender_id']
				);
			}
			else
			{
				$avatar_data = array(
					'av_pfad' => $data['receiver_avatar'],
					'aowner' => $data['receiver_av_owner'],
					'post_user' => $data['receiver_id']
				);
			}


			$avatar_path = BS_UserUtils::get_avatar_path($avatar_data);
			if($avatar_path != '')
				$avatar = '<img src="'.$avatar_path.'" alt="" />';
		}

		$rurl = BS_URL::get_mod_url('redirect');
		$rurl->set(BS_URL_LOC,'pm_navigate');
		$rurl->set(BS_URL_ID,$id);
		$rurl->set(BS_URL_KW,$data['pm_type']);
		
		// show top
		$tpl->add_variables(array(
			'date' => FWS_Date::get_date($data['pm_date']),
			'text' => $text,
			'type' => $data['pm_type'],
			'subject' => $data['pm_title'],
			'back' => $rurl->set(BS_URL_MODE,'back')->to_url(),
			'forward' => $rurl->set(BS_URL_MODE,'forward')->to_url(),
			'user_name' => $user_name,
			'avatar' => $avatar
		));
		
		$attachments = array();

		if($data['attachment_count'] > 0)
		{
			$durl = BS_URL::get_standalone_url('download');
			
			list($att_width,$att_height) = explode('x',$cfg['attachments_images_size']);
			$turl = BS_URL::get_standalone_url('thumbnail');
			$turl->set('width',$att_width);
			$turl->set('height',$att_height);
			$turl->set('method',$cfg['attachments_images_resize_method']);
			
			// show attachments
			foreach(BS_DAO::get_attachments()->get_by_pmid($data['id']) as $adata)
			{
				$ext = FWS_FileUtils::get_extension($adata['attachment_path']);
				$attachment_url = $durl->set(BS_URL_ID,$adata['id'])->to_url();
	
				$is_image = $cfg['attachments_images_show'] == 1 &&
					($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png');
	
				if($is_image)
				{
					$turl->set('path',$adata['attachment_path']);
					$image_url = $turl->to_url();
					$image_title = sprintf(
					$locale->lang('download_image'),basename($adata['attachment_path']));
				}
				else
				{
					$image_url = '';
					$image_title = '';
				}
				$file_icon = $functions->get_attachment_icon($ext);
				$attachment_name = basename($adata['attachment_path']);

				$attachments[] = array(
					'is_image' => $is_image,
					'fileicon' => $file_icon,
					'image_url' => $image_url,
					'image_title' => $image_title,
					'attachment_url' => $attachment_url,
					'attachment_size' => number_format($adata['attachment_size'],0,',','.'),
					'attachment_name' => $attachment_name
				);
			}
		}

		$tpl->add_variable_ref('attachments',$attachments);

		// show bottom
		$durl = BS_URL::get_sub_url();
		$durl->set(BS_URL_ID,$data['id']);
		$durl->set(BS_URL_DEL,$data['id']);
		$durl->set(BS_URL_MODE,'delete');
		
		$tpl->add_variables(array(
			'id' => $data['id'],
			'delete_url' => $durl->to_url(),
			'show_reply_btn' => $data['pm_type'] == 'inbox' && $user_name != 'Boardsolution'
		));
	}
}
?>