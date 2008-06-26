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
final class BS_Front_Search_Result_PMs extends PLIB_FullObject implements BS_Front_Search_Result
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
		$img_pm_read = $this->user->get_theme_item_path('images/unread/pm_read.gif');
		$img_pm_unread = $this->user->get_theme_item_path('images/unread/pm_unread.gif');

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

		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		$url = $this->url->get_url(
			0,'&'.BS_URL_LOC.'=pmsearch&'.BS_URL_ID.'='.$search->get_search_id()
				.'&'.BS_URL_ORDER.'='.$order.'&'.BS_URL_AD.'='.$ad
				.'&'.BS_URL_MODE.'='.$request->get_name(),'&'
		);
		foreach($request->get_url_params() as $name => $value)
			$url .= '&'.$name.'='.$value;
		
		$this->tpl->set_template('inc_userprofile_pmjs.htm');
		$this->tpl->add_variables(array(
			'pm_target_url' => $url.'&'.BS_URL_SITE.'='.$site,
			'delete_add' => '&'.BS_URL_MODE.'=delete',
			'at_mark_read' => '',
			'at_mark_unread' => ''
		));
		$this->tpl->restore_template();

		$this->tpl->set_template('userprofile_pmsearch_result.htm');
		
		$this->tpl->add_variables(array(
			'target_url' => str_replace('&','&amp;',$url).'&amp;'.BS_URL_SITE.'='.$site,
			'title' => $request->get_title($search)
		));

		$keywords = $request->get_highlight_keywords();
		$hl = '';
		if(count($keywords) > 0)
		{
			$kwhl = new PLIB_KeywordHighlighter($keywords,'<span class="bs_highlight">');
			$urlkw = '';
			foreach($keywords as $kw)
				$urlkw .= '"'.$kw.'" ';
			$urlkw = rtrim($urlkw);
			$hl = '&amp;'.BS_URL_HL.'='.urlencode($urlkw);
		}
		
		$pms = array();
		$pmlist = BS_DAO::get_pms()->get_pms_of_user_by_ids(
			$this->user->get_user_id(),$ids,$sql_order,$ad,$pagination->get_start(),$end
		);
		foreach($pmlist as $data)
		{
			$title = $data['pm_title'];
			$complete_title = '';
			if(PLIB_String::strlen($title) > BS_MAX_PM_TITLE_LEN)
			{
				$complete_title = $title;
				$title = PLIB_String::substr($title,0,BS_MAX_PM_TITLE_LEN) . ' ...';
			}

			if(count($keywords) > 0)
				$title = $kwhl->highlight($title);

			if($data['user_name'] != '')
			{
				$user = BS_UserUtils::get_instance()->get_link(
					$data['sender_id'],$data['user_name'],$data['user_group']
				);
			}
			else
				$user = 'Boardsolution';

			if($data['pm_read'] == 0 && $data['pm_type'] == 'inbox')
			{
				$status_picture = $img_pm_unread;
				$status_title = $this->locale->lang('unread_pm');
			}
			else
			{
				$status_picture = $img_pm_read;
				$status_title = $this->locale->lang('read_pm');
			}

			$pms[] = array(
				'prefix' => $this->functions->get_pm_attachment_prefix($data['attachment_count']),
				'pm_title' => $title,
				'complete_title' => $complete_title,
				'date' => PLIB_Date::get_date($data['pm_date']),
				'details_link' => $this->url->get_url(
					0,'&amp;'.BS_URL_LOC.'=pmdetails&amp;'.BS_URL_ID.'='.$data['id'].$hl
				),
				'status_title' => $status_title,
				'status_picture' => $status_picture,
				'sender' => $user,
				'pm_id' => $data['id'],
				'folder' => $data['pm_type'] == 'inbox' ? $this->locale->lang('pminbox')
					: $this->locale->lang('pmoutbox')
			);
		}
		
		$this->tpl->add_array('pms',$pms);
		
		$url = str_replace('&','&amp;',$url).'&amp;'.BS_URL_SITE.'={d}';
		$this->functions->add_pagination($pagination,$url);
		
		$this->tpl->restore_template();
	}
	
	public function get_noresults_message()
	{
		return $this->locale->lang('no_pms_found');
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>