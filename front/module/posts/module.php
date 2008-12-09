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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_robots_value('index,follow');
		
		$renderer->add_action(BS_ACTION_VOTE,'votepoll');
		$renderer->add_action(BS_ACTION_JOIN_EVENT,'joinevent');
		$renderer->add_action(BS_ACTION_LEAVE_EVENT,'leaveevent');
		$renderer->add_action(BS_ACTION_SUBSCRIBE_TOPIC,'subscribetopic');

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$this->add_loc_forum_path($fid);
		$this->add_loc_topic();
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$ips = FWS_Props::get()->ips();
		$cookies = FWS_Props::get()->cookies();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$tpl = FWS_Props::get()->tpl();
		$forums = FWS_Props::get()->forums();
		$functions = FWS_Props::get()->functions();
		$unread = FWS_Props::get()->unread();
		$doc = FWS_Props::get()->doc();

		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);

		// check if the parameters are valid
		if($tid == null || $fid == null)
		{
			// send 404 for search-engines and such
			$doc->set_header('HTTP/1.0 404 Not Found','');
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('thread_not_found'));
			return;
		}

		// increase the views of the topic
		$viewed_topics = $input->get_var(
			BS_COOKIE_PREFIX.'viewed_topics','cookie',FWS_Input::STRING
		);
		$viewed_topic_ids = $viewed_topics != null ? explode(',',$viewed_topics) : array();
		$spam_threadview_timeout = $ips->get_timeout('spam_threadview');
		if($spam_threadview_timeout == 0 || !in_array($tid,$viewed_topic_ids))
		{
			BS_DAO::get_topics()->update($tid,array('views' => array('views + 1')));
			if($spam_threadview_timeout > 0)
			{
				$viewed_topic_ids[] = $tid;
				$cookies->set_cookie('viewed_topics',implode(',',$viewed_topic_ids),
					$spam_threadview_timeout);
			}
		}

		// check if the topic exists
		$topic_data = BS_Front_TopicFactory::get_current_topic();
		if($topic_data === null)
		{
			// send 404 for search-engines and such
			$doc->set_header('HTTP/1.0 404 Not Found','');
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('thread_not_found'));
			return;
		}

		// check if the user is allowed to view this topic
		if(!$auth->has_access_to_intern_forum($fid))
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}

		$this->_add_posting_options_top(
			$topic_data['comallow'] && ($user->is_admin() || !$topic_data['thread_closed']),
			$fid,$tid,$site
		);

		$pagination = new BS_Pagination($cfg['posts_per_page'],$topic_data['posts'] + 1);
		$purl = BS_URL::get_posts_url($fid,$tid,1);

		switch($topic_data['type'])
		{
			case 0:
				$thread_type = $locale->lang('thread');
				break;
			case -1:
				$thread_type = $locale->lang('event');
				break;
			default:
				$thread_type = $locale->lang('poll');
				break;
		}

		// display the head
		$enable_quick_search = $cfg['enable_search'] &&
			($cfg['display_denied_options'] || $auth->has_global_permission('view_search'));
		
		if(($hl = $input->get_var(BS_URL_HL,'get',FWS_Input::STRING)) !== null)
		{
			$hl = stripslashes(FWS_StringHelper::htmlspecialchars_back($hl));
			$purl->set(BS_URL_HL,$hl);
		}
		
		$npurl = BS_URL::get_mod_url('new_post');
		$npurl->set(BS_URL_FID,$fid);
		$npurl->set(BS_URL_TID,$tid);
		$npurl->set(BS_URL_SITE,$site);
		$npurl->set(BS_URL_PID,'__PID__');
		$tpl->add_variables(array(
			'tid' => $tid,
			'fid' => $fid,
			'enable_quick_search' => $enable_quick_search,
			'enable_email_notification' => $cfg['enable_email_notification'] &&
					!$forums->forum_is_closed($topic_data['rubrikid']),
			'thread_type_ins' => $thread_type,
			'sasinsert' => $pagination->get_small($purl),
			'threadname_ins' => $topic_data['name'],
			'show_poll' => $topic_data['type'] > 0,
			'show_event' => $topic_data['type'] == -1,
			'quoteLink' => $npurl->to_url()
		));

		// display poll / event info
		if($topic_data['type'] > 0)
			$this->_add_poll();
		else if($topic_data['type'] == -1)
			$this->_add_event();


		// determine permissions
		$puser = $topic_data['post_user'];
		$edit_perm				= $auth->has_current_forum_perm(BS_MODE_EDIT_TOPIC,$puser);
		$openclose_perm		= $auth->has_current_forum_perm(BS_MODE_OPENCLOSE_TOPICS,$puser);
		$delete_perm			= $auth->has_current_forum_perm(BS_MODE_DELETE_TOPICS,$puser);
		$move_perm				= $auth->has_current_forum_perm(BS_MODE_MOVE_TOPICS,$puser);

		$del_posts_perm		= $auth->has_current_forum_perm(BS_MODE_DELETE_POSTS);
		$split_posts_perm	= $auth->has_current_forum_perm(BS_MODE_SPLIT_POSTS);

		$tactions_perm		= $edit_perm || $openclose_perm || $delete_perm || $move_perm;
		$pactions_perm		= ($user->is_admin() || !$topic_data['thread_closed']) &&
			($del_posts_perm || $split_posts_perm);

		$add_form = ($cfg['display_denied_options'] || $tactions_perm || $pactions_perm) &&
			!$forums->forum_is_closed($topic_data['rubrikid']);

		// display the bottom of the head
		$tpl->add_variables(array(
			'add_posts_action_form' => $add_form,
			'view_ip' => $cfg['display_denied_options'] || $auth->has_global_permission('view_user_ip')
		));
		
		$posts = array();
		$keywords = $functions->get_search_keywords();
		$postcon = new BS_Front_Post_Container(
			$fid,$tid,null,$pagination,'p.id '.BS_PostingUtils::get_posts_order(),'',$keywords
		);
		
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
				'post_date' => FWS_Date::get_date($post->get_field('post_time'),true),
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
		
		$tpl->add_variable_ref('posts',$posts);

		// display bottom
		$this->_add_posting_options_bottom($fid,$tid);

		// show page split
		$purl = BS_URL::get_mod_url();
		$purl->set(BS_URL_FID,$fid);
		$purl->set(BS_URL_TID,$tid);
		if($hl !== null)
			$purl->set(BS_URL_HL,$hl);
		$pagination->populate_tpl($purl);
		
		$show_bottom_bar = ($user->is_admin() ||
			!$forums->forum_is_closed($topic_data['rubrikid'])) &&
			($cfg['display_denied_options'] || $tactions_perm || $pactions_perm);
		
		$view_useronline = $auth->has_global_permission('view_useronline_list');
		if($view_useronline)
			BS_Front_OnlineUtils::add_currently_online('posts');
		
		$tpl->add_variables(array(
			'show_bottom_bar' => $show_bottom_bar,
			'quick_reply_action_type' => BS_ACTION_REPLY,
			'view_useronline_list' => $view_useronline
		));
		
		// mark topic read
		$unread->mark_topics_read(array($topic_data['id']));

		// show bottom bar?
		if($show_bottom_bar)
		{
			// add the javascript to redirect the user to the chosen topic-action
			$tpl->set_template('inc_topic_action_js.htm');
			$tpl->add_variables(array(
				'fid' => $fid,
				'site' => 1 // not needed here
			));
			$tpl->restore_template();
			
			if($del_posts_perm && $split_posts_perm)
				$manage_posts_title = $locale->lang('move_or_delete_posts');
			else if($del_posts_perm)
				$manage_posts_title = $locale->lang('delete_posts');
			else
				$manage_posts_title = $locale->lang('move_posts');
			
			// the bottom bar...
			
			$tpl->add_variables(array(
				'display_topic_actions' => $cfg['display_denied_options'] || $tactions_perm,
				'display_post_actions' => $cfg['display_denied_options'] || $pactions_perm,
				'manage_posts_title' => $manage_posts_title,
				'topic_action_combo' => BS_TopicUtils::get_action_combobox(
					'posts',$topic_data['thread_closed']
				),
			));
		}

		if($cfg['display_similar_topics'] == 1)
		{
			$curl = BS_URL::get_posts_url($fid,$tid);
			if($hl !== null)
				$curl->set(BS_URL_HL,$hl);
			BS_Front_TopicFactory::add_similar_topics(
				$topic_data['name'],$topic_data['id'],$curl
			);
		}
		
		$tpl->add_variables(array(
			'similar_topics' => $cfg['display_similar_topics'] == 1
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
		$user = FWS_Props::get()->user();
		$forums = FWS_Props::get()->forums();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$tpl = FWS_Props::get()->tpl();
		
		$display_reply = $allow_posts &&
			($user->is_admin() || !$forums->forum_is_closed($fid)) &&
			($cfg['display_denied_options'] || $auth->has_current_forum_perm(BS_MODE_REPLY));
		
		$url = BS_URL::get_mod_url('new_post');
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_TID,$tid);
		if($site > 0)
			$url->set(BS_URL_SITE,$site);
		
		$tpl->add_variables(array(
			'display_reply' => $display_reply,
			'reply_url' => $url->to_url()
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
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$forums = FWS_Props::get()->forums();
		$tpl = FWS_Props::get()->tpl();
		$topic_data = BS_Front_TopicFactory::get_current_topic();
		$display_subscribe = ($cfg['display_denied_options'] || $user->is_loggedin()) &&
			$cfg['enable_email_notification'] && !$topic_data['thread_closed'] &&
			!$forums->forum_is_closed($fid);
		
		$url = BS_URL::get_standalone_url('print');
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_TID,$tid);
		$tpl->add_variables(array(
			'enable_email_notification' => $display_subscribe,
			'print_url' => $url->to_url()
		));
	}
	
	/**
	 * Adds the poll-data to this a topic (if it's a poll)
	 */
	private function _add_poll()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();

		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$topic_data = BS_Front_TopicFactory::get_current_topic();
	
		$user_voted = BS_UserUtils::user_voted_for_poll($topic_data['type']);
		
		$url = BS_URL::get_posts_url($fid,$tid,1);
		$url->set(BS_URL_MODE,'results');
		$result_url = $url->to_url();
		$vote_url = BS_URL::build_posts_url($fid,$tid);
	
		$show_results = !$user->is_loggedin() ||
			$input->get_var(BS_URL_MODE,'get',FWS_Input::STRING) == 'results' || $user_voted ||
			$topic_data['thread_closed'] == 1;
		
		$tpl->set_template('inc_poll.htm');
		$tpl->add_variables(array(
			'vote_action' => $vote_url,
			'action_type' => BS_ACTION_VOTE
		));
		
		$tploptions = array();
		
		// result
		if($show_results)
		{
			$img_rating_back = $user->get_theme_item_path('images/diagrams/rate_back.gif');
	
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
			
			$tpl->add_variables(array(
				'show_poll_options' => $user->is_loggedin() &&
					!$user_voted && $topic_data['thread_closed'] == 0
			));
		}
		// vote
		else
		{
			$this->request_formular(false,false);
			
			foreach(BS_DAO::get_polls()->get_options_by_id($topic_data['type']) as $i => $pdata)
			{
				if($pdata['multichoice'] == 1)
				{
					$vote_button =  new FWS_HTML_Checkbox(
						'vote_option[]','vote_'.$i.'_'.$pdata['id'],null,null,'',$pdata['id']
					);
				}
				else
				{
					$vote_button = new FWS_HTML_RadioButtonGroup('vote_option','vote_'.$i,null);
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
	
			$tpl->add_variables(array(
				'show_poll_options' => true
			));
		}
		
		$tpl->add_variables(array(
			'show_results' => $show_results,
			'result_url' => $result_url,
			'vote_url' => $vote_url
		));
		
		$tpl->add_variable_ref('poll_options',$tploptions);
		$tpl->restore_template();
	}
	
	/**
	 * Adds the event in the current topic (if it is an event)
	 */
	private function _add_event()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();

		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
	
		$event_data = BS_DAO::get_events()->get_by_topic_id($tid);
		if($event_data['event_end'] == 0)
			$event_end = 'open';
		else
			$event_end = FWS_Date::get_date($event_data['event_end']);
		
		$tpl->set_template('inc_event.htm');
		$tpl->add_variables(array(
			'location' => $event_data['event_location'],
			'event_begin' => FWS_Date::get_date($event_data['event_begin']),
			'event_end' => $event_end,
			'description' => nl2br($event_data['description']),
			'show_announcements' => $event_data['max_announcements'] >= 0
		));
		
		if($event_data['max_announcements'] >= 0)
		{
			if($event_data['timeout'] == 0)
				$timeout = FWS_Date::get_date($event_data['event_begin']);
			else
				$timeout = FWS_Date::get_date($event_data['timeout']);
			
			$event = new BS_Event($event_data);
			$tpl->add_variables(array(
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
		
		$tpl->restore_template();
	}
}
?>