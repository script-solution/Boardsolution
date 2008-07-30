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
		$renderer->add_action(BS_ACTION_SUBSCRIBE_FORUM,'subscribeforum');

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$this->add_loc_forum_path($fid);
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();
		$functions = FWS_Props::get()->functions();
		$forums = FWS_Props::get()->forums();

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);

		// if the topic id is set some things may be wrong...
		if($input->isset_var(BS_URL_TID,'get'))
		{
			$this->report_error();
			return;
		}
		
		// invalid forum-id?
		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
		{
			$this->report_error();
			return;
		}

		$forum_data = $forums->get_node_data($fid);

		// check if the topic exists
		if($forum_data === null)
		{
			// send a 404 for search-engines and such
			$doc->set_header('HTTP/1.0 404 Not Found','');
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('forum_not_found'));
			return;
		}

		$order_options = array('lastpost','topic_name','topic_type','replies','views');
		$order = $input->correct_var(
			BS_URL_ORDER,'get',FWS_Input::STRING,$order_options,'lastpost',false
		);

		$ad = $input->correct_var(BS_URL_AD,'get',FWS_Input::STRING,array('ASC','DESC'),'DESC');

		$limit_options = array(5,8,10,15,30,100,$cfg['threads_per_page']);
		$limit = $input->correct_var(
			BS_URL_LIMIT,'get',FWS_Input::INTEGER,$limit_options,$cfg['threads_per_page']
		);

		// check if the user is allowed to view the forum
		if(!$auth->has_access_to_intern_forum($fid))
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}

		$this->_add_options_top();
		
		$display_lt_top = strpos($cfg['current_topic_loc'],'top') !== false;
		$display_lt_bottom = strpos($cfg['current_topic_loc'],'bottom') !== false;
		$display_lt = $cfg['current_topic_enable'] == 1 && ($display_lt_bottom || $display_lt_top);
		
		$futils = BS_ForumUtils::get_instance();
		if($forums->has_childs($fid))
		{
			if($forums->get_forum_type($fid) == 'contains_cats' && $display_lt)
				BS_Front_TopicFactory::get_instance()->add_latest_topics_small($fid);

			$futils->get_forum_list($fid);
			
			$tpl->add_variables(array(
				'sub_forums' => true
			));
		}

		if($forums->get_forum_type($fid) == 'contains_threads')
		{
			$action_type = BS_URL_AT.'='.BS_ACTION_CHANGE_READ_STATUS;
			$fid_param = BS_URL_FID.'='.$fid;
			$forum_read_url = BS_URL::get_url(
				0,'&amp;'.$action_type.'&amp;'.BS_URL_LOC.'=read'
					.'&amp;'.BS_URL_MODE.'=forum&amp;'.$fid_param,'&amp;',true
			);

			$pagination = new BS_Pagination($limit,$forum_data->get_threads());
			
			$tpl->set_template('inc_topic_action_js.htm');
			$tpl->add_variables(array(
				'fid' => $fid,
				'site' => $pagination->get_page()
			));
			$tpl->restore_template();

			$mark_read = '<a style="font-size: 0.9em;" href="'.$forum_read_url.'">';
			$mark_read .= $locale->lang('mark_forum_read').'</a>';

			if($cfg['enable_email_notification'] == 1 &&
				$auth->has_global_permission('subscribe_forums'))
			{
				$subscribe_forum_url = BS_URL::get_url(
					0,'&amp;'.BS_URL_AT.'='.BS_ACTION_SUBSCRIBE_FORUM.'&amp;'.$fid_param,'&amp;',true
				);
				$subscr_forum = '<a style="font-size: 0.9em;" href="'.$subscribe_forum_url.'">';
				$subscr_forum .= $locale->lang('subscribe_forum').'</a>';
				$mark_read .= ', ';
			}
			else
				$subscr_forum = '';

			// display the topics
			$show_topic_action = $cfg['display_denied_options'] || $user->is_loggedin();
			$topics = new BS_Front_Topics($locale->lang('threads'),'',$order,$ad,$limit,$fid);
			$topics->set_show_topic_action($show_topic_action);
			$topics->set_left_content($mark_read.$subscr_forum);
			$topics->set_total_topic_num($forum_data->get_threads());
			$topics->set_middle_width(20);
			$show_search_forum = $cfg['enable_search'] && 
				($cfg['display_denied_options'] || $auth->has_global_permission('view_search'));
			$topics->set_show_search_forum($show_search_forum);
			$topics->add_topics();

			$this->_add_options_bottom();

			if($input->isset_var(BS_URL_ORDER,'get'))
			{
				$ad = $input->get_var(BS_URL_AD,'get',FWS_Input::STRING);
				$params = '&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ORDER.'='.$order;
				$params .= '&amp;'.BS_URL_AD.'='.$ad.'&amp;'.BS_URL_LIMIT.'='.$limit;
				$params .= '&amp;'.BS_URL_SITE.'={d}';
				$page_url = BS_URL::get_url(0,$params);
			}
			else
				$page_url = BS_URL::get_topics_url($fid,'&amp;','{d}');

			$page_split = $functions->add_pagination($pagination,$page_url);
			
			$tpl->add_variables(array(
				'redirect_url' => BS_URL::get_url(
					'redirect','&amp;'.BS_URL_LOC.'=topic_action&amp;'.BS_URL_FID.'='.$fid
						.'&amp;'.BS_URL_SITE.'='.$pagination->get_page()
				),
				'show_topic_action' => $show_topic_action,
				'page_split' => $page_split
			));

			$this->_add_bottom();
		}
		
		$view_useronline = $auth->has_global_permission('view_useronline_list');
		if($view_useronline)
			BS_Front_OnlineUtils::get_instance()->add_currently_online('topics');
		
		$type = $forums->get_forum_type($fid);
		$tpl->add_variables(array(
			'moderators' => $auth->get_forum_mods($fid),
			'latest_topics_top' => $type == 'contains_cats' && $display_lt && $display_lt_top,
			'latest_topics_bottom' => $type == 'contains_cats' && $display_lt && $display_lt_bottom,
			'contains_topics' => $type == 'contains_threads',
			'view_useronline_list' => $view_useronline
		));
	}
	
	/**
	 * Builds the top of the topics-view: the create-buttons, the users in the current forum and so on
	 */
	private function _add_options_top()
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$forum_data = $forums->get_node_data($fid);
		$type = $forum_data->get_forum_type();
		$closed = $forum_data->get_forum_is_closed();
	
		$display_topic = ($cfg['display_denied_options'] ||
			$auth->has_current_forum_perm(BS_MODE_START_TOPIC)) &&
			($user->is_admin() || $closed == 0) && $type == 'contains_threads';
		$display_poll = ($cfg['display_denied_options'] ||
			$auth->has_current_forum_perm(BS_MODE_START_POLL)) &&
			($user->is_admin() || $closed == 0) &&
			$cfg['enable_polls'] == 1 && $type == 'contains_threads';
		$display_event = ($cfg['display_denied_options'] ||
			$auth->has_current_forum_perm(BS_MODE_START_EVENT)) &&
			($user->is_admin() || $closed == 0) &&
			$cfg['enable_events'] == 1 && $type == 'contains_threads';
	
		$tpl->add_variables(array(
			'url' => BS_URL::get_url(
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
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();

		$tpl->add_variables(array(
			'display_topic_actions' => $cfg['display_denied_options'] || $user->is_loggedin(),
			'topic_action_combo' => BS_TopicUtils::get_instance()->get_action_combobox()
		));
	}

	/**
	 * Builds the topic-bottom
	 */
	private function _add_bottom()
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$forum_data = $forums->get_node_data($fid);

		// check if the user is allowed to view the forum
		if(!$auth->has_access_to_intern_forum($fid))
			return;

		$hidden_fields = array();
		if(($sid = BS_URL::get_session_id()) !== false)
			$hidden_fields[$sid[0]] = $sid[1];
		$url = new BS_URL();
		$hidden_fields = array_merge($hidden_fields,$url->get_extern_vars());
		
		$order_ins = '';
		$ascdesc_ins = '';
		$tps_ins = '';
		if($forum_data->get_threads() > 0)
		{
			$form = $this->request_formular(false,false);
			
			$order = $input->get_var(BS_URL_ORDER,'get',FWS_Input::STRING);
			$ad = $input->get_var(BS_URL_AD,'get',FWS_Input::STRING);
			$limit = $input->get_var(BS_URL_LIMIT,'get',FWS_Input::INTEGER);

			$order_options = array(
				'lastpost' => $locale->lang('date'),
				'topic_name' => $locale->lang('name'),
				'topic_type' => $locale->lang('threadtype'),
				'replies' => $locale->lang('replies'),
				'views' => $locale->lang('hits')
			);
			$order_ins = $form->get_combobox(BS_URL_ORDER,$order_options,$order);

			$ad_options = array(
				'ASC' => $locale->lang('ascending'),
				'DESC' => $locale->lang('descending')
			);
			$ascdesc_ins = $form->get_combobox(BS_URL_AD,$ad_options,$ad);

			$limit_options = array(
				5 => 5,
				8 => 8,
				10 => 10,
				15 => 15,
				30 => 30,
				100 => 100,
				$cfg['threads_per_page'] => $cfg['threads_per_page']
			);
			asort($limit_options);
			$tps_ins = $form->get_combobox(BS_URL_LIMIT,$limit_options,$limit);
		}

		$options = array();
		if($auth->has_current_forum_perm(BS_MODE_REPLY))
			$options[] = $locale->lang('allow_entry_true');
		else
			$options[] = $locale->lang('allow_entry_false');

		if($auth->has_current_forum_perm(BS_MODE_START_TOPIC))
			$options[] = $locale->lang('allow_topic_true');
		else
			$options[] = $locale->lang('allow_topic_false');

		if($auth->has_current_forum_perm(BS_MODE_EDIT_OWN_TOPICS) ||
				$auth->has_current_forum_perm(BS_MODE_EDIT_TOPIC))
			$options[] = $locale->lang('allow_edit_own_topics_true');
		else
			$options[] = $locale->lang('allow_edit_own_topics_false');

		if($auth->has_global_permission('delete_own_threads') ||
				$auth->has_current_forum_perm(BS_MODE_DELETE_TOPICS))
			$options[] = $locale->lang('allow_delete_own_topics_true');
		else
			$options[] = $locale->lang('allow_delete_own_topics_false');

		if($auth->has_global_permission('edit_own_posts') ||
				$auth->has_current_forum_perm(BS_MODE_EDIT_POST))
			$options[] = $locale->lang('allow_edit_own_posts_true');
		else
			$options[] = $locale->lang('allow_edit_own_posts_false');

		if($auth->has_global_permission('delete_own_posts') ||
				$auth->has_current_forum_perm(BS_MODE_DELETE_POSTS))
			$options[] = $locale->lang('allow_delete_own_posts_true');
		else
			$options[] = $locale->lang('allow_delete_own_posts_false');

		// change search-display-state?
		if($input->get_var(BS_URL_KW,'get',FWS_Input::STRING) == 'clap_options')
			$functions->clap_area('topic_options');

		$clap_cookie = $input->get_var(BS_COOKIE_PREFIX.'topic_options','cookie',FWS_Input::INT_BOOL);
		$img_type = ($clap_cookie === null || $clap_cookie == 1) ? 'open' : 'closed';

		$tpl->add_variables(array(
			'img_type' => $img_type,
			'hide_options' => ($clap_cookie === null || $clap_cookie == 1) ? '' : ' style="display: none;"',
			'cookie_prefix' => BS_COOKIE_PREFIX,
			'options' => $options,
			'php_self' => $input->get_var('PHP_SELF','server',FWS_Input::STRING),
			'action_param' => BS_URL_ACTION,
			'fid_param' => BS_URL_FID,
			'fid' => $input->get_var(BS_URL_FID,'get',FWS_Input::ID),
			'hidden_fields' => $hidden_fields,
			'order_ins' => $order_ins,
			'ascdesc_ins' => $ascdesc_ins,
			'tps_ins' => $tps_ins,
			'number_of_threads' => $forum_data->get_threads()
		));
	}
}
?>