<?php
/**
 * Contains the forums-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The forums-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_forums extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_default_renderer()->set_robots_value('index,follow');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$tpl = FWS_Props::get()->tpl();
		$auth = FWS_Props::get()->auth();

		// display latest topics at the top?
		$display_lt_top = strpos($cfg['current_topic_loc'],'top') !== false;
		$display_lt_bottom = strpos($cfg['current_topic_loc'],'bottom') !== false;
		$display_lt = $cfg['current_topic_enable'] == 1 && ($display_lt_bottom || $display_lt_top);
		if($display_lt)
			BS_Front_TopicFactory::add_latest_topics_small();
		
		$tpl->add_variables(array(
			'latest_topics_top' => $display_lt && $display_lt_top,
			'latest_topics_bottom' => $display_lt && $display_lt_bottom,
			'forum_list' => BS_ForumUtils::get_forum_list(0)
		));

		// show bottom
		$view_useronline = $auth->has_global_permission('view_useronline_list');
		if($view_useronline)
			BS_Front_OnlineUtils::add_currently_online('forums');
		
		$tpl->add_variables(array(
			'view_useronline_list' => $view_useronline,
			'display_ministats' => $cfg['display_ministats'] == 1
		));
		
		if($cfg['display_ministats'] == 1)
			$this->_add_forum_bottom();
	}
	
	/**
	 * Adds the forum-bottom
	 */
	private function _add_forum_bottom()
	{
		$functions = FWS_Props::get()->functions();
		$locale = FWS_Props::get()->locale();
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();
		$birthdays = BS_Front_EventUtils::get_todays_birthdays();
		$events = BS_Front_EventUtils::get_current_events();
		
		$stats_data = $functions->get_stats(false);
		
		$total = $stats_data['total_forums'].' '.($stats_data['total_forums'] == 1 ? $locale->lang('forum') : $locale->lang('forums'));
		$total .= ', '.$stats_data['total_topics'].' '.(($stats_data['total_topics'] == 1) ? $locale->lang('thread') : $locale->lang('threads'));
		$total .= ', '.$stats_data['posts_total'].' '.(($stats_data['posts_total'] == 1) ? $locale->lang('post') : $locale->lang('posts'));
		$total .= ', '.$stats_data['total_users'].' '.(($stats_data['total_users'] == 1) ? $locale->lang('registereduser1') : $locale->lang('registereduser'));
		
		if($input->get_var(BS_URL_LOC,'get',FWS_Input::STRING) == 'clap_ministats')
			$functions->clap_area('ministats');
	
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_LOC,'clap_ministats');	
		$clap_data = $functions->get_clap_data('ministats',$url->to_url());
		
		$nm = BS_DAO::get_profile()->get_newest_user();
		$tpl->add_variable_ref('stats_data',$stats_data);
		$tpl->add_variables(array(
			'show_current_topics_link' => !$cfg['current_topic_enable'] ||
				strpos($cfg['current_topic_loc'],'portal') !== false,
			'clap_image' => $clap_data['link'],
			'ministats_params' => $clap_data['divparams'],
			'cookie_prefix' => BS_COOKIE_PREFIX,
			'current_events_desc' => sprintf($locale->lang('current_events_desc'),BS_MINISTATS_EVENT_DAYS),
			'statistics_ins' => $total,
			'events' => $events,
			'birthdays' => $birthdays,
			'lastlogin' => BS_Front_OnlineUtils::get_last_activity(),
			'newest_member' => BS_UserUtils::get_link($nm['id'],$nm['user_name'],$nm['user_group'])
		));
	}
}
?>