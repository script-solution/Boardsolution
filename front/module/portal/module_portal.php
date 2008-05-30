<?php
/**
 * Contains the portal-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The portal-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_portal extends BS_Front_Module
{
	public function run()
	{
		// portal disabled?
		if($this->cfg['enable_portal'] == 0)
		{
			$this->_report_error();
			return;
		}
		
		$online = BS_Front_OnlineUtils::get_instance()->get_currently_online_user();
		
		// user-locations
		if($this->cfg['display_denied_options'] ||
				$this->auth->has_global_permission('view_online_locations'))
		{
			$user_locations = '<a href="'.$this->url->get_url('user_locations').'">';
			$user_locations .= $online['online_total'].' '.$this->locale->lang('useronline').'</a>';
		}
		else
			$user_locations = $online['online_total'].' '.$this->locale->lang('useronline');
		
		// check if the news are enabled and if the user can view at least one news-forum
		$enable_news = $this->cfg['enable_portal_news'] == 1 && $this->_are_news_visible(); 
		
		// content
		if($enable_news)
			$this->_add_news();
		else if($this->cfg['current_topic_enable'])
			$this->_add_latest_topics_full();
		
		// left-bar
		$show_compose_pm = $this->user->is_loggedin() && $this->cfg['enable_pms'] &&
			$this->user->get_profile_val('allow_pms');
		
		$show_latest_topics = strpos($this->cfg['current_topic_loc'],'portal') !== false &&
			$this->cfg['current_topic_enable'];
		
		$this->tpl->add_array('online',$online,false);
		$this->tpl->add_variables(array(
			'show_news' => $enable_news,
			'forums_url' => $this->url->get_forums_url(),
			'new_pm_url' => $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=pmcompose'),
			'profile_config_url' => $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=pr_config'),
			'rss20_feed' => $this->url->get_standalone_url('front','news_feed','&amp;'.BS_URL_MODE.'=rss20'),
			'atom_feed' => $this->url->get_standalone_url('front','news_feed','&amp;'.BS_URL_MODE.'=atom'),
			'show_feeds' => $enable_news && $this->cfg['enable_news_feeds'],
			'total_user_online' => $user_locations,
			'show_compose_pm' => $show_compose_pm,
			'legend' => BS_Front_OnlineUtils::get_instance()->get_usergroup_legend(),
			'lastlogin' => BS_Front_OnlineUtils::get_instance()->get_last_activity(),
			'birthdays' => BS_Front_EventUtils::get_instance()->get_todays_birthdays(),
			'events' => BS_Front_EventUtils::get_instance()->get_current_events(),
			'current_topics_url' => $this->url->get_url('latest_topics'),
			'calendar_url' => $this->url->get_url('calendar'),
			'current_topics_url' => $this->url->get_url('latest_topics'),
			'show_latest_topics' => $show_latest_topics,
			'show_latest_topics_full' => $enable_news &&
				strpos($this->cfg['current_topic_loc'],'portal') !== false && $this->cfg['current_topic_enable'],
			'team_url' => $this->url->get_url('team'),
			'newest_member' => $this->functions->get_newest_member()
		));
		
		if($show_latest_topics)
			$this->_add_current_topics();
		
		$this->tpl->add_variables(array(
			'search_url' => $this->url->get_url('search')
		));
		
		// mark the news read
		$this->unread->mark_news_read();
	}
	
	/**
	 * Adds the full version of the latest topics for the portal to the template
	 */
	private function _add_latest_topics_full()
	{
		$url = $this->url->get_url('latest_topics');
		$title = '<a href="'.$url.'">'.$this->locale->lang('current_topics').'</a>';
		
		$num = $this->cfg['current_topic_num'];
		$topics = new BS_Front_Topics($title,' moved_tid = 0','lastpost','DESC',$num,0,true);
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_show_forum(true);
		$topics->set_show_topic_opening(false);
		$topics->set_show_topic_views(false);
		$topics->set_middle_width(60);
		$topics->add_topics();
		
		// TODO that doesn't work yet, right?
	}
	
	/**
	 * Adds the news to the template
	 */
	private function _add_news()
	{
		$fids = PLIB_Array_Utils::advanced_explode(',',$this->cfg['news_forums']);
		if(!PLIB_Array_Utils::is_integer($fids) || count($fids) == 0)
			return;
		
		$cache = array(
			'unread_image'	=> $this->user->get_theme_item_path('images/unread/post_unread.gif'),
			'read_image'		=> $this->user->get_theme_item_path('images/unread/post_read.gif')
		);
		
		// remove the denied-forums from fids
		$denied = BS_ForumUtils::get_instance()->get_denied_forums(false);
		$denied = array_flip($denied);
		$myfids = array();
		foreach($fids as $fid)
		{
			if(!isset($denied[$fid]))
				$myfids[] = $fid;
		}
		
		$newslist = BS_DAO::get_posts()->get_news_from_forums($myfids,$this->cfg['news_count']);
		$num = count($newslist);
		
		$this->tpl->add_variables(array(
			'num' => $num
		));
		
		$news = array();	
		if($num > 0)
		{
			foreach($newslist as $i => $data)
			{
				// build username
				if($data['post_user'] > 0)
				{
					$username = BS_UserUtils::get_instance()->get_link(
						$data['post_user'],$data['user_name'],$data['user_group']
					);
				}
				else
					$username = $data['post_an_user'];
				
				// init bbcode-engine
				$use_bbcode = BS_PostingUtils::get_instance()->get_message_option('enable_bbcode') &&
					$data['use_bbcode'];
				$use_smileys = BS_PostingUtils::get_instance()->get_message_option('enable_smileys') &&
					$data['use_smileys'];
				$bbcode = new BS_BBCode_Parser($data['text'],'posts',$use_bbcode,$use_smileys);
				
				// read or unread?
				if($this->unread->is_unread_news($data['threadid']))
				{
					$news_icon = '<img alt="'.$this->locale->lang('unread_news').'" title="';
		      $news_icon .= $this->locale->lang('unread_news').'"';
		      $news_icon .= ' src="'.$cache['unread_image'].'" />';
				}
				else
			  {
			    $news_icon = '<img alt="'.$this->locale->lang('read_news').'" title="';
			    $news_icon .= $this->locale->lang('read_news').'"';
			    $news_icon .= ' src="'.$cache['read_image'].'" />';
			  }
				
				// build comments-link
				if($data['posts'] > 0)
				{
					$comments_url = $this->url->get_posts_url($data['rubrikid'],$data['threadid']);
					$comments = '<a href="'.$comments_url.'">';
					$comments .= sprintf($this->locale->lang('xcomments'),$data['posts']).'</a>';
				}
				else
				{
					// closed or no comments allowed?
					if($this->forums->forum_is_closed($data['rubrikid']) || !$data['comallow'])
						$comments = '';
					// no permission to reply in this forum?
					else if(!$this->auth->has_permission_in_forum(BS_MODE_REPLY,$data['rubrikid']))
						$comments = '';
					// everything ok, so show the link
					else
					{
						$reply_url = $this->url->get_url('new_post','&amp;'.BS_URL_FID.'='.$data['rubrikid']
							.'&amp;'.BS_URL_TID.'='.$data['threadid']);
						$comments = '<a href="'.$reply_url.'">'.$this->locale->lang('new_comment').'</a>';
					}
				}
				
				// show news
				$news[] = array(
					'news_title' => $data['name'],
					'news_icon' => $news_icon,
					'topic_url' => $this->url->get_posts_url($data['rubrikid'],$data['threadid']),
					'username' => $username,
					'date' => PLIB_Date::get_date($data['post_time']),
					'forum_path' => BS_ForumUtils::get_instance()->get_forum_path($data['rubrikid'],false),
					'text' => $bbcode->get_message_for_output(),
					'comments' => $comments,
					'show_separator' => $i < $num - 1
				);
			}
		}
		
		$this->tpl->add_array('news_list',$news);
	}
	
	/**
	 * Adds the current topics to the template
	 */
	private function _add_current_topics()
	{
		$cache = array(
			'symbol_poll' =>				$this->user->get_theme_item_path('images/thread_type/poll.gif'),
			'symbol_event' =>				$this->user->get_theme_item_path('images/thread_type/event.gif'),
		);
		
		$denied = BS_ForumUtils::get_instance()->get_denied_forums(false);
		
		$topics = array();
		foreach(BS_DAO::get_topics()->get_latest_topics($this->cfg['current_topic_num'],$denied) as $data)
		{
			$pagination = new BS_Pagination($this->cfg['posts_per_page'],$data['posts'] + 1);
			$is_unread = $this->unread->is_unread_thread($data['id']);
			
			$first_unread_url = '';
			if($is_unread)
			{
				$fup = $this->unread->get_first_unread_post($data['id']);
				if($pagination->get_page_count() > 1)
				{
					$first_unread_url = $this->url->get_url(
						'redirect','&amp;'.BS_URL_LOC.'=show_post&amp;'.BS_URL_ID.'='.$fup
					);
				}
				else
				{
					$first_unread_url = $this->url->get_url(
						'posts','&amp;'.BS_URL_FID.'='.$data['rubrikid'].'&amp;'.BS_URL_TID.'='.$data['id']
						.'#b_'.$fup
					);
				}
			}
		
			// build topic-name
			$topic_name = BS_TopicUtils::get_instance()->get_displayed_name($data['name']);
			$posts_url = $this->url->get_posts_url($data['rubrikid'],$data['id'],'&amp;',1);
			
			$topics[] = array(
				'is_important' => $data['important'] == 1,
				'is_unread' => $is_unread,
				'first_unread_url' => $first_unread_url,
				'name_complete' => $topic_name['complete'],
				'name' => $topic_name['displayed'],
				'url' => $posts_url,
				'topic_symbol' => BS_TopicUtils::get_instance()->get_symbol(
					$cache,$data['type'],$data['symbol']
				),
				'lastpost' => $this->_get_lastpost($data)
			);
		}
		
		$this->tpl->add_array('topics',$topics);
	}

	/**
	 * generates the lastpost-data
	 *
	 * @param array $data the topic-data
	 * @return mixed the lastpost-info
	 */
	private function _get_lastpost($data)
	{
		$pagination = new BS_Pagination($this->cfg['posts_per_page'],$data['posts'] + 1);
		if($data['lastpost_id'] == 0)
			return false;

		// generate lastpost-URL
		$site = 1;
		if(BS_PostingUtils::get_instance()->get_posts_order() == 'ASC' && $pagination->get_page_count() > 1)
			$site = $pagination->get_page_count();
		$url = $this->url->get_posts_url($data['rubrikid'],$data['id'],'&amp;',$site);

		// determine username
		if($data['lastpost_user'] != 0)
		{
			$user_name = BS_UserUtils::get_instance()->get_link(
				$data['lastpost_user'],$data['lp_username'],$data['user_group']
			);
		}
		else
			$user_name = $data['lastpost_an_user'];

		return array(
			'username' => $user_name,
			'date' => PLIB_Date::get_date($data['lastpost_time']),
			'url' => $url.'#b_'.$data['lastpost_id'],
		);
	}
	
	/**
	 * Checks wether there are visible news for the current user
	 *
	 * @return boolean true if so
	 */
	private function _are_news_visible()
	{
		$fids = PLIB_Array_Utils::advanced_explode(',',$this->cfg['news_forums']);
		if(!PLIB_Array_Utils::is_integer($fids) || count($fids) == 0)
			return false;
		
		$denied = BS_ForumUtils::get_instance()->get_denied_forums(true);
		$visible = false;
		foreach($fids as $fid)
		{
			if(!in_array($fid,$denied))
			{
				$visible = true;
				break;
			}
		}
		
		return $visible;
	}

	public function get_location()
	{
		return array($this->locale->lang('portal') => $this->url->get_portal_url());
	}
	
	public function get_robots_value()
	{
		return "index,follow";
	}
}
?>