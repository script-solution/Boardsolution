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
final class BS_Front_Search_Result_PMHistory extends FWS_Object implements BS_Front_Search_Result
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
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();
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

		$tpl->set_template('inc_message_review.htm');
		
		$enable_bbcode = BS_PostingUtils::get_message_option('enable_bbcode');
		$enable_smileys = BS_PostingUtils::get_message_option('enable_smileys');
		
		$keywords = $request->get_highlight_keywords();
		$messages = array();
		
		if(count($keywords) > 0)
			$kwhl = new FWS_KeywordHighlighter($keywords,'<span class="bs_highlight">');
		
		foreach($pmlist as $data)
		{
			$bbcode = new BS_BBCode_Parser($data['pm_text'],'posts',$enable_bbcode,$enable_smileys);
			$text = $bbcode->get_message_for_output();

			$title = $data['pm_title'];
			$complete_title = '';
			if(FWS_String::strlen($title) > BS_MAX_PM_TITLE_LEN)
			{
				$complete_title = $title;
				$title = '<span title="'.$complete_title.'">'.FWS_String::substr($title,0,BS_MAX_PM_TITLE_LEN) . ' ...</span>';
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

			$messages[] = array(
				'show_quote' => false,
				'quote_post_url' => '',
				'text' => $text,
				'user_name' => $username,
				'subject' => $title,
				'date' => FWS_Date::get_date($data['pm_date'],true,true),
				'post_id' => $data['id']
			);
		}

		$murl = BS_URL::get_sub_url(0,'pmsearch');
		$murl->set(BS_URL_ID,$search->get_search_id());
		$murl->set(BS_URL_ORDER,$order);
		$murl->set(BS_URL_AD,$ad);
		$murl->set(BS_URL_MODE,$request->get_name());
		foreach($request->get_url_params() as $name => $value)
			$murl->set($name,$value);
		
		$pagination->populate_tpl($murl);
		
		$tpl->add_variable_ref('messages',$messages);
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
		$locale = FWS_Props::get()->locale();

		return $locale->lang('no_pms_found');
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>