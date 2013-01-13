<?php
/**
 * Contains the pms-search-result-class
 * 
 * @package			Boardsolution
 * @subpackage	front.src.search
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
 * The search-result for the PMs
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Result_PMs extends FWS_Object implements BS_Front_Search_Result
{
	public function get_name()
	{
		return 'pms';
	}

	public function get_template()
	{
		return 'userprofile_pmsearch_result.htm';
	}

	public function display_result($search,$request)
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$user = FWS_Props::get()->user();
		$img_pm_read = $user->get_theme_item_path('images/unread/pm_read.gif');
		$img_pm_unread = $user->get_theme_item_path('images/unread/pm_unread.gif');

		list($order,$ad) = $request->get_order();
		$ids = $search->get_result_ids();
		$num = count($ids);
		$end = BS_PMS_FOLDER_PER_PAGE;
		$pagination = new BS_Pagination($end,$num);

		switch($order)
		{
			case 'subject':
				$sql_order = 'p.pm_title';
				break;
			case 'folder':
				$sql_order = 'p.pm_type';
				break;
			default:
				$sql_order = 'p.id';
				break;
		}

		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
		$murl = BS_URL::get_sub_url(0,'pmsearch');
		$murl->set(BS_URL_ID,$search->get_search_id());
		$murl->set(BS_URL_ORDER,$order);
		$murl->set(BS_URL_AD,$ad);
		$murl->set(BS_URL_MODE,$request->get_name());
		foreach($request->get_url_params() as $name => $value)
			$murl->set($name,$value);
		
		$murl->set_separator('&');
		$tpl->set_template('inc_userprofile_pmjs.htm');
		$tpl->add_variables(array(
			'pm_target_url' => $murl->set(BS_URL_SITE,$site)->to_url(),
			'delete_add' => '&'.BS_URL_MODE.'=delete',
			'at_mark_read' => '',
			'at_mark_unread' => ''
		));
		$tpl->restore_template();

		$tpl->set_template('userprofile_pmsearch_result.htm');
		
		$murl->set_separator('&amp;');
		$tpl->add_variables(array(
			'target_url' => $murl->to_url(),
			'title' => $request->get_title($search)
		));

		$keywords = $request->get_highlight_keywords();
		
		$durl = BS_URL::get_sub_url('userprofile','pmdetails');
		if(count($keywords) > 0)
		{
			$kwhl = new FWS_KeywordHighlighter($keywords,'<span class="bs_highlight">');
			$urlkw = '';
			foreach($keywords as $kw)
				$urlkw .= '"'.$kw.'" ';
			$urlkw = rtrim($urlkw);
			$durl->set(BS_URL_HL,$urlkw);
		}
		
		$pms = array();
		$pmlist = BS_DAO::get_pms()->get_pms_of_user_by_ids(
			$user->get_user_id(),$ids,$sql_order,$ad,$pagination->get_start(),$end
		);
		foreach($pmlist as $data)
		{
			$title = $data['pm_title'];
			$complete_title = '';
			if(FWS_String::strlen($title) > BS_MAX_PM_TITLE_LEN)
			{
				$complete_title = $title;
				$title = FWS_String::substr($title,0,BS_MAX_PM_TITLE_LEN) . ' ...';
			}

			if(count($keywords) > 0)
				$title = $kwhl->highlight($title);

			if($data['user_name'] != '')
			{
				$username = BS_UserUtils::get_link(
					$data['sender_id'],$data['user_name'],$data['user_group']
				);
			}
			else
				$username = 'Boardsolution';

			if($data['pm_read'] == 0 && $data['pm_type'] == 'inbox')
			{
				$status_picture = $img_pm_unread;
				$status_title = $locale->lang('unread_pm');
			}
			else
			{
				$status_picture = $img_pm_read;
				$status_title = $locale->lang('read_pm');
			}

			$pms[] = array(
				'prefix' => $functions->get_pm_attachment_prefix($data['attachment_count']),
				'pm_title' => $title,
				'complete_title' => $complete_title,
				'date' => FWS_Date::get_date($data['pm_date']),
				'details_link' => $durl->set(BS_URL_ID,$data['id'])->to_url(),
				'status_title' => $status_title,
				'status_picture' => $status_picture,
				'sender' => $username,
				'pm_id' => $data['id'],
				'folder' => $data['pm_type'] == 'inbox' ? $locale->lang('pminbox')
					: $locale->lang('pmoutbox')
			);
		}
		
		$tpl->add_variable_ref('pms',$pms);
		
		$pagination->populate_tpl($murl);
		
		$tpl->restore_template();
	}
	
	public function get_noresults_message()
	{
		$locale = FWS_Props::get()->locale();

		return $locale->lang('no_pms_found');
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>