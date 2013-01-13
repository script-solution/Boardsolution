<?php
/**
 * Contains the portal-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * The portal-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_portal extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_robots_value('index,follow');
		$renderer->add_breadcrumb($locale->lang('portal'),BS_URL::build_portal_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$functions = FWS_Props::get()->functions();
		$unread = FWS_Props::get()->unread();
		$forums = FWS_Props::get()->forums();

		// portal disabled?
		if($cfg['enable_portal'] == 0)
		{
			$this->report_error();
			return;
		}
		
		$online = array();
		$legend = '';
		$user_locations = '';
		$view_useronline = $auth->has_global_permission('view_useronline_list');
		if($view_useronline)
		{
			$online = BS_Front_OnlineUtils::get_currently_online_user();
			$legend = BS_Front_OnlineUtils::get_usergroup_legend();
		
			// user-locations
			if($cfg['display_denied_options'] ||
					$auth->has_global_permission('view_online_locations'))
			{
				$user_locations = '<a href="'.BS_URL::build_mod_url('user_locations').'">';
				$user_locations .= $online['online_total'].' '.$locale->lang('useronline').'</a>';
			}
			else
				$user_locations = $online['online_total'].' '.$locale->lang('useronline');
		}
		
		// check if the news are enabled and if the user can view at least one news-forum
		$enable_news = $cfg['enable_portal_news'] == 1 && $this->_are_news_visible();
		
		// content
		if($enable_news)
			$this->_add_news();
		else if($cfg['current_topic_enable'])
			$this->_add_latest_topics_full();
		
		// left-bar
		$show_compose_pm = $user->is_loggedin() && $cfg['enable_pms'] &&
			$user->get_profile_val('allow_pms');
		
		$show_latest_topics_small = $enable_news &&
			strpos($cfg['current_topic_loc'],'portal') !== false &&
			$cfg['current_topic_enable'];
		
		// build last active forum-list
		$nodes = array();
		foreach($this->_get_last_active_forums(BS_PORTAL_LAST_FORUMS_COUNT) as $node)
		{
			$data = $node->get_data();
			/* @var $data BS_Forums_NodeData */
			/* @var $node FWS_Tree_Node */
			
			$nodes[] = array(
				'name' => $data->get_name(),
				'id' => $data->get_id(),
				'path' => BS_ForumUtils::get_forum_path($data->get_id(),false),
				'is_unread' => $forums->is_unread_forum($data->get_id())
			);
		}
		
		$nm = BS_DAO::get_profile()->get_newest_user();
		$newsfeedurl = BS_URL::get_mod_url('news_feed');
		$tpl->add_variable_ref('online',$online);
		$tpl->add_variables(array(
			'show_news' => $enable_news,
			'forums_url' => BS_URL::build_forums_url(),
			'new_pm_url' => BS_URL::build_sub_url('userprofile','pmcompose'),
			'profile_config_url' => BS_URL::build_sub_url('userprofile','config'),
			'rss20_feed' => $newsfeedurl->set(BS_URL_MODE,'rss20')->to_url(),
			'atom_feed' => $newsfeedurl->set(BS_URL_MODE,'atom')->to_url(),
			'show_feeds' => $enable_news && $cfg['enable_news_feeds'],
			'total_user_online' => $user_locations,
			'user_online_count' => $view_useronline ? $online['online_total'] : 0,
			'view_useronline_list' => $view_useronline,
			'show_compose_pm' => $show_compose_pm,
			'legend' => $legend,
			'lastlogin' => BS_Front_OnlineUtils::get_last_activity(),
			'birthdays' => BS_Front_EventUtils::get_todays_birthdays(),
			'events' => BS_Front_EventUtils::get_current_events(),
			'current_topics_url' => BS_URL::build_mod_url('latest_topics'),
			'calendar_url' => BS_URL::build_mod_url('calendar'),
			'current_topics_url' => BS_URL::build_mod_url('latest_topics'),
			'show_latest_topics' => $show_latest_topics_small,
			'show_latest_topics_full' => !$enable_news &&
				strpos($cfg['current_topic_loc'],'portal') !== false && $cfg['current_topic_enable'],
			'team_url' => BS_URL::build_mod_url('team'),
			'newest_member' => BS_UserUtils::get_link($nm['id'],$nm['user_name'],$nm['user_group']),
			'forums' => $nodes
		));
		
		if($show_latest_topics_small)
			$this->_add_latest_topics_small();
		
		$tpl->add_variables(array(
			'search_url' => BS_URL::build_mod_url('search')
		));
		
		// mark the news read
		$unread->mark_news_read();
	}
	
	/**
	 * Returns the last <var>$count</var> active forums
	 *
	 * @param int $count the max. number of forums
	 * @return array the last active forums (the nodes)
	 */
	private function _get_last_active_forums($count)
	{
		$forums = FWS_Props::get()->forums();
		
		// build forums for the list
		$nodes = array();
		foreach($forums->get_all_nodes() as $node)
		{
			$data = $node->get_data();
			$nodes[] = array($data->get_lastpost_time(),$node);
		}
		
		// filter out denied forums
		$denied = BS_ForumUtils::get_denied_forums(false);
		$denied = array_flip($denied);
		foreach($nodes as $k => $node)
		{
			if(isset($denied[$node[1]->get_id()]))
				unset($nodes[$k]);
		}
		
		usort($nodes,array($this,'_sort_active_forums'));
		$res = array();
		for($i = 0,$len = min(count($nodes),$count);$i < $len;$i++)
			$res[] = $nodes[$i][1];
		return $res;
	}
	
	/**
	 * The sort-function for the active-forums
	 *
	 * @param array $a the first forum
	 * @param array $b the second forum
	 * @return int the compare-result
	 */
	private function _sort_active_forums($a,$b)
	{
		if($a[0] < $b[0])
			return 1;
		if($a[0] > $b[0])
			return -1;
		return 0;
	}
	
	/**
	 * Adds the full version of the latest topics for the portal to the template
	 */
	private function _add_latest_topics_full()
	{
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$murl = BS_URL::build_mod_url('latest_topics');
		$title = '<a href="'.$murl.'">'.$locale->lang('current_topics').'</a>';
		
		$num = $cfg['current_topic_num'];
		$topics = new BS_Front_Topics($title,' moved_tid = 0','lastpost','DESC',$num,0,true);
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_show_forum(true);
		$topics->set_show_topic_opening(false);
		$topics->set_show_topic_views(false);
		$topics->set_middle_width(60);
		$topics->add_topics();
	}
	
	/**
	 * Adds the current topics to the template
	 */
	private function _add_latest_topics_small()
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$unread = FWS_Props::get()->unread();
		$tpl = FWS_Props::get()->tpl();

		$cache = array(
			'symbol_poll' =>				$user->get_theme_item_path('images/thread_type/poll.gif'),
			'symbol_event' =>				$user->get_theme_item_path('images/thread_type/event.gif'),
		);
		
		$denied = BS_ForumUtils::get_denied_forums(false);
		
		$rurl = BS_URL::get_mod_url('redirect');
		$rurl->set(BS_URL_LOC,'show_post');
		$purl = BS_URL::get_mod_url('posts');
		
		$topics = array();
		foreach(BS_DAO::get_topics()->get_latest_topics($cfg['current_topic_num'],$denied) as $data)
		{
			$pagination = new BS_Pagination($cfg['posts_per_page'],$data['posts'] + 1);
			$is_unread = $unread->is_unread_thread($data['id']);
			
			$first_unread_url = '';
			if($is_unread)
			{
				$fup = $unread->get_first_unread_post($data['id']);
				if($pagination->get_page_count() > 1)
					$first_unread_url = $rurl->set(BS_URL_ID,$fup)->to_url();
				else
				{
					$purl->set(BS_URL_FID,$data['rubrikid']);
					$purl->set(BS_URL_TID,$data['id']);
					$purl->set_anchor('b_'.$fup);
					$purl->set_sef(true);
					$first_unread_url = $purl->to_url();
				}
			}
		
			// build topic-name
			list($tnamed,$tnamec) = BS_TopicUtils::get_displayed_name($data['name']);
			$posts_url = BS_URL::build_posts_url($data['rubrikid'],$data['id'],1);
			
			$topics[] = array(
				'is_important' => $data['important'] == 1,
				'is_unread' => $is_unread,
				'first_unread_url' => $first_unread_url,
				'name_complete' => $tnamec,
				'name' => $tnamed,
				'url' => $posts_url,
				'topic_symbol' => BS_TopicUtils::get_symbol(
					$cache,$data['type'],$data['symbol']
				),
				'lastpost' => $this->_get_lastpost($data)
			);
		}
		
		$tpl->add_variable_ref('topics',$topics);
	}
	
	/**
	 * Adds the news to the template
	 */
	private function _add_news()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$unread = FWS_Props::get()->unread();
		$locale = FWS_Props::get()->locale();
		$forums = FWS_Props::get()->forums();
		$auth = FWS_Props::get()->auth();

		$fids = FWS_Array_Utils::advanced_explode(',',$cfg['news_forums']);
		if(!FWS_Array_Utils::is_integer($fids) || count($fids) == 0)
			return;
		
		$cache = array(
			'unread_image'	=> $user->get_theme_item_path('images/unread/post_unread.gif'),
			'read_image'		=> $user->get_theme_item_path('images/unread/post_read.gif')
		);
		
		// remove the denied-forums from fids
		$denied = BS_ForumUtils::get_denied_forums(false);
		$denied = array_flip($denied);
		$myfids = array();
		foreach($fids as $fid)
		{
			if(!isset($denied[$fid]))
				$myfids[] = $fid;
		}
		
		$newslist = BS_DAO::get_posts()->get_news_from_forums($myfids,$cfg['news_count']);
		$num = count($newslist);
		
		$tpl->add_variables(array(
			'num' => $num
		));
		
		$news = array();	
		if($num > 0)
		{
			$replyurl = BS_URL::get_mod_url('new_post');
			$posturl = BS_URL::get_mod_url('posts');
			$posturl->set_sef(true);
			foreach($newslist as $i => $data)
			{
				// build username
				if($data['post_user'] > 0)
				{
					$username = BS_UserUtils::get_link(
						$data['post_user'],$data['user_name'],$data['user_group']
					);
				}
				else
					$username = $data['post_an_user'];
				
				// init bbcode-engine
				$use_bbcode = BS_PostingUtils::get_message_option('enable_bbcode') &&
					$data['use_bbcode'];
				$use_smileys = BS_PostingUtils::get_message_option('enable_smileys') &&
					$data['use_smileys'];
				$bbcode = new BS_BBCode_Parser($data['text'],'posts',$use_bbcode,$use_smileys);
				
				// read or unread?
				if($unread->is_unread_news($data['threadid']))
				{
					$news_icon = '<img alt="'.$locale->lang('unread_news').'" title="';
		      $news_icon .= $locale->lang('unread_news').'"';
		      $news_icon .= ' src="'.$cache['unread_image'].'" />';
				}
				else
			  {
			    $news_icon = '<img alt="'.$locale->lang('read_news').'" title="';
			    $news_icon .= $locale->lang('read_news').'"';
			    $news_icon .= ' src="'.$cache['read_image'].'" />';
			  }
				
				// build comments-link
				if($data['posts'] > 0)
				{
					$posturl->set(BS_URL_FID,$data['rubrikid']);
					$posturl->set(BS_URL_TID,$data['threadid']);
					$comments = '<a href="'.$posturl->to_url().'">';
					$comments .= sprintf($locale->lang('xcomments'),$data['posts']).'</a>';
				}
				else
				{
					// closed or no comments allowed?
					if($forums->forum_is_closed($data['rubrikid']) || !$data['comallow'])
						$comments = '';
					// no permission to reply in this forum?
					else if(!$auth->has_permission_in_forum(BS_MODE_REPLY,$data['rubrikid']))
						$comments = '';
					// everything ok, so show the link
					else
					{
						$replyurl->set(BS_URL_FID,$data['rubrikid']);
						$replyurl->set(BS_URL_TID,$data['threadid']);
						$comments = '<a href="'.$replyurl->to_url().'">'.$locale->lang('new_comment').'</a>';
					}
				}
				
				// show news
				$news[] = array(
					'news_title' => $data['name'],
					'id' => $data['id'],
					'news_icon' => $news_icon,
					'topic_url' => BS_URL::build_posts_url($data['rubrikid'],$data['threadid']),
					'username' => $username,
					'username_plain' => $data['post_user'] > 0 ? $data['user_name'] : $data['post_an_user'],
					'date' => FWS_Date::get_date($data['post_time']),
					'forum_path' => BS_ForumUtils::get_forum_path($data['rubrikid'],false),
					'text' => $bbcode->get_message_for_output(),
					'comments' => $comments,
					'show_separator' => $i < $num - 1
				);
			}
		}
		
		$tpl->add_variable_ref('news_list',$news);
	}

	/**
	 * generates the lastpost-data
	 *
	 * @param array $data the topic-data
	 * @return mixed the lastpost-info
	 */
	private function _get_lastpost($data)
	{
		$cfg = FWS_Props::get()->cfg();
		$pagination = new BS_Pagination($cfg['posts_per_page'],$data['posts'] + 1);
		if($data['lastpost_id'] == 0)
			return false;

		// generate lastpost-URL
		$site = 1;
		if(BS_PostingUtils::get_posts_order() == 'ASC' && $pagination->get_page_count() > 1)
			$site = $pagination->get_page_count();
		$murl = BS_URL::build_posts_url($data['rubrikid'],$data['id'],$site);

		// determine username
		if($data['lastpost_user'] != 0)
		{
			$user_name = BS_UserUtils::get_link(
				$data['lastpost_user'],$data['lp_username'],$data['user_group']
			);
		}
		else
			$user_name = $data['lastpost_an_user'];

		return array(
			'username' => $user_name,
			'date' => FWS_Date::get_date($data['lastpost_time']),
			'url' => $murl.'#b_'.$data['lastpost_id'],
		);
	}
	
	/**
	 * Checks wether there are visible news for the current user
	 *
	 * @return boolean true if so
	 */
	private function _are_news_visible()
	{
		$cfg = FWS_Props::get()->cfg();

		if($cfg['news_count'] == 0)
			return 0;
		
		$fids = FWS_Array_Utils::advanced_explode(',',$cfg['news_forums']);
		if(!FWS_Array_Utils::is_integer($fids) || count($fids) == 0)
			return false;
		
		$denied = BS_ForumUtils::get_denied_forums(true);
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
}
?>
