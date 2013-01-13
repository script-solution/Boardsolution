<?php
/**
 * Contains the helper-class for step4
 * 
 * @package			Boardsolution
 * @subpackage	install.module
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
 * Helper-methods for the step4
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_4_Helper extends FWS_UtilBase
{
	/**
	 * @return array all occurred errors
	 */
	public static function get_errors()
	{
		$user = FWS_Props::get()->user();
		if($user->get_session_data('install_type','full') == 'full')
			$tbls = self::check_full();
		else
			$tbls = self::check_update();
		
		$errors = array();
		foreach($tbls as $status)
		{
			if($status !== true)
				$errors[] = $status;
		}
		return $errors;
	}
	
	/**
	 * Checks the db for an update
	 *
	 * @return array an array with the result for each table
	 */
	public static function check_update()
	{
		$db = FWS_Props::get()->db();
		$user = FWS_Props::get()->user();
		
		if(!$db->is_connected())
			return array();
		
		$fields = array(
			'acp_access' => array(
				'id', 'module', 'access_type', 'access_value', 
			),
			'activation' => array(
				'user_id', 'user_key', 
			),
			'attachments' => array(
				'id', 'pm_id', 'thread_id', 'post_id', 'poster_id', 'attachment_size', 'attachment_path', 
				'downloads', 
			),
			'avatars' => array(
				'id', 'av_pfad', 'user', 
			),
			'banlist' => array(
				'id', 'bann_name', 'bann_type', 
			),
			'bots' => array(
				'id', 'bot_name', 'bot_match', 'bot_ip_start', 'bot_ip_end', 'bot_access', 
			),
			'cache' => array(
				'table_name', 'table_content', 
			),
			'change_email' => array(
				'user_id', 'user_key', 'email_address', 'email_date', 
			),
			'change_pw' => array(
				'user_id', 'user_key', 'email_date', 
			),
			'config' => array(
				'posts_per_page', 'threads_per_page', 'members_per_page', 'spam_post_on', 'spam_post_time', 
				'spam_thread_on', 'spam_thread_time', 'spam_reg_on', 'spam_reg_time', 'spam_threadview_on', 
				'spam_threadview_time', 'spam_pm_on', 'spam_pm_time', 'spam_linkadd_on', 'spam_linkadd_time', 
				'spam_linkview_on', 'spam_linkview_time', 'spam_email_on', 'spam_email_time', 'forum_title', 
				'post_stats_type', 'post_font_pool', 'post_show_edited', 'thread_max_title_len', 
				'thread_hot_posts_count', 'thread_hot_views_count', 'profile_max_img_width', 
				'profile_max_img_height', 'profile_min_user_len', 'profile_max_user_len', 'profile_max_pw_len', 
				'profile_max_img_filesize', 'profile_max_avatars', 'profile_user_special_chars', 'linklist_enable', 
				'linklist_permission', 'events_in_calendar', 'pm_max_inbox', 'pm_max_outbox', 'pm_enable', 
				'get_email_new_account', 'get_email_new_link', 'account_activation', 'enable_email_notification', 
				'enable_emails', 'max_poll_options', 'cookie_path', 'cookie_domain', 'board_email', 
				'show_always_page_split', 'badwords_highlight', 'badwords_spaces_around', 'badwords', 
				'badwords_default_replacement', 'enable_badwords', 'enable_memberlist', 'enable_stats', 
				'enable_faq', 'enable_calendar', 'enable_search', 'enable_avatars', 'enable_polls', 
				'enable_events', 'enable_gzip', 'enable_security_code', 'attachments_max_number', 
				'attachments_max_per_post', 'attachments_per_user', 'attachments_max_space_usage', 
				'attachments_max_filesize', 'attachments_filetypes', 'attachments_enable', 
				'attachments_images_show', 'attachments_images_width', 'attachments_images_height', 
				'attachments_images_resize_method', 'default_forum_style', 'allow_custom_style', 
				'default_forum_lang', 'allow_custom_lang', 'current_topic_enable', 'current_topic_loc', 
				'current_topic_num', 'default_timezone', 'default_daylight_saving', 'enable_board', 
				'board_disabled_text', 'mod_edit_posts', 'mod_delete_posts', 'mod_split_posts', 'mod_edit_topics', 
				'mod_delete_topics', 'mod_move_topics', 'mod_openclose_topics', 'mod_mark_topics_important', 
				'mod_color', 'mod_rank_filled_image', 'mod_rank_empty_image', 'hide_denied_forums', 
				'allow_ghost_mode', 'max_topic_subscriptions', 'max_forum_subscriptions', 'spam_search_on', 
				'spam_search_time', 'enable_signatures', 'enable_post_count', 'enable_user_ranks', 
				'profile_max_user_changes', 'profile_max_login_tries', 'ip_validation_type', 'validate_user_agent', 
				'display_similar_topics', 'similar_topic_num', 'default_posts_order', 'use_captcha_for_guests', 
				'events_cache', 'enable_calendar_events', 'enable_portal', 'enable_portal_news', 'news_forums', 
				'news_count', 'mail_method', 'smtp_host', 'smtp_port', 'smtp_login', 'smtp_password', 
				'smtp_use_ltgt', 'enable_moderators', 'mod_lock_topics', 'enable_news_feeds', 
				'allow_email_changes', 'msgs_default_bbcode_mode', 'msgs_parse_urls', 'msgs_code_highlight', 
				'msgs_code_line_numbers', 'msgs_max_line_length', 'msgs_allow_java_applet', 'posts_enable_smileys', 
				'posts_enable_bbcode', 'posts_max_length', 'posts_max_images', 'posts_max_smileys', 
				'posts_allowed_tags', 'sig_enable_smileys', 'sig_enable_bbcode', 'sig_max_length', 
				'sig_max_images', 'sig_max_smileys', 'sig_allowed_tags', 'lnkdesc_enable_smileys', 
				'lnkdesc_enable_bbcode', 'lnkdesc_max_length', 'lnkdesc_max_images', 'lnkdesc_max_smileys', 
				'lnkdesc_allowed_tags', 'enable_error_log', 'error_log_days', 'confirm_email_addresses', 
				'display_ministats', 'ip_log_days', 
			),
			'events' => array(
				'id', 'tid', 'user_id', 'event_title', 'event_begin', 'event_end', 'announced_user', 
				'max_announcements', 'description', 'event_location', 'timeout', 
			),
			'forums' => array(
				'id', 'parent_id', 'sortierung', 'forum_name', 'description', 'forum_type', 'forum_is_intern', 
				'threads', 'posts', 'lastpost_id', 'permission_thread', 'permission_poll', 'permission_event', 
				'permission_post', 'increase_experience', 'display_subforums', 'forum_is_closed', 
			),
			'intern' => array(
				'id', 'fid', 'access_type', 'access_value', 
			),
			'languages' => array(
				'id', 'lang_folder', 'lang_name', 
			),
			'links' => array(
				'id', 'category', 'link_url', 'link_desc', 'clicks', 'votes', 'vote_points', 'link_date', 
				'user_id', 'active', 'link_desc_posted', 
			),
			'log_errors' => array(
				'id', 'query', 'user_id', 'date', 'message', 'backtrace', 
			),
			'log_ips' => array(
				'id', 'user_ip', 'user_id', 'user_agent', 'date', 'action', 
			),
			'moderators' => array(
				'id', 'user_id', 'rid', 
			),
			'pms' => array(
				'id', 'receiver_id', 'sender_id', 'pm_type', 'pm_title', 'pm_text', 'pm_date', 'pm_read', 
				'pm_text_posted', 
			),
			'polls' => array(
				'id', 'pid', 'option_name', 'option_value', 'multichoice', 
			),
			'poll_votes' => array(
				'poll_id', 'user_id', 
			),
			'posts' => array(
				'id', 'rubrikid', 'threadid', 'post_user', 'post_time', 'text', 'post_an_user', 'post_an_mail', 
				'use_smileys', 'use_bbcode', 'ip_adresse', 'edit_lock', 'edited_times', 'edited_date', 
				'edited_user', 'text_posted', 
			),
			'profiles' => array(
				'id', 'add_hp', 'add_icq', 'add_irc', 'avatar', 'signatur', 'registerdate', 'posts', 'exppoints', 
				'logins', 'lastlogin', 'active', 'banned', 'linkvotes', 'default_font', 'allow_pms', 'ghost_mode', 
				'unread_topics', 'last_unread_update', 'online', 'bbcode_mode', 'attach_signature', 
				'allow_board_emails', 'forum_style', 'forum_lang', 'default_email_notification', 'timezone', 
				'daylight_saving', 'user_group', 'enable_pm_email', 'email_display_mode', 'emails_include_post', 
				'signature_posted', 'add_birthday', 'username_changes', 'login_tries', 'store_unread_in_cookie', 
				'posts_order', 'unsent_posts', 'email_notification_type', 'last_email_notification', 
				'last_search_time', 
			),
			'user_ranks' => array(
				'id', 'rank', 'post_to', 'post_from', 
			),
			'search' => array(
				'id', 'session_id', 'search_date', 'result_ids', 'result_type', 'keywords', 'search_mode', 
			),
			'sessions' => array(
				'session_id', 'user_id', 'user_ip', 'date', 'location', 'user_agent', 'session_data', 
			),
			'smileys' => array(
				'id', 'smiley_path', 'primary_code', 'secondary_code', 'is_base', 'sort_key', 
			),
			'subscriptions' => array(
				'id', 'forum_id', 'topic_id', 'user_id', 'sub_date', 
			),
			'tasks' => array(
				'id', 'task_title', 'task_file', 'task_interval', 'last_execution', 'enabled', 'task_time', 
			),
			'themes' => array(
				'id', 'theme_folder', 'theme_name', 
			),
			'topics' => array(
				'id', 'rubrikid', 'name', 'post_time', 'post_user', 'symbol', 'type', 'comallow', 'views', 'moved', 
				'posts', 'post_an_user', 'post_an_mail', 'lastpost_id', 'lastpost_time', 'lastpost_user', 
				'lastpost_an_user', 'important', 'thread_closed', 'moved_rid', 'moved_tid', 'locked', 
			),
			'user_bans' => array(
				'id', 'user_id', 'baned_user', 
			),
			'user_fields' => array(
				'id', 'field_name', 'field_type', 'field_length', 'field_sort', 'field_show_type', 'display_name', 
				'allowed_values', 'field_validation', 'field_suffix', 'field_custom_display', 'field_is_required', 
				'field_edit_notice', 'display_always', 
			),
			'user_groups' => array(
				'id', 'group_title', 'group_color', 'group_rank_filled_image', 'group_rank_empty_image', 
				'overrides_mod', 'is_super_mod', 'view_memberlist', 'view_linklist', 'view_stats', 'view_calendar', 
				'view_search', 'view_userdetails', 'edit_own_posts', 'delete_own_posts', 'edit_own_threads', 
				'delete_own_threads', 'openclose_own_threads', 'send_mails', 'add_new_link', 'attachments_add', 
				'attachments_download', 'add_cal_event', 'edit_cal_event', 'delete_cal_event', 'subscribe_forums', 
				'view_user_ip', 'is_visible', 'view_online_locations', 'disable_ip_blocks', 'enter_board', 
				'view_user_online_detail', 'always_edit_poll_options', 
			),
		);
		
		$res = array();
		$prefix = $user->get_session_data('table_prefix','bs_');
		foreach($fields as $table => $tblfields)
		{
			try
			{
				$db->execute(
					"SELECT ".implode(',',$tblfields)." FROM `".$prefix.$table."` LIMIT 1"
				);
				$res[$prefix.$table] = true;
			}
			catch(FWS_DB_Exception_QueryFailed $ex)
			{
				$res[$prefix.$table] = '<b>'.$prefix.$table.'</b>: '.$ex->get_mysql_error();
			}
		}
		return $res;
	}
	
	/**
	 * Checks the db for a full-installation
	 *
	 * @return array the status of each table
	 */
	public static function check_full()
	{
		$db = FWS_Props::get()->db();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		
		if(!$db->is_connected())
			return array();
		
		$count = 0;
		$res = array();
		$prefix = $user->get_session_data('table_prefix','bs_');
		foreach(BS_Install_Module_5_Helper::get_tables() as $name)
		{
			try
			{
				$db->execute("SELECT * FROM `".$name."` LIMIT 1");
				$res[$name] = sprintf($locale->lang('error_table_exists'),$name);
			}
			catch(FWS_DB_Exception_QueryFailed $ex)
			{
				$res[$name] = true;
			}
		}
		return $res;
	}
}
?>