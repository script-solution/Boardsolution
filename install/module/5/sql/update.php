<?php
/**
 * Contains the update-SQL-class.
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The update-SQL-class. Performs the update of the database from BS 1.2x to BS 1.3
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_5_SQL_Update extends BS_Install_Module_5_SQL_Base
{
	/**
	 * @see BS_Install_Module_5_SQL_Base::run()
	 */
	protected function run()
	{
		$db = FWS_Props::get()->db();
		$user = FWS_Props::get()->user();
		
		$dbname = $user->get_session_data('database','');
		$prefix = $user->get_session_data('table_prefix','bs_');
		
		// change default charset and collation of the db
		if($db->get_server_version() >= '4.1')
		{
			$db->execute(
				'ALTER DATABASE `'.$dbname.'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;'
			);
		}
		
		$consts = BS_Install_Module_5_Helper::get_tables();
		
		// bbcodes
		$this->add_to_log('Creating Table "'.$consts['BS_TB_BBCODES'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_BBCODES']}` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `name` varchar(30) NOT NULL,
		  `type` varchar(15) NOT NULL default 'inline',
		  `content` varchar(30) NOT NULL,
		  `replacement` text NOT NULL,
		  `replacement_param` text NOT NULL,
		  `param` enum('no','optional','required') NOT NULL,
		  `param_type` varchar(10) NOT NULL,
		  `allow_nesting` tinyint(1) unsigned NOT NULL,
		  `ignore_whitespace` tinyint(1) unsigned NOT NULL,
		  `ignore_unknown_tags` tinyint(1) unsigned NOT NULL,
		  `allowed_content` varchar(255) NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$db->execute("INSERT INTO `{$consts['BS_TB_BBCODES']}`
			(`id`, `name`, `type`, `content`, `replacement`, `replacement_param`, `param`, `param_type`, `allow_nesting`, `ignore_whitespace`, `ignore_unknown_tags`, `allowed_content`)
			VALUES
			(1, 'b', 'inline', 'text', '<b>{TEXT}</b>', '', 'no', 'text', 0, 0, 0, 'inline,link'),
			(2, 'i', 'inline', 'text', '<i>{TEXT}</i>', '', 'no', 'text', 0, 0, 0, 'inline,link'),
			(3, 'u', 'inline', 'text', '<u>{TEXT}</u>', '', 'no', 'text', 0, 0, 0, 'inline,link'),
			(6, 's', 'inline', 'text', '<s>{TEXT}</s>', '', 'no', 'text', 0, 0, 0, 'inline,link'),
			(7, 'font', 'inline', 'font', '', '<span style=\"font-family: {PARAM};\">{TEXT}</span>', 'required', 'text', 0, 0, 0, 'inline,link'),
			(8, 'color', 'inline', 'text', '', '<span style=\"color: {PARAM};\">{TEXT}</span>', 'required', 'color', 0, 0, 0, 'inline,link'),
			(9, 'size', 'inline', 'size', '', '<span style=\"font-size: {PARAM}px;\">{TEXT}</span>', 'required', 'integer', 0, 0, 0, 'inline,link'),
			(10, 'url', 'link', 'url', '<a target=\"_blank\" href=\"{TEXT}\">{TEXT}</a>', '<a target=\"_blank\" href=\"{PARAM}\">{TEXT}</a>', 'optional', 'url', 0, 0, 0, 'inline'),
			(11, 'mail', 'link', 'text', '<a href=\"mailto:{TEXT}\">{TEXT}</a>', '<a href=\"mailto:{PARAM}\">{TEXT}</a>', 'optional', 'mail', 0, 0, 0, 'inline'),
			(12, 'img', 'inline', 'image', '<img src=\"{TEXT}\" alt=\"{TEXT}\" style=\"max-width: 100%;\" />', '', 'no', 'text', 0, 0, 0, ''),
			(13, 'quote', 'block', 'text', '<div class=\"bs_quote_section\"><div class=\"bs_quote_section_top\"><b>{LANG=quote}</b>:</div><div class=\"bs_quote_section_main\">{TEXT}</div></div>', '<div class=\"bs_quote_section\"><div class=\"bs_quote_section_top\"><b>{PARAM}</b> {LANG=wrotethefollowing}:</div><div class=\"bs_quote_section_main\">{TEXT}</div></div>', 'optional', 'text', 1, 0, 0, 'inline,link,block'),
			(14, 'code', 'block', 'code', '<div class=\"bs_quote_section\" style=\"overflow: hidden;\"><div class=\"bs_quote_section_top\"><b>{LANG=code}:</b></div><div class=\"bs_quote_section_main\" style=\"overflow: auto;\">{TEXT}</div></div>', '<div class=\"bs_quote_section\" style=\"overflow: hidden;\"><div class=\"bs_quote_section_top\"><b>{PARAM}:</b></div><div class=\"bs_quote_section_main\" style=\"overflow: auto;\">{TEXT}</div></div>', 'optional', 'identifier', 0, 0, 0, ''),
			(15, 'list', 'block', 'list', '{TEXT}', '{TEXT}', 'optional', 'text', 1, 0, 0, 'inline,link,block'),
			(16, 'topic', 'link', 'text', '', '<a target=\"_blank\" href=\"{BSF}action=redirect&amp;loc=show_topic&amp;tid={PARAM}\">{TEXT}</a>', 'required', 'integer', 0, 0, 0, 'inline'),
			(17, 'post', 'link', 'text', '', '<a target=\"_blank\" href=\"{BSF}action=redirect&amp;loc=show_post&amp;id={PARAM}\">{TEXT}</a>', 'required', 'integer', 0, 0, 0, 'inline'),
			(18, 'sub', 'inline', 'text', '<sub>{TEXT}</sub>', '', 'no', 'text', 0, 0, 0, 'inline,link'),
			(19, 'sup', 'inline', 'text', '<sup>{TEXT}</sup>', '', 'no', 'text', 0, 0, 0, 'inline,link'),
			(20, 'left', 'block', 'text', '<div align=\"left\">{TEXT}</div>', '', 'no', 'text', 0, 0, 0, 'inline,link'),
			(21, 'center', 'block', 'text', '<div align=\"center\">{TEXT}</div>', '', 'no', 'text', 0, 0, 0, 'inline,link'),
			(22, 'right', 'block', 'text', '<div align=\"right\">{TEXT}</div>', '', 'no', 'text', 0, 0, 0, 'inline,link'),
			(23, 'att', 'inline', 'attachment', '', '{TEXT}', 'required', 'text', 0, 0, 0, 'inline'),
			(24, 'attimg', 'inline', 'attachmentimage', '{TEXT}', '', 'no', 'text', 0, 0, 0, '');");
		$this->add_to_log_success();
		
		// cache
		$this->add_to_log('Changing Table "'.$consts['BS_TB_CACHE'].'"...');
		$db->execute("DELETE FROM `{$consts['BS_TB_CACHE']}` WHERE table_name = 'smileys'");
		$db->execute(
			"ALTER TABLE `{$consts['BS_TB_CACHE']}`
			 CHANGE `table_name` `table_name` enum('banlist','intern','languages','moderators','themes',
			 																			 'user_groups','user_ranks','config','user_fields',
			 																			 'stats','tasks','acp_access','bots') NOT NULL"
 		);
 		$this->add_to_log_success();
		
 		// config
		$this->add_to_log('Creating Table "'.$consts['BS_TB_CONFIG'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_CONFIG']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `name` varchar(255) NOT NULL,
		  `custom_title` varchar(255) NOT NULL,
		  `group_id` int(10) unsigned NOT NULL,
		  `sort` int(10) unsigned NOT NULL,
		  `type` varchar(30) NOT NULL,
		  `properties` text NOT NULL,
		  `suffix` varchar(100) NOT NULL,
		  `value` text NOT NULL,
		  `default` text NOT NULL,
		  `affects_msgs` tinyint(1) unsigned NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();

		// config-groups
		$this->add_to_log('Creating Table "'.$consts['BS_TB_CONFIG_GROUPS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_CONFIG_GROUPS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `parent_id` int(10) unsigned NOT NULL,
		  `name` varchar(100) NOT NULL,
		  `title` varchar(255) NOT NULL,
		  `sort` int(10) unsigned NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
 		
		BS_Install_Module_5_Helper::generate_settings();
		
		$cfg = $db->get_row("SELECT * FROM `".$prefix."config`");
		$cfgconv = array(
			'account_activation' => 'account_activation',
			'allow_custom_lang' => 'allow_custom_lang',
			'allow_custom_style' => 'allow_custom_style',
			'allow_email_changes' => 'allow_email_changes',
			'allow_ghost_mode' => 'allow_ghost_mode',
			'attachments_enable' => 'attachments_enable',
			'attachments_filetypes' => 'attachments_filetypes',
			'attachments_images_resize_method' => 'attachments_images_resize_method',
			'attachments_images_show' => 'attachments_images_show',
			'attachments_max_filesize' => 'attachments_max_filesize',
			'attachments_max_number' => 'attachments_max_number',
			'attachments_max_per_post' => 'attachments_max_per_post',
			'attachments_per_user' => 'attachments_per_user',
			'badwords_default_replacement' => 'badwords_default_replacement',
			'badwords' => 'badwords_definitions',
			'badwords_highlight' => 'badwords_highlight',
			'badwords_spaces_around' => 'badwords_spaces_around',
			'board_disabled_text' => 'board_disabled_text',
			'board_email' => 'board_email',
			'confirm_email_addresses' => 'confirm_email_addresses',
			'cookie_domain' => 'cookie_domain',
			'cookie_path' => 'cookie_path',
			'current_topic_enable' => 'current_topic_enable',
			'current_topic_loc' => 'current_topic_loc',
			'current_topic_num' => 'current_topic_num',
			'default_forum_lang' => 'default_forum_lang',
			'default_forum_style' => 'default_forum_style',
			'default_posts_order' => 'default_posts_order',
			'default_timezone' => 'default_timezone',
			'display_ministats' => 'display_ministats',
			'display_similar_topics' => 'display_similar_topics',
			'enable_avatars' => 'enable_avatars',
			'enable_badwords' => 'enable_badwords',
			'enable_board' => 'enable_board',
			'enable_calendar' => 'enable_calendar',
			'enable_calendar_events' => 'enable_calendar_events',
			'enable_email_notification' => 'enable_email_notification',
			'enable_emails' => 'enable_emails',
			'enable_error_log' => 'enable_error_log',
			'enable_events' => 'enable_events',
			'enable_faq' => 'enable_faq',
			'enable_gzip' => 'enable_gzip',
			'linklist_enable' => 'enable_linklist',
			'enable_memberlist' => 'enable_memberlist',
			'enable_moderators' => 'enable_moderators',
			'enable_news_feeds' => 'enable_news_feeds',
			'pm_enable' => 'enable_pms',
			'enable_polls' => 'enable_polls',
			'enable_portal' => 'enable_portal',
			'enable_portal_news' => 'enable_portal_news',
			'enable_post_count' => 'enable_post_count',
			'enable_search' => 'enable_search',
			'enable_security_code' => 'enable_security_code',
			'enable_signatures' => 'enable_signatures',
			'enable_stats' => 'enable_stats',
			'enable_user_ranks' => 'enable_user_ranks',
			'error_log_days' => 'error_log_days',
			'events_in_calendar' => 'enable_calendar_events',
			'forum_title' => 'forum_title',
			'get_email_new_account' => 'get_email_new_account',
			'get_email_new_link' => 'get_email_new_link',
			'hide_denied_forums' => 'hide_denied_forums',
			'ip_log_days' => 'ip_log_days',
			'ip_validation_type' => 'ip_validation_type',
			'linklist_permission' => 'linklist_activate_links',
			'lnkdesc_allowed_tags' => 'desc_allowed_tags',
			'lnkdesc_enable_bbcode' => 'desc_enable_bbcode',
			'lnkdesc_enable_smileys' => 'desc_enable_smileys',
			'lnkdesc_max_images' => 'desc_max_images',
			'lnkdesc_max_length' => 'desc_max_length',
			'lnkdesc_max_smileys' => 'desc_max_smileys',
			'mail_method' => 'mail_method',
			'max_forum_subscriptions' => 'max_forum_subscriptions',
			'max_poll_options' => 'max_poll_options',
			'max_topic_subscriptions' => 'max_topic_subscriptions',
			'members_per_page' => 'members_per_page',
			'mod_color' => 'mod_color',
			'mod_delete_posts' => 'mod_delete_posts',
			'mod_delete_topics' => 'mod_delete_topics',
			'mod_edit_posts' => 'mod_edit_posts',
			'mod_edit_topics' => 'mod_edit_topics',
			'mod_lock_topics' => 'mod_lock_topics',
			'mod_mark_topics_important' => 'mod_mark_topics_important',
			'mod_move_topics' => 'mod_move_topics',
			'mod_openclose_topics' => 'mod_openclose_topics',
			'mod_rank_empty_image' => 'mod_rank_empty_image',
			'mod_rank_filled_image' => 'mod_rank_filled_image',
			'mod_split_posts' => 'mod_split_posts',
			'msgs_allow_java_applet' => 'msgs_allow_java_applet',
			'msgs_code_highlight' => 'msgs_code_highlight',
			'msgs_code_line_numbers' => 'msgs_code_line_numbers',
			'msgs_default_bbcode_mode' => 'msgs_default_bbcode_mode',
			'msgs_max_line_length' => 'msgs_max_line_length',
			'msgs_parse_urls' => 'msgs_parse_urls',
			'news_count' => 'news_count',
			'news_forums' => 'news_forums',
			'pm_max_inbox' => 'pm_max_inbox',
			'pm_max_outbox' => 'pm_max_outbox',
			'post_font_pool' => 'post_font_pool',
			'post_show_edited' => 'post_show_edited',
			'post_stats_type' => 'post_stats_type',
			'posts_allowed_tags' => 'posts_allowed_tags',
			'posts_enable_bbcode' => 'posts_enable_bbcode',
			'posts_enable_smileys' => 'posts_enable_smileys',
			'posts_max_images' => 'posts_max_images',
			'posts_max_length' => 'posts_max_length',
			'posts_max_smileys' => 'posts_max_smileys',
			'posts_per_page' => 'posts_per_page',
			'profile_max_avatars' => 'profile_max_avatars',
			'profile_max_img_filesize' => 'profile_max_img_filesize',
			'profile_max_login_tries' => 'profile_max_login_tries',
			'profile_max_pw_len' => 'profile_max_pw_len',
			'profile_max_user_changes' => 'profile_max_user_changes',
			'profile_max_user_len' => 'profile_max_user_len',
			'profile_min_user_len' => 'profile_min_user_len',
			'profile_user_special_chars' => 'profile_user_special_chars',
			'show_always_page_split' => 'show_always_page_split',
			'sig_allowed_tags' => 'sig_allowed_tags',
			'sig_enable_bbcode' => 'sig_enable_bbcode',
			'sig_enable_smileys' => 'sig_enable_smileys',
			'sig_max_images' => 'sig_max_images',
			'sig_max_length' => 'sig_max_length',
			'sig_max_smileys' => 'sig_max_smileys',
			'similar_topic_num' => 'similar_topic_num',
			'smtp_host' => 'smtp_host',
			'smtp_login' => 'smtp_login',
			'smtp_password' => 'smtp_password',
			'smtp_port' => 'smtp_port',
			'smtp_use_ltgt' => 'smtp_use_ltgt',
			'thread_hot_posts_count' => 'thread_hot_posts_count',
			'thread_hot_views_count' => 'thread_hot_views_count',
			'thread_max_title_len' => 'thread_max_title_len',
			'threads_per_page' => 'threads_per_page',
			'use_captcha_for_guests' => 'use_captcha_for_guests',
			'validate_user_agent' => 'validate_user_agent',
		);
		foreach($cfgconv as $cfgold => $cfgnew)
		{
			$db->execute(
				'UPDATE '.$consts['BS_TB_CONFIG'].'
				 SET value = "'.$cfg[$cfgold].'"
				 WHERE name = "'.$cfgnew.'"'
			);
		}
		// some special cases
		$db->execute(
			'UPDATE '.$consts['BS_TB_CONFIG'].'
			 SET value = "'.$cfg['attachments_images_width'].'x'.$cfg['attachments_images_height'].'"
			 WHERE name = "attachments_images_size"'
		);
		$db->execute(
			'UPDATE '.$consts['BS_TB_CONFIG'].'
			 SET value = "'.$cfg['profile_max_img_width'].'x'.$cfg['profile_max_img_height'].'"
			 WHERE name = "profile_max_img_size"'
		);
		$db->execute(
			'UPDATE '.$consts['BS_TB_CONFIG'].'
			 SET value = "'.$cfg['badwords'].'"
			 WHERE name = "badwords_definitions"'
		);
		$cfgspam = array(
			'spam_email','spam_linkadd','spam_linkview','spam_pm','spam_post','spam_reg','spam_search',
			'spam_thread','spam_threadview'
		);
		foreach($cfgspam as $name)
		{
			$db->execute(
				'UPDATE '.$consts['BS_TB_CONFIG'].'
				 SET value = "'.($cfg[$name.'_on'] == 1 ? $cfg[$name.'_time'] : 0).'"
				 WHERE name = "'.$name.'"'
			);
		}
 		
 		// config
		$this->add_to_log('Deleting Table "'.$prefix.'config"...');
		$db->execute("DROP TABLE `".$prefix."config`");
 		$this->add_to_log_success();
		
 		// events-announcements
		$this->add_to_log('Creating Table "'.$consts['BS_TB_EVENT_ANN'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_EVENT_ANN']}` (
		  `event_id` int(10) unsigned NOT NULL,
		  `user_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY  (`event_id`,`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		// events
		$this->add_to_log('Changing Table "'.$consts['BS_TB_EVENTS'].'"...');
		$rows = $db->get_rows(
			"SELECT id,announced_user FROM `{$consts['BS_TB_EVENTS']}`
			 WHERE announced_user != '0' AND announced_user != ''"
		);
		foreach($rows as $row)
		{
			$user = FWS_Array_Utils::advanced_explode(',',$row['announced_user']);
			foreach($user as $uid)
			{
				$db->execute(
					"INSERT INTO `{$consts['BS_TB_EVENT_ANN']}` (event_id,user_id)
					 VALUES (".$row['id'].",".$uid.");"
				);
			}
		}
		$db->execute(
			"ALTER TABLE `{$consts['BS_TB_EVENTS']}`
			 ADD `description_posted` text NOT NULL,
			 DROP `announced_user`"
 		);
 		$this->add_to_log_success();
		
 		// forum-perms
		$this->add_to_log('Creating Table "'.$consts['BS_TB_FORUMS_PERM'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_FORUMS_PERM']}` (
		  `forum_id` int(10) unsigned NOT NULL,
		  `group_id` int(10) unsigned NOT NULL,
		  `type` enum('reply','topic','poll','event') NOT NULL,
		  PRIMARY KEY  (`forum_id`,`group_id`,`type`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		// forums
		$this->add_to_log('Changing Table "'.$consts['BS_TB_FORUMS'].'"...');
		$types = array(
			'permission_thread' => 'topic',
			'permission_poll' => 'poll',
			'permission_event' => 'event',
			'permission_post' => 'reply'
		);
		$rows = $db->get_rows(
			"SELECT id,`permission_thread`,`permission_poll`,`permission_event`,`permission_post`
			 FROM `{$consts['BS_TB_FORUMS']}`"
		);
		foreach($rows as $row)
		{
			foreach(array_keys($types) as $type)
			{
				$ids = FWS_Array_Utils::advanced_explode(',',$row[$type]);
				foreach($ids as $gid)
				{
					$db->execute(
						"INSERT INTO `{$consts['BS_TB_FORUMS_PERM']}` (forum_id,group_id,type)
						 VALUES (".$row['id'].",".$gid.",'".$types[$type]."');"
					);
				}
			}
		}
		$db->execute(
			"ALTER TABLE `{$consts['BS_TB_FORUMS']}`
			 DROP `permission_thread`,
			 DROP `permission_poll`,
			 DROP `permission_event`,
			 DROP `permission_post`"
 		);
 		$this->add_to_log_success();
		
 		// link-votes
		$this->add_to_log('Creating Table "'.$consts['BS_TB_LINK_VOTES'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_LINK_VOTES']}` (
		  `user_id` int(10) unsigned NOT NULL,
		  `link_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY  (`user_id`,`link_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		// pms
		$this->add_to_log('Changing Table "'.$consts['BS_TB_PMS'].'"...');
		$db->execute(
			"ALTER TABLE `{$consts['BS_TB_PMS']}`
			 ADD `attachment_count` tinyint(3) unsigned NOT NULL default '0'"
 		);
 		$this->add_to_log_success();
		
 		// unread
		$this->add_to_log('Creating Table "'.$consts['BS_TB_UNREAD'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_UNREAD']}` (
		  `user_id` int(10) unsigned NOT NULL,
		  `post_id` int(10) unsigned NOT NULL,
		  `is_news` tinyint(1) unsigned NOT NULL,
		  PRIMARY KEY  (`user_id`,`post_id`)
		) TYPE=MyISAM");
		$this->add_to_log_success();
		
		// unsent posts
		$this->add_to_log('Creating Table "'.$consts['BS_TB_UNSENT_POSTS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_UNSENT_POSTS']}` (
		  `user_id` int(10) unsigned NOT NULL,
		  `post_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY  (`user_id`,`post_id`)
		) TYPE=MyISAM");
		$this->add_to_log_success();
		
 		// profile
		$this->add_to_log('Changing Table "'.$consts['BS_TB_PROFILES'].'"...');
		$rows = $db->get_rows(
			"SELECT id,linkvotes FROM `{$consts['BS_TB_PROFILES']}` WHERE linkvotes != ''"
		);
		foreach($rows as $row)
		{
			$user = FWS_Array_Utils::advanced_explode(',',$row['linkvotes']);
			foreach($user as $lid)
			{
				$db->execute(
					"INSERT INTO `{$consts['BS_TB_LINK_VOTES']}` (user_id,link_id)
					 VALUES (".$row['id'].",".$lid.");"
				);
			}
		}
		$rows = $db->get_rows(
			"SELECT id,unread_topics FROM `{$consts['BS_TB_PROFILES']}` WHERE unread_topics != ''"
		);
		foreach($rows as $row)
		{
			$unread = unserialize($row['unread_topics']);
			if(isset($unread['t']))
			{
				foreach($unread['t'] as $tid => $tunread)
				{
					$db->execute(
						"INSERT INTO `{$consts['BS_TB_UNREAD']}` (user_id,post_id,is_news)
						 VALUES (".$row['id'].",".$tunread[0].",".(isset($unread['n'][$tid]) ? 1 : 0).");"
					);
				}
			}
		}
		$rows = $db->get_rows(
			"SELECT id,unsent_posts FROM `{$consts['BS_TB_PROFILES']}` WHERE unsent_posts != ''"
		);
		foreach($rows as $row)
		{
			$pids = FWS_Array_Utils::advanced_explode(',',$row['unsent_posts']);
			foreach($pids as $pid)
			{
				$db->execute(
					"INSERT INTO `{$consts['BS_TB_UNSENT_POSTS']}` (user_id,post_id)
					 VALUES (".$row['id'].",".$pid.");"
				);
			}
		}
		$db->execute(
			"ALTER TABLE `{$consts['BS_TB_PROFILES']}`
			 DROP `linkvotes`,
			 DROP `unread_topics`,
			 DROP `unsent_posts`,
			 CHANGE `timezone` `timezone` varchar(100) NOT NULL default 'Europe/Berlin',
			 ADD `startmodule` enum('portal','forums') NOT NULL default 'portal'"
 		);
 		$db->execute(
 			"UPDATE `{$consts['BS_TB_PROFILES']}` SET timezone = 'Europe/Berlin'"
 		);
 		$this->add_to_log_success();
 		
 		// themes
		$this->add_to_log('Adding new themes into "'.$consts['BS_TB_THEMES'].'"...');
		$db->execute("INSERT INTO `".$consts['BS_TB_THEMES']."` (`theme_folder`, `theme_name`)
									VALUES ('minimal', 'Minimal');");
		$db->execute("INSERT INTO `".$consts['BS_TB_THEMES']."` (`theme_folder`, `theme_name`)
									VALUES ('bots', 'Bots');");
		$db->execute(
			'UPDATE `'.$consts['BS_TB_THEMES'].'` SET
			 theme_folder = "desert", theme_name = "Desert"
			 WHERE theme_folder = "green_gray"'
		);
 		$this->add_to_log_success();
		
 		// unread hide
		$this->add_to_log('Creating Table "'.$consts['BS_TB_UNREAD_HIDE'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_UNREAD_HIDE']}` (
		  `user_id` int(10) unsigned NOT NULL,
		  `forum_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY  (`user_id`,`forum_id`)
		) TYPE=MyISAM");
		$this->add_to_log_success();
		
		// user-groups
		$this->add_to_log('Changing Table "'.$consts['BS_TB_USER_GROUPS'].'"...');
		$db->execute(
			"ALTER TABLE `{$consts['BS_TB_USER_GROUPS']}`
			ADD `view_useronline_list` tinyint(1) unsigned NOT NULL default '0',
  		ADD `is_team` tinyint(1) unsigned NOT NULL default '0'"
  	);
		$this->add_to_log_success();
		
		// tasks
		$this->add_to_log('Deleting Task in "'.$consts['BS_TB_TASKS'].'"...');
		$db->execute("DELETE FROM `{$consts['BS_TB_TASKS']}` WHERE task_file = 'events.php'");
		$this->add_to_log_success();
		
		
		$this->add_to_log('Generating DB-Cache...');
		include_once(FWS_Path::server_app().'config/mysql.php');
		$cache = FWS_Props::get()->cache();
		$cache->refresh_all();
		$this->add_to_log_success();
	}
}
?>