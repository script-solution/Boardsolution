<?php
/**
 * Contains the topics-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The topics-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_topics extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_SUBSCRIBE_FORUM => 'subscribeforum'
		);
	}
	
	public function run()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);

		// if the topic id is set some things may be wrong...
		if($this->input->isset_var(BS_URL_TID,'get'))
		{
			$this->_report_error();
			return;
		}
		
		// invalid forum-id?
		if(!PLIB_Helper::is_integer($fid) || $fid <= 0)
		{
			$this->_report_error();
			return;
		}

		$forum_data = $this->forums->get_node_data($fid);

		// check if the topic exists
		if($forum_data === null)
		{
			// send a 404 for search-engines and such
			header('HTTP/1.0 404 Not Found');
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('forum_not_found'));
			return;
		}

		$order_options = array('lastpost','topic_name','topic_type','replies','views');
		$order = $this->input->correct_var(
			BS_URL_ORDER,'get',PLIB_Input::STRING,$order_options,'lastpost',false
		);

		$ad = $this->input->correct_var(BS_URL_AD,'get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');

		$limit_options = array(5,8,10,15,30,100,$this->cfg['threads_per_page']);
		$limit = $this->input->correct_var(
			BS_URL_LIMIT,'get',PLIB_Input::INTEGER,$limit_options,$this->cfg['threads_per_page']
		);

		// check if the user is allowed to view the forum
		if(!$this->auth->has_access_to_intern_forum($fid))
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}

		$this->_add_options_top();
		
		$display_lt_top = strpos($this->cfg['current_topic_loc'],'top') !== false;
		$display_lt_bottom = strpos($this->cfg['current_topic_loc'],'bottom') !== false;
		$display_lt = $this->cfg['current_topic_enable'] == 1 && ($display_lt_bottom || $display_lt_top);
		
		$forums = BS_ForumUtils::get_instance();
		if($this->forums->has_childs($fid))
		{
			if($this->forums->get_forum_type($fid) == 'contains_cats' && $display_lt)
				BS_Front_TopicFactory::get_instance()->add_latest_topics_small($fid);

			$forums->get_forum_list($fid);
			
			$this->tpl->add_variables(array(
				'sub_forums' => true
			));
		}

		if($this->forums->get_forum_type($fid) == 'contains_threads')
		{
			$action_type = BS_URL_AT.'='.BS_ACTION_CHANGE_READ_STATUS;
			$fid_param = BS_URL_FID.'='.$fid;
			$forum_read_url = $this->url->get_url(
				0,'&amp;'.$action_type.'&amp;'.BS_URL_LOC.'=read'
					.'&amp;'.BS_URL_MODE.'=forum&amp;'.$fid_param,'&amp;',true
			);

			$pagination = new BS_Pagination($limit,$forum_data->get_threads());
			
			$this->tpl->set_template('inc_topic_action_js.htm');
			$this->tpl->add_variables(array(
				'fid' => $fid,
				'site' => $pagination->get_page()
			));
			$this->tpl->restore_template();

			$mark_read = '<a style="font-size: 0.9em;" href="'.$forum_read_url.'">';
			$mark_read .= $this->locale->lang('mark_forum_read').'</a>';

			if($this->cfg['enable_email_notification'] == 1 &&
				$this->auth->has_global_permission('subscribe_forums'))
			{
				$subscribe_forum_url = $this->url->get_url(
					0,'&amp;'.BS_URL_AT.'='.BS_ACTION_SUBSCRIBE_FORUM.'&amp;'.$fid_param,'&amp;',true
				);
				$subscr_forum = '<a style="font-size: 0.9em;" href="'.$subscribe_forum_url.'">';
				$subscr_forum .= $this->locale->lang('subscribe_forum').'</a>';
				$mark_read .= ', ';
			}
			else
				$subscr_forum = '';

			// display the topics
			$show_topic_action = $this->cfg['display_denied_options'] || $this->user->is_loggedin();
			$topics = new BS_Front_Topics($this->locale->lang('threads'),'',$order,$ad,$limit,$fid);
			$topics->set_show_topic_action($show_topic_action);
			$topics->set_left_content($mark_read.$subscr_forum);
			$topics->set_total_topic_num($forum_data->get_threads());
			$topics->set_middle_width(20);
			$show_search_forum = $this->cfg['enable_search'] && 
				($this->cfg['display_denied_options'] || $this->auth->has_global_permission('view_search'));
			$topics->set_show_search_forum($show_search_forum);
			$topics->add_topics();

			$this->_add_options_bottom();

			if($this->input->isset_var(BS_URL_ORDER,'get'))
			{
				$ad = $this->input->get_var(BS_URL_AD,'get',PLIB_Input::STRING);
				$params = '&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ORDER.'='.$order;
				$params .= '&amp;'.BS_URL_AD.'='.$ad.'&amp;'.BS_URL_LIMIT.'='.$limit;
				$params .= '&amp;'.BS_URL_SITE.'={d}';
				$page_url = $this->url->get_url(0,$params);
			}
			else
				$page_url = $this->url->get_topics_url($fid,'&amp;','{d}');

			$page_split = $this->functions->add_pagination($pagination,$page_url);
			
			$this->tpl->add_variables(array(
				'redirect_url' => $this->url->get_url(
					'redirect','&amp;'.BS_URL_LOC.'=topic_action&amp;'.BS_URL_FID.'='.$fid
						.'&amp;'.BS_URL_SITE.'='.$pagination->get_page()
				),
				'show_topic_action' => $show_topic_action,
				'page_split' => $page_split
			));

			$this->_add_bottom();
		}
		
		$type = $this->forums->get_forum_type($fid);
		$this->tpl->add_variables(array(
			'moderators' => $this->auth->get_forum_mods($fid),
			'latest_topics_top' => $type == 'contains_cats' && $display_lt && $display_lt_top,
			'latest_topics_bottom' => $type == 'contains_cats' && $display_lt && $display_lt_bottom,
			'contains_topics' => $type == 'contains_threads',
			'online_list' => BS_Front_OnlineUtils::get_instance()->add_currently_online('topics')
		));
	}
	
	/**
	 * Builds the top of the topics-view: the create-buttons, the users in the current forum and so on
	 */
	private function _add_options_top()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$forum_data = $this->forums->get_node_data($fid);
		$type = $forum_data->get_forum_type();
		$closed = $forum_data->get_forum_is_closed();
	
		$display_topic = ($this->cfg['display_denied_options'] ||
			$this->auth->has_current_forum_perm(BS_MODE_START_TOPIC)) &&
			($this->user->is_admin() || $closed == 0) && $type == 'contains_threads';
		$display_poll = ($this->cfg['display_denied_options'] ||
			$this->auth->has_current_forum_perm(BS_MODE_START_POLL)) &&
			($this->user->is_admin() || $closed == 0) &&
			$this->cfg['enable_polls'] == 1 && $type == 'contains_threads';
		$display_event = ($this->cfg['display_denied_options'] ||
			$this->auth->has_current_forum_perm(BS_MODE_START_EVENT)) &&
			($this->user->is_admin() || $closed == 0) &&
			$this->cfg['enable_events'] == 1 && $type == 'contains_threads';
	
		$this->tpl->add_variables(array(
			'url' => $this->url->get_url(
				'redirect','&amp;'.BS_URL_LOC.'=topic_action&amp;'.BS_URL_FID.'='.$fid
			),
			'display_topic' => $display_topic,
			'display_poll' => $display_poll,
			'display_event' => $display_event
		));
	}
	
	/**
	 * Builds the top of the topics-view: the create-buttons, the users in the current forum and so on
	 */
	private function _add_options_bottom()
	{
		$this->tpl->add_variables(array(
			'display_topic_actions' => $this->cfg['display_denied_options'] || $this->user->is_loggedin(),
			'topic_action_combo' => BS_TopicUtils::get_instance()->get_action_combobox()
		));
	}

	/**
	 * Builds the topic-bottom
	 */
	private function _add_bottom()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$forum_data = $this->forums->get_node_data($fid);

		// check if the user is allowed to view the forum
		if(!$this->auth->has_access_to_intern_forum($fid))
			return;

		$hidden_fields = array();
		if(($sid = $this->url->get_splitted_session_id()) != 0)
			$hidden_fields[$sid[0]] = $sid[1];
		$hidden_fields = array_merge($hidden_fields,$this->url->get_extern_vars());
		
		$order_ins = '';
		$ascdesc_ins = '';
		$tps_ins = '';
		if($forum_data->get_threads() > 0)
		{
			$form = $this->_request_formular(false,false);
			
			$order = $this->input->get_var(BS_URL_ORDER,'get',PLIB_Input::STRING);
			$ad = $this->input->get_var(BS_URL_AD,'get',PLIB_Input::STRING);
			$limit = $this->input->get_var(BS_URL_LIMIT,'get',PLIB_Input::INTEGER);

			$order_options = array(
				'lastpost' => $this->locale->lang('date'),
				'topic_name' => $this->locale->lang('name'),
				'topic_type' => $this->locale->lang('threadtype'),
				'replies' => $this->locale->lang('replies'),
				'views' => $this->locale->lang('hits')
			);
			$order_ins = $form->get_combobox(BS_URL_ORDER,$order_options,$order);

			$ad_options = array(
				'ASC' => $this->locale->lang('ascending'),
				'DESC' => $this->locale->lang('descending')
			);
			$ascdesc_ins = $form->get_combobox(BS_URL_AD,$ad_options,$ad);

			$limit_options = array(
				5 => 5,
				8 => 8,
				10 => 10,
				15 => 15,
				30 => 30,
				100 => 100,
				$this->cfg['threads_per_page'] => $this->cfg['threads_per_page']
			);
			asort($limit_options);
			$tps_ins = $form->get_combobox(BS_URL_LIMIT,$limit_options,$limit);
		}

		$options = array();
		if($this->auth->has_current_forum_perm(BS_MODE_REPLY))
			$options[] = $this->locale->lang('allow_entry_true');
		else
			$options[] = $this->locale->lang('allow_entry_false');

		if($this->auth->has_current_forum_perm(BS_MODE_START_TOPIC))
			$options[] = $this->locale->lang('allow_topic_true');
		else
			$options[] = $this->locale->lang('allow_topic_false');

		if($this->auth->has_current_forum_perm(BS_MODE_EDIT_OWN_TOPICS) ||
				$this->auth->has_current_forum_perm(BS_MODE_EDIT_TOPIC))
			$options[] = $this->locale->lang('allow_edit_own_topics_true');
		else
			$options[] = $this->locale->lang('allow_edit_own_topics_false');

		if($this->auth->has_global_permission('delete_own_threads') ||
				$this->auth->has_current_forum_perm(BS_MODE_DELETE_TOPICS))
			$options[] = $this->locale->lang('allow_delete_own_topics_true');
		else
			$options[] = $this->locale->lang('allow_delete_own_topics_false');

		if($this->auth->has_global_permission('edit_own_posts') ||
				$this->auth->has_current_forum_perm(BS_MODE_EDIT_POST))
			$options[] = $this->locale->lang('allow_edit_own_posts_true');
		else
			$options[] = $this->locale->lang('allow_edit_own_posts_false');

		if($this->auth->has_global_permission('delete_own_posts') ||
				$this->auth->has_current_forum_perm(BS_MODE_DELETE_POSTS))
			$options[] = $this->locale->lang('allow_delete_own_posts_true');
		else
			$options[] = $this->locale->lang('allow_delete_own_posts_false');

		// change search-display-state?
		if($this->input->get_var(BS_URL_KW,'get',PLIB_Input::STRING) == 'clap_options')
			$this->functions->clap_area('topic_options');

		$clap_cookie = $this->input->get_var(BS_COOKIE_PREFIX.'topic_options','cookie',PLIB_Input::INT_BOOL);
		$img_type = ($clap_cookie === null || $clap_cookie == 1) ? 'open' : 'closed';

		$this->tpl->add_variables(array(
			'img_type' => $img_type,
			'hide_options' => ($clap_cookie === null || $clap_cookie == 1) ? '' : ' style="display: none;"',
			'cookie_prefix' => BS_COOKIE_PREFIX,
			'options' => $options,
			'php_self' => $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'action_param' => BS_URL_ACTION,
			'fid_param' => BS_URL_FID,
			'fid' => $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID),
			'hidden_fields' => $hidden_fields,
			'order_ins' => $order_ins,
			'ascdesc_ins' => $ascdesc_ins,
			'tps_ins' => $tps_ins,
			'number_of_threads' => $forum_data->get_threads()
		));
	}

	public function get_location()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);

		$result = array();
		$this->_add_loc_forum_path($result,$fid);

		return $result;
	}
	
	public function get_robots_value()
	{
		return "index,follow";
	}
}
?>