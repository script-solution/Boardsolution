<?php
/**
 * Contains the pmdetails-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pmdetails submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_pmdetails extends BS_Front_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$input = PLIB_Props::get()->input();
		$renderer = $doc->use_default_renderer();

		$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$renderer->add_breadcrumb(
			$locale->lang('details'),
			$url->get_url(0,'&amp;'.BS_URL_LOC.'=pmdetails&amp;'.BS_URL_ID.'='.$id)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		$helper = BS_Front_Module_UserProfile_Helper::get_instance();
		if($helper->get_pm_permission() < 1)
		{
			$this->report_error(PLIB_Document_Messages::NO_ACCESS);
			return;
		}

		// pm-id valid?
		$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
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

		// mark it read?
		if($data['pm_read'] == 0)
			BS_DAO::get_pms()->set_read_flag(array($id),$user->get_user_id(),1);

		if($data['pm_type'] == 'inbox' && $data['sender_name'] != '')
		{
			$user_name = $locale->lang('From').' ';
			$user_name .= BS_UserUtils::get_instance()->get_link(
				$data['sender_id'],$data['sender_name'],$data['sender_user_group']
			);
		}
		else if($data['pm_type'] == 'inbox')
			$user_name = 'Boardsolution';
		else
		{
			$user_name = $locale->lang('pm_to').' ';
			$user_name .= BS_UserUtils::get_instance()->get_link(
				$data['receiver_id'],$data['receiver_name'],$data['receiver_user_group']
			);
		}

		$enable_bbcode = BS_PostingUtils::get_instance()->get_message_option('enable_bbcode');
		$enable_smileys = BS_PostingUtils::get_instance()->get_message_option('enable_smileys');
		$bbcode = new BS_BBCode_Parser($data['pm_text'],'posts',$enable_bbcode,$enable_smileys);
		$text = $bbcode->get_message_for_output();

		$keywords = $functions->get_search_keywords();
		if($keywords != null)
		{
			$kwhl = new PLIB_KeywordHighlighter($keywords,'<span class="bs_highlight">');
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


			$avatar_path = BS_UserUtils::get_instance()->get_avatar_path($avatar_data);
			if($avatar_path != '')
				$avatar = '<img src="'.$avatar_path.'" alt="" />';
		}

		// show top
		$add = '&amp;'.BS_URL_ID.'='.$id.'&amp;'.BS_URL_KW.'='.$data['pm_type'];
		$tpl->add_variables(array(
			'date' => PLIB_Date::get_date($data['pm_date']),
			'text' => $text,
			'type' => $data['pm_type'],
			'subject' => $data['pm_title'],
			'back' => $url->get_url(
				'redirect','&amp;'.BS_URL_LOC.'=pm_navigate&amp;'.BS_URL_MODE.'=back'.$add
			),
			'forward' => $url->get_url(
				'redirect','&amp;'.BS_URL_LOC.'=pm_navigate&amp;'.BS_URL_MODE.'=forward'.$add
			),
			'user_name' => $user_name,
			'avatar' => $avatar
		));
		
		$attachments = array();

		if($data['attachment_count'] > 0)
		{
			// show attachments
			foreach(BS_DAO::get_attachments()->get_by_pmid($data['id']) as $adata)
			{
				$ext = PLIB_FileUtils::get_extension($adata['attachment_path']);
				$attachment_url = $url->get_url('download','&amp;'.BS_URL_ID.'='.$adata['id']);
	
				$is_image = $cfg['attachments_images_show'] == 1 &&
					($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png');
	
				if($is_image)
		    {
		    	list($att_width,$att_height) = explode('x',$cfg['attachments_images_size']);
		    	$params = '&amp;path='.$adata['attachment_path'].'&amp;width=';
		    	$params .= $att_width.'&amp;height='.$att_height.'&amp;method=';
		    	$params .= $cfg['attachments_images_resize_method'];
		    	$image_url = $url->get_url('thumbnail',$params);
		      $image_title = sprintf(
		      	$locale->lang('download_image'),basename($adata['attachment_path'])
		      );
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

		$tpl->add_array('attachments',$attachments);

		// show bottom
		$uid = ($data['pm_type'] == 'inbox') ? $data['sender_id'] : $data['receiver_id'];
		$params = '&amp;'.BS_URL_LOC.'=pmbanlist&amp;'.BS_URL_AT.'='.BS_ACTION_BAN_USER;
		$params .= '&amp;'.BS_URL_ID.'='.$uid;
		$tpl->add_variables(array(
			'id' => $data['id'],
			'ban_user_url' => $url->get_url('userprofile',$params,'&amp;',true),
			'show_reply_btn' => $data['pm_type'] == 'inbox'
		));
	}
}
?>