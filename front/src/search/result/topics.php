<?php
/**
 * Contains the topics-search-result-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The search-result displayed as topics
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Result_Topics extends FWS_Object implements BS_Front_Search_Result
{
	public function get_name()
	{
		return 'topics';
	}
	
	public function display_result($search,$request)
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		
		/* @var $search BS_Front_Search_Manager */
		/* @var $request BS_Front_Search_Request */
		
		list($order,$ad) = $request->get_order();
		
		$ids = $search->get_result_ids();
		$idstr = implode(',',$ids);
		if($idstr == '')
			return;
		
		$end = $cfg['threads_per_page'];
		$pagination = new BS_Pagination($end,count($ids));
		$murl = BS_URL::get_sub_url(0,'pmsearch');
		$murl->set(BS_URL_ID,$search->get_search_id());
		$murl->set(BS_URL_ORDER,$order);
		$murl->set(BS_URL_AD,$ad);
		$murl->set(BS_URL_MODE,$request->get_name());
		foreach($request->get_url_params() as $name => $value)
			$murl->set($name,$value);
		$small_page_split = $pagination->get_small($murl);

		$public_url = clone $murl;
		$public_url->remove(BS_URL_ID);
		$public_url->remove(BS_URL_SITE);
		$public_url->set(BS_URL_LOC,$this->get_name());

		$sql = ' t.id IN ('.$idstr.') AND moved_tid = 0';

		// display the topics
		$topics = new BS_Front_Topics($request->get_title($search),$sql,$order,$ad,$end);
		$topics->set_left_content($locale->lang('page').' '.$small_page_split);
		$topics->set_right_content('<a href="'.$public_url->to_url().'"><img 
						src="'.$user->get_theme_item_path('images/world.gif').'"
						alt="'.$locale->lang('publish_search_result').'"
						title="'.$locale->lang('publish_search_result').'" /></a>');
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_show_relevance(true);
		$topics->set_show_forum(true);
		$topics->set_middle_width(50);
		$topics->set_keywords($request->get_highlight_keywords());
		$topics->add_topics();
		
		$pagination->populate_tpl($murl);
	}
	
	public function get_template()
	{
		return 'search_result_topics.htm';
	}
	
	public function get_noresults_message()
	{
		$locale = FWS_Props::get()->locale();

		return $locale->lang('no_topics_found');
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>