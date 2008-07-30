<?php
/**
 * Contains the pms-search-result-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		$murl = BS_URL::get_url(
			0,'&'.BS_URL_LOC.'=pmsearch&'.BS_URL_ID.'='.$search->get_search_id()
				.'&'.BS_URL_ORDER.'='.$order.'&'.BS_URL_AD.'='.$ad
				.'&'.BS_URL_MODE.'='.$request->get_name(),'&'
		);
		foreach($request->get_url_params() as $name => $value)
			$murl .= '&'.$name.'='.$value;
		
		$tpl->set_template('inc_userprofile_pmjs.htm');
		$tpl->add_variables(array(
			'pm_target_url' => $murl.'&'.BS_URL_SITE.'='.$site,
			'delete_add' => '&'.BS_URL_MODE.'=delete',
			'at_mark_read' => '',
			'at_mark_unread' => ''
		));
		$tpl->restore_template();

		$tpl->set_template('userprofile_pmsearch_result.htm');
		
		$tpl->add_variables(array(
			'target_url' => str_replace('&','&amp;',$murl).'&amp;'.BS_URL_SITE.'='.$site,
			'title' => $request->get_title($search)
		));

		$keywords = $request->get_highlight_keywords();
		$hl = '';
		if(count($keywords) > 0)
		{
			$kwhl = new FWS_KeywordHighlighter($keywords,'<span class="bs_highlight">');
			$urlkw = '';
			foreach($keywords as $kw)
				$urlkw .= '"'.$kw.'" ';
			$urlkw = rtrim($urlkw);
			$hl = '&amp;'.BS_URL_HL.'='.urlencode($urlkw);
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
				$username = BS_UserUtils::get_instance()->get_link(
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
				'details_link' => BS_URL::get_url(
					0,'&amp;'.BS_URL_LOC.'=pmdetails&amp;'.BS_URL_ID.'='.$data['id'].$hl
				),
				'status_title' => $status_title,
				'status_picture' => $status_picture,
				'sender' => $username,
				'pm_id' => $data['id'],
				'folder' => $data['pm_type'] == 'inbox' ? $locale->lang('pminbox')
					: $locale->lang('pmoutbox')
			);
		}
		
		$tpl->add_array('pms',$pms);
		
		$purl = str_replace('&','&amp;',$murl).'&amp;'.BS_URL_SITE.'={d}';
		$functions->add_pagination($pagination,$purl);
		
		$tpl->restore_template();
	}
	
	public function get_noresults_message()
	{
		$locale = FWS_Props::get()->locale();

		return $locale->lang('no_pms_found');
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>