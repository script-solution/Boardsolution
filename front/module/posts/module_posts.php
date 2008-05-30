<?php
/**
 * Contains the posts-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The posts-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_posts extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_VOTE => 'votepoll',
			BS_ACTION_JOIN_EVENT => 'joinevent',
			BS_ACTION_LEAVE_EVENT => 'leaveevent',
			BS_ACTION_SUBSCRIBE_TOPIC => 'subscribetopic'
		);
	}
	
	public function run()
	{
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);

		// check if the parameters are valid
		if($tid == null || $fid == null)
		{
			// send 404 for search-engines and such
			header('HTTP/1.0 404 Not Found');
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('thread_not_found'));
			return;
		}

		// increase the views of the topic
		$viewed_topics = $this->input->get_var(
			BS_COOKIE_PREFIX.'viewed_topics','cookie',PLIB_Input::STRING
		);
		$viewed_topic_ids = $viewed_topics != null ? explode(',',$viewed_topics) : array();
		$spam_threadview_timeout = $this->ips->get_timeout('spam_threadview');
		if($spam_threadview_timeout == 0 || !in_array($tid,$viewed_topic_ids))
		{
			BS_DAO::get_topics()->update($tid,array('views' => array('views + 1')));
			if($spam_threadview_timeout > 0)
			{
				$viewed_topic_ids[] = $tid;
				$this->cookies->set_cookie('viewed_topics',implode(',',$viewed_topic_ids),
					$spam_threadview_timeout);
			}
		}

		$topic_data = $this->cache->get_cache('topic')->current();

		// check if the topic exists
		if($topic_data['id'] == '')
		{
			// send 404 for search-engines and such
			header('HTTP/1.0 404 Not Found');
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('thread_not_found'));
			return;
		}

		// check if the user is allowed to view this topic
		if(!$this->auth->has_access_to_intern_forum($fid))
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}

		$this->_add_posting_options_top(
			$topic_data['comallow'] && ($this->user->is_admin() || !$topic_data['thread_closed']),
			$fid,$tid,$site
		);

		$pagination = new BS_Pagination($this->cfg['posts_per_page'],$topic_data['posts'] + 1);
		$url = $this->url->get_url(
			'posts','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_SITE.'={d}'
		);

		switch($topic_data['type'])
		{
			case 0:
				$thread_type = $this->locale->lang('thread');
				break;
			case -1:
				$thread_type = $this->locale->lang('event');
				break;
			default:
				$thread_type = $this->locale->lang('poll');
				break;
		}

		// display the head
		$enable_quick_search = $this->cfg['enable_search'] &&
			($this->cfg['display_denied_options'] || $this->auth->has_global_permission('view_search'));
		
		$spagiurl = $url;
		if(($hl = $this->input->get_var(BS_URL_HL,'get',PLIB_Input::STRING)) !== null)
		{
			$hl = stripslashes(PLIB_StringHelper::htmlspecialchars_back($hl));
			$spagiurl .= '&amp;'.BS_URL_HL.'='.urlencode($hl);
		}
		
		$this->tpl->add_variables(array(
			'tid' => $tid,
			'fid' => $fid,
			'enable_quick_search' => $enable_quick_search,
			'enable_email_notification' => $this->cfg['enable_email_notification'] &&
					!$this->forums->forum_is_closed($topic_data['rubrikid']),
			'thread_type_ins' => $thread_type,
			'sasinsert' => $this->functions->get_pagination_small($pagination,$spagiurl),
			'threadname_ins' => $topic_data['name'],
			'show_poll' => $topic_data['type'] > 0,
			'show_event' => $topic_data['type'] == -1
		));

		// display poll / event info
		if($topic_data['type'] > 0)
			$this->_add_poll();
		else if($topic_data['type'] == -1)
			$this->_add_event();


		// determine permissions
		$puser = $topic_data['post_user'];
		$edit_perm				= $this->auth->has_current_forum_perm(BS_MODE_EDIT_TOPIC,$puser);
		$openclose_perm		= $this->auth->has_current_forum_perm(BS_MODE_OPENCLOSE_TOPICS,$puser);
		$delete_perm			= $this->auth->has_current_forum_perm(BS_MODE_DELETE_TOPICS,$puser);
		$move_perm				= $this->auth->has_current_forum_perm(BS_MODE_MOVE_TOPICS,$puser);

		$del_posts_perm		= $this->auth->has_current_forum_perm(BS_MODE_DELETE_POSTS);
		$split_posts_perm	= $this->auth->has_current_forum_perm(BS_MODE_SPLIT_POSTS);

		$tactions_perm		= $edit_perm || $openclose_perm || $delete_perm || $move_perm;
		$pactions_perm		= ($this->user->is_admin() || !$topic_data['thread_closed']) &&
			($del_posts_perm || $split_posts_perm);

		$add_form = ($this->cfg['display_denied_options'] || $tactions_perm || $pactions_perm) &&
			!$this->forums->forum_is_closed($topic_data['rubrikid']);

		// display the bottom of the head
		$this->tpl->add_variables(array(
			'add_posts_action_form' => $add_form,
			'view_ip' => $this->cfg['display_denied_options'] || $this->auth->has_global_permission('view_user_ip')
		));
		
		$posts = array();
		$postcon = new BS_Front_Post_Container(
			$fid,$tid,null,$pagination,'p.id '.BS_PostingUtils::get_instance()->get_posts_order()
		);
		$keywords = $this->functions->get_search_keywords();
		if($keywords !== null)
			$postcon->set_highlight_keywords($keywords);
		
		foreach($postcon->get_posts() as $post)
		{
			/* @var $post BS_Front_Post_Data */
			$posts[] = array(
				'bid' => $post->get_field('bid'),
				'user_id' => $post->get_field('post_user'),
				'rank_images' => $post->get_rank_images(),
				'new_post_pic' => $post->get_post_image(),
				'is_unread' => $post->is_unread(),
				'user_name' => $post->get_username(),
				'user_name_plain' => $post->get_username(false),
				'an_email_ins' => $post->get_guest_email(),
				'post_date' => PLIB_Date::get_date($post->get_field('post_time'),true),
				'main_table_class' => $post->get_css_class('main'),
				'left_table_class' => $post->get_css_class('left'),
				'posts_bar_class' => $post->get_css_class('bar'),
				'number' => $post->get_post_number(),
				'user_status' => $post->get_user_status(),
				'rank_title' => $post->get_rank_title(),
				'user_ip' => $post->get_user_ip(),
				'show_avatar' => $post->show_avatar(),
				'avatar' => $post->get_avatar(),
				'add_fields' => $post->get_additional_fields(),
				'register_time' => $post->get_register_date(),
				'message_ins' => $post->get_profile_buttons(),
				'bottom_ins' => $post->get_post_buttons(),
				'show_separator' => !$post->is_last_post(),
				'stats_ins_bottom' => $post->get_user_stats(),
				'post_url' => $post->get_post_url(),
				'text_ins' => $post->get_post_text()
			);
		}
		
		$this->tpl->add_array('posts',$posts);

		// display bottom
		$this->_add_posting_options_bottom($fid,$tid);

		// show page split
		$hl = $this->input->get_var(BS_URL_HL,'get',PLIB_Input::STRING);
		$highlight = ($hl != null) ? '&amp;'.BS_URL_HL.'='.$hl : '';
		$purl = $this->url->get_url(
			0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.$highlight.'&amp;'.BS_URL_SITE.'={d}'
		);
		$this->functions->add_pagination($pagination,$purl);
		
		$show_bottom_bar = ($this->user->is_admin() ||
			!$this->forums->forum_is_closed($topic_data['rubrikid'])) &&
			($this->cfg['display_denied_options'] || $tactions_perm || $pactions_perm);
		
		BS_Front_OnlineUtils::get_instance()->add_currently_online('posts');
		
		$this->tpl->add_variables(array(
			'show_bottom_bar' => $show_bottom_bar,
			'quick_reply_action_type' => BS_ACTION_REPLY
		));
		
		// mark topic read
		if($this->user->is_loggedin())
			$this->unread->mark_topics_read(array($topic_data['id']));

		// show bottom bar?
		if($show_bottom_bar)
		{
			// add the javascript to redirect the user to the chosen topic-action
			$this->tpl->set_template('inc_topic_action_js.htm');
			$this->tpl->add_variables(array(
				'fid' => $fid,
				'site' => 1 // not needed here
			));
			$this->tpl->restore_template();
			
			if($del_posts_perm && $split_posts_perm)
				$manage_posts_title = $this->locale->lang('move_or_delete_posts');
			else if($del_posts_perm)
				$manage_posts_title = $this->locale->lang('delete_posts');
			else
				$manage_posts_title = $this->locale->lang('move_posts');
			
			// the bottom bar...
			
			$this->tpl->add_variables(array(
				'display_topic_actions' => $this->cfg['display_denied_options'] || $tactions_perm,
				'display_post_actions' => $this->cfg['display_denied_options'] || $pactions_perm,
				'manage_posts_title' => $manage_posts_title,
				'topic_action_combo' => BS_TopicUtils::get_instance()->get_action_combobox(
					'posts',$topic_data['thread_closed']
				),
			));
		}

		if($this->cfg['display_similar_topics'] == 1)
		{
			$current_url = $this->url->get_url(0,'&amp;'.BS_URL_FID.'='.$fid
				.'&amp;'.BS_URL_TID.'='.$tid.$highlight);
			BS_Front_TopicFactory::get_instance()->add_similar_topics(
				$topic_data['name'],$topic_data['id'],$current_url
			);
		}
		
		$this->tpl->add_variables(array(
			'similar_topics' => $this->cfg['display_similar_topics'] == 1
		));
	}
	
	/**
	 * Adds the top of the posting page: the reply button and the user in the current forum
	 *
	 * @param boolean $allow_posts is the topic open?
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @param int $site the current page
	 */
	private function _add_posting_options_top($allow_posts,$fid,$tid,$site)
	{
		$site_add = $site != null ? '&amp;'.BS_URL_SITE.'='.$site : '';
		
		$display_reply = $allow_posts &&
			($this->user->is_admin() || !$this->forums->forum_is_closed($fid)) &&
			($this->cfg['display_denied_options'] || $this->auth->has_current_forum_perm(BS_MODE_REPLY));
	
		$this->tpl->add_variables(array(
			'display_reply' => $display_reply,
			'reply_url' => $this->url->get_url(
				'new_post','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.$site_add
			)
		));
	}
	
	/**
	 * Adds the bottom of the posting page: the reply button
	 *
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 */
	private function _add_posting_options_bottom($fid,$tid)
	{
		$topic_data = $this->cache->get_cache('topic')->current();
		$display_subscribe = ($this->cfg['display_denied_options'] || $this->user->is_loggedin()) &&
			$this->cfg['enable_email_notification'] && !$topic_data['thread_closed'] &&
			!$this->forums->forum_is_closed($fid);
	
		$this->tpl->add_variables(array(
			'enable_email_notification' => $display_subscribe,
			'print_url' => $this->url->get_standalone_url(
				'front','print','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid
			)
		));
	}
	
	/**
	 * Adds the poll-data to this a topic (if it's a poll)
	 */
	private function _add_poll()
	{
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$topic_data = $this->cache->get_cache('topic')->current();
	
		$user_voted = BS_UserUtils::get_instance()->user_voted_for_poll($topic_data['type']);
		$result_url = $this->url->get_url(
			0,'&amp;'.BS_URL_FID."=".$fid."&amp;".BS_URL_TID."=".$tid."&amp;".BS_URL_MODE."=results"
		);
		$vote_url = $this->url->get_posts_url($fid,$tid);
	
		$show_results = !$this->user->is_loggedin() ||
			$this->input->get_var(BS_URL_MODE,'get',PLIB_Input::STRING) == 'results' || $user_voted ||
			$topic_data['thread_closed'] == 1;
		
		$this->tpl->set_template('inc_poll.htm');
		$this->tpl->add_variables(array(
			'vote_action' => $this->url->get_url(0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid),
			'action_type' => BS_ACTION_VOTE
		));
		
		$tploptions = array();
		
		// result
		if($show_results)
		{
			$img_rating_back = $this->user->get_theme_item_path('images/diagrams/rate_back.gif');
	
			$total_votes = 0;
			$poll_options = array();
			$optionlist = BS_DAO::get_polls()->get_options_by_id($topic_data['type'],'option_value','DESC');
			foreach($optionlist as $pdata)
			{
				$poll_options[] = $pdata;
				$total_votes += $pdata['option_value'];
			}
	
			foreach($poll_options as $pdata)
			{
				if($pdata['option_value'] == 0)
					$percent = 0;
				else
					$percent = @round(100 / ($total_votes / $pdata['option_value']),2);
	
				if($percent != 0)
					$img_back = '<img src="'.$img_rating_back.'" alt="" />';
				else
					$img_back = '';
	
				$img_percent = round($percent,0);
	
				$tploptions[] = array(
					'index' => 0,
					'multichoice' => $pdata['multichoice'],
					'option_name' => $pdata['option_name'],
					'option_value' => $pdata['option_value'],
					'option_id' => $pdata['id'],
					'percent' => $percent,
					'img_width' => ($img_percent > 0 ? '100%' : '0px'),
					'img_percent' => $img_percent,
					'img_remaining_percent' => 100 - $img_percent,
					'img_back' => $img_back
				);
			}
			
			$this->tpl->add_variables(array(
				'show_poll_options' => $this->user->is_loggedin() &&
					!$user_voted && $topic_data['thread_closed'] == 0
			));
		}
		// vote
		else
		{
			$this->_request_formular(false,false);
			
			foreach(BS_DAO::get_polls()->get_options_by_id($topic_data['type']) as $i => $pdata)
			{
				if($pdata['multichoice'] == 1)
				{
					$vote_button =  new PLIB_HTML_Checkbox(
						'vote_option[]','vote_'.$i.'_'.$pdata['id'],null,null,'',$pdata['id']
					);
				}
				else
				{
					$vote_button = new PLIB_HTML_RadioButtonGroup('vote_option','vote_'.$i,null);
					$vote_button->add_option($pdata['id'],'');
				}
				$vote_button->set_custom_attribute('onclick','this.checked = !this.checked;');
	
				$tploptions[] = array(
					'option_name' => $pdata['option_name'],
					'option_value' => $pdata['option_value'],
					'option_id' => $pdata['id'],
					'vote_button' => $vote_button->to_html()
				);
			}
	
			$this->tpl->add_variables(array(
				'show_poll_options' => true
			));
		}
		
		$this->tpl->add_variables(array(
			'show_results' => $show_results,
			'result_url' => $result_url,
			'vote_url' => $vote_url
		));
		
		$this->tpl->add_array('poll_options',$tploptions);
		$this->tpl->restore_template();
	}
	
	/**
	 * Adds the event in the current topic (if it is an event)
	 */
	private function _add_event()
	{
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
	
		$event_data = BS_DAO::get_events()->get_by_topic_id($tid);
		if($event_data['event_end'] == 0)
			$event_end = 'open';
		else
			$event_end = PLIB_Date::get_date($event_data['event_end']);
		
		$this->tpl->set_template('inc_event.htm');
		$this->tpl->add_variables(array(
			'location' => $event_data['event_location'],
			'event_begin' => PLIB_Date::get_date($event_data['event_begin']),
			'event_end' => $event_end,
			'description' => nl2br($event_data['description']),
			'show_announcements' => $event_data['max_announcements'] >= 0
		));
		
		if($event_data['max_announcements'] >= 0)
		{
			if($event_data['timeout'] == 0)
				$timeout = PLIB_Date::get_date($event_data['event_begin']);
			else
				$timeout = PLIB_Date::get_date($event_data['timeout']);
			
			$event = new BS_Event($event_data);
			$this->tpl->add_variables(array(
				'timeout' => $timeout,
				'fid' => $fid,
				'tid' => $tid,
				'can_leave' => $event->can_leave(),
				'can_announce' => $event->can_announce(),
				'announcement_list' => $event->get_announcement_list(),
				'max_announcements' => $event_data['max_announcements'],
				'total_announcements' => $event->get_count()
			));
		}
		
		$this->tpl->restore_template();
	}

	public function get_location()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);

		$result = array();
		$this->_add_loc_forum_path($result,$fid);
		$this->_add_loc_topic($result);

		return $result;
	}
	
	public function get_robots_value()
	{
		return "index,follow";
	}
}
?>