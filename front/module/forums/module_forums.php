<?php
/**
 * Contains the forums-module
 * 
 * @version			$Id: module_forums.php 802 2008-05-30 06:51:57Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The forums-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_forums extends BS_Front_Module
{
	public function run()
	{
		$forums = BS_ForumUtils::get_instance();

		// display latest topics at the top?
		$display_lt_top = strpos($this->cfg['current_topic_loc'],'top') !== false;
		$display_lt_bottom = strpos($this->cfg['current_topic_loc'],'bottom') !== false;
		$display_lt = $this->cfg['current_topic_enable'] == 1 && ($display_lt_bottom || $display_lt_top);
		if($display_lt)
		{
			$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
			BS_Front_TopicFactory::get_instance()->add_latest_topics_small($fid);
		}
		
		$this->tpl->add_variables(array(
			'latest_topics_top' => $display_lt && $display_lt_top,
			'latest_topics_bottom' => $display_lt && $display_lt_bottom,
			'forum_list' => $forums->get_forum_list(0)
		));

		// show bottom
		$this->tpl->add_variables(array(
			'online_list' => BS_Front_OnlineUtils::get_instance()->add_currently_online('forums'),
			'display_ministats' => $this->cfg['display_ministats'] == 1
		));
		
		if($this->cfg['display_ministats'] == 1)
			$this->_add_forum_bottom();
	}
	
	/**
	 * Adds the forum-bottom
	 */
	private function _add_forum_bottom()
	{
		$birthdays = BS_Front_EventUtils::get_instance()->get_todays_birthdays();
		$events = BS_Front_EventUtils::get_instance()->get_current_events();
		
		$stats_data = $this->functions->get_stats(false);
		
		$total = $stats_data['total_forums'].' '.($stats_data['total_forums'] == 1 ? $this->locale->lang('forum') : $this->locale->lang('forums'));
		$total .= ', '.$stats_data['total_topics'].' '.(($stats_data['total_topics'] == 1) ? $this->locale->lang('thread') : $this->locale->lang('threads'));
		$total .= ', '.$stats_data['posts_total'].' '.(($stats_data['posts_total'] == 1) ? $this->locale->lang('post') : $this->locale->lang('posts'));
		$total .= ', '.$stats_data['total_users'].' '.(($stats_data['total_users'] == 1) ? $this->locale->lang('registereduser1') : $this->locale->lang('registereduser'));
		
		if($this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING) == 'clap_ministats')
			$this->functions->clap_area('ministats');
	
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$fid_param = $fid != null ? '&amp;'.BS_URL_FID.'='.$fid : '';	
		$url = $this->url->get_url(0,$fid_param.'&amp;'.BS_URL_LOC.'=clap_ministats');
		$clap_data = $this->functions->get_clap_data('ministats',$url);
		
		$this->tpl->add_array('stats_data',$stats_data,false);
		$this->tpl->add_variables(array(
			'show_current_topics_link' => !$this->cfg['current_topic_enable'] ||
				strpos($this->cfg['current_topic_loc'],'portal') !== false,
			'clap_image' => $clap_data['link'],
			'ministats_params' => $clap_data['divparams'],
			'cookie_prefix' => BS_COOKIE_PREFIX,
			'current_events_desc' => sprintf($this->locale->lang('current_events_desc'),BS_MINISTATS_EVENT_DAYS),
			'statistics_ins' => $total,
			'events' => $events,
			'birthdays' => $birthdays,
			'lastlogin' => BS_Front_OnlineUtils::get_instance()->get_last_activity(),
			'newest_member' => $this->functions->get_newest_member()
		));
	}

	public function get_location()
	{
		return array();
	}
	
	public function get_robots_value()
	{
		return "index,follow";
	}
}
?>