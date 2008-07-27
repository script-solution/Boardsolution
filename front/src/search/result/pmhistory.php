<?php
/**
 * Contains the pm-history-search-result-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The search-result for the PM-history
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Result_PMHistory extends PLIB_Object implements BS_Front_Search_Result
{
	public function get_name()
	{
		return 'pm_history';
	}

	public function get_template()
	{
		return 'inc_message_review.htm';
	}
	
	public function display_result($search,$request)
	{
		$tpl = PLIB_Props::get()->tpl();
		$functions = PLIB_Props::get()->functions();
		$user = PLIB_Props::get()->user();
		$url = PLIB_Props::get()->url();

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

		$pmlist = BS_DAO::get_pms()->get_pms_of_user_by_ids(
			$user->get_user_id(),$ids,$sql_order,$ad,$pagination->get_start(),$end
		);
		$this->_pm_subject = preg_replace('/^(RE: )*(.*)/','\\2',$pmlist[0]['pm_title']);

		$tpl->set_template('inc_message_review.htm');
		
		$enable_bbcode = BS_PostingUtils::get_instance()->get_message_option('enable_bbcode');
		$enable_smileys = BS_PostingUtils::get_instance()->get_message_option('enable_smileys');
		
		$keywords = $request->get_highlight_keywords();
		$messages = array();
		
		if(count($keywords) > 0)
			$kwhl = new PLIB_KeywordHighlighter($keywords,'<span class="bs_highlight">');
		
		foreach($pmlist as $data)
		{
			$bbcode = new BS_BBCode_Parser($data['pm_text'],'posts',$enable_bbcode,$enable_smileys);
			$text = $bbcode->get_message_for_output();

			$title = $data['pm_title'];
			$complete_title = '';
			if(PLIB_String::strlen($title) > BS_MAX_PM_TITLE_LEN)
			{
				$complete_title = $title;
				$title = '<span title="'.$complete_title.'">'.PLIB_String::substr($title,0,BS_MAX_PM_TITLE_LEN) . ' ...</span>';
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

			$messages[] = array(
				'show_quote' => false,
				'quote_post_url' => '',
				'text' => $text,
				'user_name' => $username,
				'subject' => $title,
				'date' => PLIB_Date::get_date($data['pm_date'],true,true),
				'post_id' => $data['id']
			);
		}

		$murl = $url->get_url(
			0,'&amp;'.BS_URL_LOC.'=pmsearch'.'&amp;'.BS_URL_ID.'='.$search->get_search_id()
				.'&amp;'.BS_URL_ORDER.'='.$order.'&amp;'.BS_URL_AD.'='.$ad
				.'&amp;'.BS_URL_MODE.'='.$request->get_name().'&amp;'.BS_URL_SITE.'={d}'
		);
		foreach($request->get_url_params() as $name => $value)
			$murl .= '&amp;'.$name.'='.$value;
		
		$functions->add_pagination($pagination,$murl);
		
		$tpl->add_array('messages',$messages);
		$tpl->add_variables(array(
			'page_split' => true,
			'linewrap' => false,
			'topic_title' => $request->get_title($search),
			'limit_height' => false
		));
		
		$tpl->restore_template();
	}
	
	public function get_noresults_message()
	{
		$locale = PLIB_Props::get()->locale();

		return $locale->lang('no_pms_found');
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>