<?php
/**
 * Contains the pm-history-search-result-class
 *
 * @version			$Id: pmhistory.php 724 2008-05-22 14:37:18Z nasmussen $
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
final class BS_Front_Search_Result_PMHistory extends PLIB_FullObject implements BS_Front_Search_Result
{
	public function get_name()
	{
		return 'pmhistory';
	}

	public function get_template()
	{
		return 'inc_message_review.htm';
	}
	
	public function display_result($search,$request)
	{
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
			$this->user->get_user_id(),$ids,$sql_order,$ad,$pagination->get_start(),$end
		);
		$this->_pm_subject = preg_replace('/^(RE: )*(.*)/','\\2',$pmlist[0]['pm_title']);

		$this->tpl->set_template('inc_message_review.htm');
		
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
				$user = BS_UserUtils::get_instance()->get_link(
					$data['sender_id'],$data['user_name'],$data['user_group']
				);
			}
			else
				$user = 'Boardsolution';

			$messages[] = array(
				'show_quote' => false,
				'quote_post_url' => '',
				'text' => $text,
				'user_name' => $user,
				'subject' => $title,
				'date' => PLIB_Date::get_date($data['pm_date'],true,true),
				'post_id' => $data['id']
			);
		}

		$url = $this->url->get_url(
			0,'&amp;'.BS_URL_LOC.'=pmsearch'.'&amp;'.BS_URL_ID.'='.$search->get_search_id()
				.'&amp;'.BS_URL_ORDER.'='.$order.'&amp;'.BS_URL_AD.'='.$ad
				.'&amp;'.BS_URL_MODE.'='.$request->get_name().'&amp;'.BS_URL_SITE.'={d}'
		);
		$this->functions->add_pagination($pagination,$url);
		
		$this->tpl->add_array('messages',$messages);
		$this->tpl->add_variables(array(
			'linewrap' => false,
			'topic_title' => $request->get_title($search),
			'limit_height' => false
		));
		
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