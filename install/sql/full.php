<?php
/**
 * Contains the full-installation-SQL-class.
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The full-installation-SQL-class.
 * Creates all tables in the database and inserts the initially required data.
 * 
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_InstallSQL_full extends BS_InstallSQL
{
	public function run()
	{
		include(PLIB_Path::inner().'config/community.php');
		include(PLIB_Path::inner().'config/mysql.php');
		include(PLIB_Path::inner().'src/mysql.php');
		
		// we have to init $this->db here because we need it later on
		$this->db = new BS_MySQL(BS_MYSQL_HOST,BS_MYSQL_LOGIN,BS_MYSQL_PASSWORD,BS_MYSQL_DATABASE);
		
		// change default charset and collation of the db
		if($this->db->get_server_version() >= '4.1')
		{
			$this->db->sql_qry(
				'ALTER DATABASE `'.BS_MYSQL_DATABASE.'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;'
			);
		}
		
		$this->add_to_log('Creating Table "'.BS_TB_ACP_ACCESS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_ACP_ACCESS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `module` varchar(200) NOT NULL default '',
		  `access_type` enum('user','group') NOT NULL default 'user',
		  `access_value` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_ACTIVATION.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_ACTIVATION."` (
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `user_key` varchar(32) NOT NULL default '',
		  PRIMARY KEY  (`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_ATTACHMENTS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_ATTACHMENTS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `pm_id` int(10) unsigned NOT NULL default '0',
		  `thread_id` int(10) unsigned NOT NULL default '0',
		  `post_id` int(10) unsigned NOT NULL default '0',
		  `poster_id` int(10) unsigned NOT NULL default '0',
		  `attachment_size` int(10) unsigned NOT NULL default '0',
		  `attachment_path` varchar(255) NOT NULL default '',
		  `downloads` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `poster_id` (`poster_id`),
		  KEY `thread_id` (`thread_id`),
		  KEY `post_id` (`post_id`),
		  KEY `pm_id` (`pm_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_AVATARS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_AVATARS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `av_pfad` varchar(255) NOT NULL default '',
		  `user` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `user` (`user`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_BANS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_BANS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `bann_name` varchar(255) NOT NULL default '',
		  `bann_type` varchar(5) NOT NULL default '',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM AUTO_INCREMENT=1 ;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_BOTS.'"...');
		$this->db->sql_qry(
			"CREATE TABLE `".BS_TB_BOTS."` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `bot_name` varchar(255) NOT NULL default '',
			  `bot_match` varchar(255) NOT NULL default '',
			  `bot_ip_start` varchar(15) NOT NULL default '',
			  `bot_ip_end` varchar(15) NOT NULL default '',
			  `bot_access` tinyint(1) unsigned NOT NULL default 0,
			  PRIMARY KEY  (`id`)
			) TYPE=MyISAM;"
		);
		
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Googlebot', 'Googlebot/', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('YahooBot', 'Yahoo! Slurp;', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('MSNBot', 'msnbot/', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('ZyBorg', 'ZyBorg/', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('SeekBot', 'Seekbot/', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Exabot', 'Exabot/', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Gigabot', 'Gigabot/', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('iCCrawler', 'iCCrawler', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Mediapartners-Google', 'Mediapartners-Google/', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('MJ12bot', 'MJ12bot/', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('W3C-Validator', 'W3C_Validator/', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Accoona', 'Accoona-AI-Agent/', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('MSN-Media-Bot', 'msnbot-media/1.0', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Twiceler-Bot', 'Twiceler-0.9', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('RedBot', 'RedBot/redbot-1.0', '', '', 1);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_BOTS."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Ask.com', 'Ask Jeeves/Teoma;', '', '', 1);"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_CACHE.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_CACHE."` (
		  `table_name` enum('banlist','intern','languages','moderators','smileys','themes',
												'user_groups','user_ranks','config','user_fields','stats','tasks',
												'acp_access','bots') NOT NULL default 'banlist',
		  `table_content` longtext NOT NULL ,
		  PRIMARY KEY  (`table_name`)
		) TYPE=MyISAM;");
		
		$empty = serialize(array());
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('banlist','".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('intern','".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('languages', '".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('moderators','".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('smileys', '".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('themes', '".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('user_groups','".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('user_ranks','".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('config', '".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('user_fields', '".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('stats','".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('tasks','".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('acp_access','".$empty."');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_CACHE."` (`table_name`, `table_content`) VALUES ('bots','".$empty."');"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_CHANGE_EMAIL.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_CHANGE_EMAIL."` (
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `user_key` varchar(32) NOT NULL default '',
		  `email_address` varchar(255) NOT NULL default '',
		  `email_date` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_CHANGE_PW.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_CHANGE_PW."` (
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `user_key` varchar(32) NOT NULL default '',
		  `email_date` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_DESIGN.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_DESIGN."` (
		  `posts_per_page` tinyint(3) unsigned NOT NULL default '0',
		  `threads_per_page` tinyint(3) unsigned NOT NULL default '0',
		  `members_per_page` tinyint(3) unsigned NOT NULL default '0',
		  `spam_post_on` tinyint(1) unsigned NOT NULL default '0',
		  `spam_post_time` mediumint(6) unsigned NOT NULL default '0',
		  `spam_thread_on` tinyint(1) unsigned NOT NULL default '0',
		  `spam_thread_time` mediumint(6) unsigned NOT NULL default '0',
		  `spam_reg_on` tinyint(1) unsigned NOT NULL default '0',
		  `spam_reg_time` mediumint(6) unsigned NOT NULL default '0',
		  `spam_threadview_on` tinyint(1) unsigned NOT NULL default '0',
		  `spam_threadview_time` mediumint(6) unsigned NOT NULL default '0',
		  `spam_pm_on` tinyint(1) unsigned NOT NULL default '0',
		  `spam_pm_time` mediumint(6) unsigned NOT NULL default '0',
		  `spam_linkadd_on` tinyint(1) unsigned NOT NULL default '0',
		  `spam_linkadd` mediumint(6) unsigned NOT NULL default '0',
		  `spam_linkview_on` tinyint(1) unsigned NOT NULL default '0',
		  `spam_linkview_time` mediumint(6) unsigned NOT NULL default '0',
		  `spam_email_on` tinyint(1) unsigned NOT NULL default '0',
		  `spam_email_time` mediumint(6) unsigned NOT NULL default '0',
		  `forum_title` varchar(80) NOT NULL default '',
		  `post_stats_type` enum('disabled','current_rank','continuous','newbie_friendly') NOT NULL default 'newbie_friendly',
		  `post_font_pool` text NOT NULL,
		  `post_show_edited` enum('always','not_lastpost','never') NOT NULL default 'always',
		  `thread_max_title_len` mediumint(5) unsigned NOT NULL default '0',
		  `thread_hot_posts_count` int(10) unsigned NOT NULL default '0',
		  `thread_hot_views_count` int(10) unsigned NOT NULL default '0',
		  `profile_max_img_width` mediumint(5) unsigned NOT NULL default '0',
		  `profile_max_img_height` mediumint(5) unsigned NOT NULL default '0',
		  `profile_min_user_len` smallint(2) unsigned NOT NULL default '0',
		  `profile_max_user_len` smallint(2) unsigned NOT NULL default '0',
		  `profile_max_pw_len` smallint(2) unsigned NOT NULL default '0',
		  `profile_max_img_filesize` mediumint(8) unsigned NOT NULL default '0',
		  `profile_max_avatars` smallint(3) unsigned NOT NULL default '0',
		  `profile_user_special_chars` tinyint(1) unsigned NOT NULL default '0',
		  `enable_linklist` tinyint(1) unsigned NOT NULL default '0',
		  `linklist_activate_links` tinyint(1) unsigned NOT NULL default '0',
		  `events_in_calendar` text NOT NULL,
		  `pm_max_inbox` smallint(6) unsigned NOT NULL default '0',
		  `pm_max_outbox` smallint(6) unsigned NOT NULL default '0',
		  `enable_pms` tinyint(1) unsigned NOT NULL default '0',
		  `get_email_new_account` tinyint(1) unsigned NOT NULL default '0',
		  `get_email_new_link` tinyint(1) unsigned NOT NULL default '0',
		  `account_activation` enum('none','email','admin') NOT NULL default 'email',
		  `enable_email_notification` tinyint(1) unsigned NOT NULL default '0',
		  `enable_emails` tinyint(1) unsigned NOT NULL default '0',
		  `max_poll_options` tinyint(3) unsigned NOT NULL default '0',
		  `cookie_path` varchar(100) NOT NULL default '',
		  `cookie_domain` varchar(100) NOT NULL default '',
		  `board_email` varchar(100) NOT NULL default '',
		  `show_always_page_split` tinyint(1) unsigned NOT NULL default '0',
		  `badwords_highlight` varchar(100) NOT NULL default 'none',
		  `badwords_spaces_around` tinyint(1) unsigned NOT NULL default '0',
		  `badwords` text NOT NULL,
		  `badwords_default_replacement` varchar(100) NOT NULL default '',
		  `enable_badwords` tinyint(1) unsigned NOT NULL default '0',
		  `enable_memberlist` tinyint(1) unsigned NOT NULL default '0',
		  `enable_stats` tinyint(1) unsigned NOT NULL default '0',
		  `enable_faq` tinyint(1) unsigned NOT NULL default '0',
		  `enable_calendar` tinyint(1) unsigned NOT NULL default '0',
		  `enable_search` tinyint(1) unsigned NOT NULL default '0',
		  `enable_avatars` tinyint(1) unsigned NOT NULL default '0',
		  `enable_polls` tinyint(1) unsigned NOT NULL default '0',
		  `enable_events` tinyint(1) unsigned NOT NULL default '0',
		  `enable_gzip` tinyint(1) unsigned NOT NULL default '0',
		  `enable_security_code` tinyint(1) NOT NULL default '0',
		  `attachments_max_number` smallint(5) unsigned NOT NULL default '0',
		  `attachments_max_per_post` tinyint(3) unsigned NOT NULL default '0',
		  `attachments_per_user` smallint(5) unsigned NOT NULL default '0',
		  `attachments_max_space_usage` int(10) unsigned NOT NULL default '0',
		  `attachments_max_filesize` int(10) unsigned NOT NULL default '0',
		  `attachments_filetypes` varchar(255) NOT NULL default '',
		  `attachments_enable` tinyint(1) unsigned NOT NULL default '0',
		  `attachments_images_show` tinyint(1) unsigned NOT NULL default '0',
		  `attachments_images_width` smallint(4) unsigned NOT NULL default '0',
		  `attachments_images_height` smallint(4) unsigned NOT NULL default '0',
		  `attachments_images_resize_method` enum('width_fixed','height_fixed','both_fixed') NOT NULL default 'width_fixed',
		  `default_forum_style` smallint(3) unsigned NOT NULL default '0',
		  `allow_custom_style` tinyint(1) unsigned NOT NULL default '0',
		  `default_forum_lang` smallint(3) unsigned NOT NULL default '0',
		  `allow_custom_lang` tinyint(1) unsigned NOT NULL default '0',
		  `current_topic_enable` tinyint(1) unsigned NOT NULL default '0',
		  `current_topic_loc` enum('top','bottom','portal') NOT NULL default 'portal',
		  `current_topic_num` smallint(3) unsigned NOT NULL default '0',
		  `default_timezone` decimal(2,1) NOT NULL default '0.0',
		  `default_daylight_saving` tinyint(1) unsigned NOT NULL default '0',
		  `enable_board` tinyint(1) unsigned NOT NULL default '1',
		  `board_disabled_text` text NOT NULL,
		  `mod_edit_posts` tinyint(1) unsigned NOT NULL default '0',
		  `mod_delete_posts` tinyint(1) unsigned NOT NULL default '0',
		  `mod_split_posts` tinyint(1) unsigned NOT NULL default '0',
		  `mod_edit_topics` tinyint(1) unsigned NOT NULL default '0',
		  `mod_delete_topics` tinyint(1) unsigned NOT NULL default '0',
		  `mod_move_topics` tinyint(1) unsigned NOT NULL default '0',
		  `mod_openclose_topics` tinyint(1) unsigned NOT NULL default '0',
		  `mod_mark_topics_important` tinyint(1) unsigned NOT NULL default '0',
		  `mod_color` varchar(6) NOT NULL default '',
		  `mod_rank_filled_image` varchar(255) NOT NULL default '',
		  `mod_rank_empty_image` varchar(255) NOT NULL default '',
		  `hide_denied_forums` tinyint(1) unsigned NOT NULL default '0',
		  `allow_ghost_mode` tinyint(1) unsigned NOT NULL default '0',
		  `max_topic_subscriptions` int(10) unsigned NOT NULL default '0',
		  `max_forum_subscriptions` int(10) unsigned NOT NULL default '0',
		  `spam_search_on` tinyint(1) unsigned NOT NULL default 0,
		  `spam_search_time` int(10) unsigned NOT NULL default 0,
		  `enable_signatures` tinyint(1) unsigned NOT NULL default '0',
		  `enable_post_count` tinyint(1) unsigned NOT NULL default '0',
		  `enable_user_ranks` tinyint(1) unsigned NOT NULL default '0',
		  `profile_max_user_changes` tinyint(3) NOT NULL default '0',
		  `profile_max_login_tries` tinyint(3) unsigned NOT NULL default '0',
		  `ip_validation_type` enum('A.B.C.D','A.B.C','A.B','none') NOT NULL default 'A.B.C.D',
		  `validate_user_agent` tinyint(1) unsigned NOT NULL default '0',
		  `display_similar_topics` tinyint(1) unsigned NOT NULL default '0',
		  `similar_topic_num` tinyint(3) unsigned NOT NULL default '0',
		  `default_posts_order` enum('ASC','DESC') NOT NULL default 'ASC',
		  `use_captcha_for_guests` tinyint(1) unsigned NOT NULL default 0,
		  `events_cache` text NOT NULL,
		  `enable_calendar_events` tinyint(1) unsigned NOT NULL default 0,
		  `enable_portal` tinyint(1) unsigned NOT NULL default 0,
		  `enable_portal_news` tinyint(1) unsigned NOT NULL default 0,
		  `news_forums` text NOT NULL,
		  `news_count` int(10) unsigned NOT NULL default 0,
		  `mail_method` enum('mail','smtp') NOT NULL default 'mail',
		  `smtp_host` varchar(255) NOT NULL default '',
		  `smtp_port` int(10) unsigned NOT NULL default 0,
		  `smtp_login` varchar(255) NOT NULL default '',
		  `smtp_password` varchar(255) NOT NULL default '',
		  `smtp_use_ltgt` tinyint(1) unsigned NOT NULL default 0,
		  `enable_moderators` tinyint(1) unsigned NOT NULL default 0,
		  `mod_lock_topics` tinyint(1) unsigned NOT NULL default 0,
		  `enable_news_feeds` tinyint(1) unsigned NOT NULL default 0,
		  `allow_email_changes` tinyint(1) unsigned NOT NULL default 0,
		  `msgs_default_bbcode_mode` enum('simple','advanced','applet') NOT NULL default 'simple',
		  `msgs_parse_urls` tinyint(1) unsigned NOT NULL default 0,
		  `msgs_code_highlight` tinyint(1) unsigned NOT NULL default 0,
		  `msgs_code_line_numbers` tinyint(1) unsigned NOT NULL default 0,
		  `msgs_max_line_length` mediumint(4) unsigned NOT NULL default 0,
		  `msgs_allow_java_applet` tinyint(1) unsigned NOT NULL default 0,
		  `posts_enable_smileys` tinyint(1) unsigned NOT NULL default 0,
		  `posts_enable_bbcode` tinyint(1) unsigned NOT NULL default 0,
		  `posts_max_length` int(10) unsigned NOT NULL default 0,
		  `posts_max_images` mediumint(4) unsigned NOT NULL default 0,
		  `posts_max_smileys` mediumint(4) unsigned NOT NULL default 0,
		  `posts_allowed_tags` text NOT NULL,
		  `sig_enable_smileys` tinyint(1) unsigned NOT NULL default 0,
		  `sig_enable_bbcode` tinyint(1) unsigned NOT NULL default 0,
		  `sig_max_length` int(10) unsigned NOT NULL default 0,
		  `sig_max_images` mediumint(4) unsigned NOT NULL default 0,
		  `sig_max_smileys` mediumint(4) unsigned NOT NULL default 0,
		  `sig_allowed_tags` text NOT NULL,
		  `lnkdesc_enable_smileys` tinyint(1) unsigned NOT NULL default 0,
		  `lnkdesc_enable_bbcode` tinyint(1) unsigned NOT NULL default 0,
		  `lnkdesc_max_length` int(10) unsigned NOT NULL default 0,
		  `lnkdesc_max_images` mediumint(4) unsigned NOT NULL default 0,
		  `lnkdesc_max_smileys` mediumint(4) unsigned NOT NULL default 0,
		  `lnkdesc_allowed_tags` text NOT NULL,
		  `enable_error_log` tinyint(1) unsigned NOT NULL default 0,
		  `error_log_days` mediumint(4) unsigned NOT NULL default 0,
		  `confirm_email_addresses` tinyint(1) unsigned NOT NULL default 0,
		  `display_ministats` tinyint(1) unsigned NOT NULL default 0,
			`ip_log_days` mediumint(4) unsigned NOT NULL default 0
		) TYPE=MyISAM;");
		
		$selected_lang = $this->input->get_var('lang','get',PLIB_Input::STRING);
		switch($selected_lang)
		{
			case 'ger_du':
				$lang_id = 3;
				break;
			case 'ger_sie':
				$lang_id = 1;
				break;
			case 'en':
				$lang_id = 2;
				break;
		}
		
		$this->db->sql_qry("INSERT INTO `".BS_TB_DESIGN."` SET
			`posts_per_page` = 15,
			`threads_per_page` = 20,
			`members_per_page` = 15,
			`spam_post_on` = 1,
			`spam_post_time` = 60,
			`spam_thread_on` = 1,
			`spam_thread_time` = 60,
			`spam_reg_on` = 1,
			`spam_reg_time` = 3600,
			`spam_threadview_on` = 1,
			`spam_threadview_time` = 1800,
			`spam_pm_on` = 1,
			`spam_pm_time` = 60,
			`spam_linkadd_on` = 1,
			`spam_linkadd` = 60,
			`spam_linkview_on` = 1,
			`spam_linkview_time` = 3600,
			`spam_email_on` = 1,
			`spam_email_time` = 120,
			`forum_title` = 'Boardsolution',
			`post_stats_type` = 'newbie_friendly',
			`post_font_pool` = 'Verdana,Helvetica,Courier New,Arial',
			`post_show_edited` = 'always',
			`thread_max_title_len` = 40,
			`thread_hot_posts_count` = 15,
			`thread_hot_views_count` = 300,
			`profile_max_img_width` = 150,
			`profile_max_img_height` = 120,
			`profile_min_user_len` = 3,
			`profile_max_user_len` = 30,
			`profile_max_pw_len` = 30,
			`profile_max_img_filesize` = 50,
			`profile_max_avatars` = 8,
			`profile_user_special_chars` = 0,
			`enable_linklist` = 1,
			`linklist_activate_links` = 1,
			`events_in_calendar` = '',
			`pm_max_inbox` = 0,
			`pm_max_outbox` = 0,
			`enable_pms` = 1,
			`get_email_new_account` = 0,
			`get_email_new_link` = 1,
			`account_activation` = 'email',
			`enable_email_notification` = 1,
			`enable_emails` = 1,
			`max_poll_options` = 15,
			`cookie_path` = '/',
			`cookie_domain` = '',
			`board_email` = 'board@domain.de',
			`show_always_page_split` = 1,
			`badwords_highlight` = '&lt;i&gt;{value}&lt;/i&gt;',
			`badwords_spaces_around` = 1,
			`badwords` = '',
			`badwords_default_replacement` = '*censored*',
			`enable_badwords` = 0,
			`enable_memberlist` = 1,
			`enable_stats` = 1,
			`enable_faq` = 1,
			`enable_calendar` = 1,
			`enable_search` = 1,
			`enable_avatars` = 1,
			`enable_polls` = 1,
			`enable_events` = 1,
			`enable_gzip` = 0,
			`enable_security_code` = 1,
			`attachments_max_number` = 0,
			`attachments_max_per_post` = 3,
			`attachments_per_user` = 0,
			`attachments_max_space_usage` = 0,
			`attachments_max_filesize` = 512000,
			`attachments_filetypes` = 'zip|rar|tar|ini|txt|jpeg|png|jpg|gif|xml',
			`attachments_enable` = 1,
			`attachments_images_show` = 1,
			`attachments_images_width` = 300,
			`attachments_images_height` = 200,
			`attachments_images_resize_method` = 'width_fixed',
			`default_forum_style` = 1,
			`allow_custom_style` = 1,
			`default_forum_lang` = ".$lang_id.",
			`allow_custom_lang` = 1,
			`current_topic_enable` = 1,
			`current_topic_loc` = 'portal',
			`current_topic_num` = 5,
			`default_timezone` = 1.0,
			`default_daylight_saving` = 2,
			`enable_board` = 0,
			`board_disabled_text` = 'Das Board ist aufgrund von Wartungsarbeiten vorübergehend deaktiviert.\nBitte haben Sie Verständnis.',
			`mod_edit_posts` = 1,
			`mod_delete_posts` = 1,
			`mod_split_posts` = 1,
			`mod_edit_topics` = 1,
			`mod_delete_topics` = 1,
			`mod_move_topics` = 1,
			`mod_openclose_topics` = 1,
			`mod_mark_topics_important` = 1,
			`mod_color` = '006600',
			`mod_rank_filled_image` = 'images/ranks/mod.gif',
			`mod_rank_empty_image` = 'images/ranks/mod.gif',
			`hide_denied_forums` = 1,
			`allow_ghost_mode` = 1,
			`max_topic_subscriptions` = 20,
			`max_forum_subscriptions` = 2,
			`spam_search_on` = 1,
			`spam_search_time` = 10,
			`enable_signatures` = 1,
			`enable_post_count` = 1,
			`enable_user_ranks` = 1,
			`profile_max_user_changes` = 4,
			`profile_max_login_tries` = 2,
			`ip_validation_type` = 'A.B.C.D',
			`validate_user_agent` = 0,
			`display_similar_topics` = 1,
			`similar_topic_num` = 5,
			`default_posts_order` = 'ASC',
			`use_captcha_for_guests` = 1,
			`events_cache` = 'a:0:{}',
			`enable_calendar_events` = 1,
			`enable_portal` = 1,
			`enable_portal_news` = 1,
			`news_forums` = '0',
			`news_count` = 10,
			`mail_method` = 'mail',
			`smtp_host` = '',
			`smtp_port` = 25,
			`smtp_login` = '',
			`smtp_password` = '',
			`smtp_use_ltgt` = 0,
			`enable_moderators` = 1,
			`mod_lock_topics` = 1,
			`enable_news_feeds` = 1,
			`allow_email_changes` = 1,
			`msgs_default_bbcode_mode` = 'advanced',
			`msgs_parse_urls` = 1,
			`msgs_code_highlight` = 1,
			`msgs_code_line_numbers` = 1,
			`msgs_max_line_length` = 100,
			`msgs_allow_java_applet` = 1,
			`posts_enable_smileys` = 1,
			`posts_enable_bbcode` = 1,
			`posts_max_length` = 15000,
			`posts_max_images` = 3,
			`posts_max_smileys` = 15,
			`posts_allowed_tags` = 'b,i,u,size,font,color,url,mail,img,code,quote,list,topic,post,left,right,center,s,sup,sub',
			`sig_enable_smileys` = 1,
			`sig_enable_bbcode` = 1,
			`sig_max_length` = 500,
			`sig_max_images` = 1,
			`sig_max_smileys` = 3,
			`sig_allowed_tags` = 'b,i,u,size,font,color,url,mail,img,code,quote,list,topic,post',
			`lnkdesc_enable_smileys` = 1,
			`lnkdesc_enable_bbcode` = 1,
			`lnkdesc_max_length` = 500,
			`lnkdesc_max_images` = 1,
			`lnkdesc_max_smileys` = 3,
			`lnkdesc_allowed_tags` = 'b,i,u,size,font,color,url,mail,img,code,quote,list,topic,post',
			`enable_error_log` = 1,
			`error_log_days` = 120,
			`confirm_email_addresses` = 1,
			`display_ministats` = 0,
			`ip_log_days` = 21");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_EVENTS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_EVENTS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `tid` int(10) unsigned NOT NULL default '0',
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `event_title` varchar(200) NOT NULL default '',
		  `event_begin` int(10) unsigned NOT NULL default '0',
		  `event_end` int(10) unsigned NOT NULL default '0',
		  `announced_user` text NOT NULL,
		  `max_announcements` mediumint(5) NOT NULL default '0',
		  `description` text NOT NULL,
		  `event_location` varchar(100) NOT NULL default '',
		  `timeout` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `tid` (`tid`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_FORUMS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_FORUMS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `parent_id` int(10) unsigned NOT NULL default '0',
		  `sortierung` int(10) unsigned NOT NULL default '0',
		  `forum_name` varchar(50) NOT NULL default '',
		  `description` varchar(255) NOT NULL default '',
		  `forum_type` enum('contains_cats','contains_threads') NOT NULL default 'contains_cats',
		  `forum_is_intern` tinyint(1) unsigned NOT NULL default '0',
		  `threads` int(10) unsigned NOT NULL default '0',
		  `posts` int(10) unsigned NOT NULL default '0',
		  `lastpost_id` int(10) NOT NULL default '0',
		  `permission_thread` text NOT NULL,
		  `permission_poll` text NOT NULL,
		  `permission_event` text NOT NULL,
		  `permission_post` text NOT NULL,
		  `increase_experience` tinyint(1) unsigned NOT NULL default '0',
		  `display_subforums` tinyint(1) unsigned NOT NULL default '0',
		  `forum_is_closed` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `lastpost_id` (`lastpost_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_INTERN.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_INTERN."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `fid` int(10) unsigned NOT NULL default '0',
		  `access_type` enum('group','user') NOT NULL default 'user',
		  `access_value` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `fid` (`fid`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_LANGS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_LANGS."` (
		  `id` smallint(3) unsigned NOT NULL auto_increment,
		  `lang_folder` varchar(20) NOT NULL default '',
		  `lang_name` varchar(50) NOT NULL default '',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_LANGS."` (`id`, `lang_folder`, `lang_name`) VALUES
			 (1, 'ger_sie', 'Deutsch (Sie-Version)');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_LANGS."` (`id`, `lang_folder`, `lang_name`) VALUES
			 (2, 'en', 'English');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_LANGS."` (`id`, `lang_folder`, `lang_name`) VALUES
			 (3, 'ger_du', 'Deutsch (Du-Version)');"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_LANGS."` (`id`, `lang_folder`, `lang_name`) VALUES
			 (4, 'dk', 'Dansk');"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_LINKS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_LINKS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `category` varchar(100) NOT NULL default '',
		  `link_url` varchar(255) NOT NULL default '',
		  `link_desc` text NOT NULL,
		  `clicks` int(10) unsigned NOT NULL default '0',
		  `votes` int(10) unsigned NOT NULL default '0',
		  `vote_points` int(10) unsigned NOT NULL default '0',
		  `link_date` int(10) unsigned NOT NULL default '0',
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `active` tinyint(1) unsigned NOT NULL default '0',
		  `link_desc_posted` text NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `user_id` (`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_LOG_ERRORS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_LOG_ERRORS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `query` text NOT NULL,
		  `user_id` int(10) unsigned NOT NULL default 0,
		  `date` int(10) unsigned NOT NULL default 0,
		  `message` text NOT NULL,
		  `backtrace` text NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_LOG_IPS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_LOG_IPS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `user_ip` varchar(15) NOT NULL default '',
		  `user_id` int(10) unsigned NOT NULL default 0,
		  `user_agent` varchar(255) NOT NULL default '',
		  `date` int(10) unsigned NOT NULL default 0,
		  `action` varchar(20) NOT NULL default '',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_MODS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_MODS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `rid` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `rid` (`rid`),
		  KEY `user_id` (`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_PMS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_PMS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `receiver_id` int(10) unsigned NOT NULL default '0',
		  `sender_id` int(10) unsigned NOT NULL default '0',
		  `pm_type` enum('inbox','outbox') NOT NULL default 'inbox',
		  `pm_title` varchar(80) NOT NULL default '',
		  `pm_text` text NOT NULL,
		  `pm_date` int(10) unsigned NOT NULL default '0',
		  `pm_read` tinyint(1) unsigned NOT NULL default '0',
		  `pm_text_posted` text NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `receiver_id` (`receiver_id`),
		  KEY `sender_id` (`sender_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_POLL.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_POLL."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `pid` int(10) unsigned NOT NULL default '0',
		  `option_name` varchar(100) NOT NULL default '',
		  `option_value` int(10) unsigned NOT NULL default '0',
		  `multichoice` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `pid` (`pid`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_POLL_VOTES.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_POLL_VOTES."` (
		  `poll_id` int(10) unsigned NOT NULL default '0',
		  `user_id` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`poll_id`,`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_POSTS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_POSTS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `rubrikid` int(10) unsigned NOT NULL default '0',
		  `threadid` int(10) unsigned NOT NULL default '0',
		  `post_user` int(10) unsigned NOT NULL default '0',
		  `post_time` int(10) unsigned NOT NULL default '0',
		  `text` text NOT NULL,
		  `post_an_user` varchar(150) default '',
		  `post_an_mail` varchar(150) default '',
		  `use_smileys` tinyint(1) unsigned NOT NULL default '0',
		  `use_bbcode` tinyint(1) unsigned NOT NULL default '0',
		  `ip_adresse` varchar(15) NOT NULL default '',
		  `edit_lock` tinyint(1) unsigned NOT NULL default '0',
		  `edited_times` tinyint(3) unsigned NOT NULL default '0',
		  `edited_date` int(10) unsigned NOT NULL default '0',
		  `edited_user` int(10) unsigned NOT NULL default '0',
		  `text_posted` text NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `rubrikid` (`rubrikid`),
		  KEY `threadid` (`threadid`),
		  KEY `post_user` (`post_user`),
		  KEY `edited_user` (`edited_user`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_PROFILES.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_PROFILES."` (
		  `id` int(10) unsigned NOT NULL default '0',
		  `add_hp` varchar(255) NOT NULL default '',
		  `add_icq` int(15) default NULL default 0,
		  `add_irc` varchar(120) NOT NULL default '',
		  `avatar` int(10) unsigned NOT NULL default '0',
		  `signatur` text NOT NULL,
		  `registerdate` int(10) unsigned NOT NULL default '0',
		  `posts` int(10) unsigned NOT NULL default '0',
		  `exppoints` int(10) unsigned NOT NULL default '0',
		  `logins` int(10) unsigned NOT NULL default '0',
		  `lastlogin` int(10) unsigned NOT NULL default '0',
		  `active` tinyint(1) unsigned NOT NULL default '0',
		  `banned` tinyint(1) unsigned NOT NULL default '0',
		  `linkvotes` text NOT NULL,
		  `default_font` varchar(50) NOT NULL default '',
		  `allow_pms` tinyint(1) unsigned NOT NULL default '0',
		  `ghost_mode` tinyint(1) unsigned NOT NULL default '0',
		  `unread_topics` text NOT NULL,
		  `last_unread_update` int(10) unsigned NOT NULL default '0',
		  `online` tinyint(1) unsigned NOT NULL default '0',
		  `bbcode_mode` enum('simple','advanced','applet') NOT NULL default 'simple',
		  `attach_signature` tinyint(1) unsigned NOT NULL default '1',
		  `allow_board_emails` tinyint(1) unsigned NOT NULL default '0',
		  `forum_style` smallint(3) unsigned NOT NULL default '0',
		  `forum_lang` smallint(3) unsigned NOT NULL default '0',
		  `default_email_notification` tinyint(1) unsigned NOT NULL default '0',
		  `timezone` decimal(2,1) NOT NULL default '1.0',
		  `daylight_saving` tinyint(1) unsigned NOT NULL default '0',
		  `user_group` text NOT NULL,
		  `enable_pm_email` tinyint(1) unsigned NOT NULL default '0',
		  `email_display_mode` enum('hide','jumble','default') NOT NULL default 'default',
		  `emails_include_post` tinyint(1) unsigned NOT NULL default '0',
		  `signature_posted` text NOT NULL,
		  `add_birthday` date NOT NULL default '0000-00-00',
		  `username_changes` tinyint(3) unsigned NOT NULL default '0',
		  `login_tries` tinyint(3) unsigned NOT NULL default '0',
		  `store_unread_in_cookie` tinyint(1) unsigned NOT NULL default '0',
		  `posts_order` enum('ASC','DESC') NOT NULL default 'ASC',
		  `unsent_posts` text NOT NULL,
		  `email_notification_type` enum('immediatly','1day','2days','1week') NOT NULL default 'immediatly',
		  `last_email_notification` int(10) unsigned NOT NULL default '0',
		  `last_search_time` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `avatar` (`avatar`)
		) TYPE=MyISAM;");
		
		$this->db->sql_qry("INSERT INTO `".BS_TB_PROFILES."` SET
			`id` = 1,
			`registerdate` = ".time().",
			`active` = 1,
			`banned` = 0,
			`allow_pms` = 1,
			`ghost_mode` = 0,
			`bbcode_mode` = 'advanced',
			`attach_signature` = 1,
			`allow_board_emails` = 1,
			`daylight_saving` = 2,
			`user_group` = '".BS_STATUS_ADMIN.",',
			`email_display_mode` = 'jumble',
			`posts_order` = 'ASC';");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_SEARCH.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_SEARCH."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `session_id` varchar(32) NOT NULL default '',
		  `search_date` int(10) unsigned NOT NULL default '0',
		  `result_ids` text NOT NULL,
		  `result_type` enum('topics','posts','pms','pm_history') NOT NULL default 'topics',
		  `keywords` text NOT NULL,
		  `search_mode` enum('default','user_posts','user_topics','pms','topic','history') NOT NULL default 'default',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_SESSIONS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_SESSIONS."` (
		  `session_id` varchar(32) NOT NULL default '',
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `user_ip` varchar(15) NOT NULL default '',
		  `date` int(10) unsigned NOT NULL default '0',
		  `location` varchar(255) NOT NULL default '',
		  `user_agent` varchar(255) NOT NULL default '',
		  `session_data` text NOT NULL,
		  PRIMARY KEY  (`session_id`),
		  KEY `user_id` (`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_SMILEYS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_SMILEYS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `smiley_path` varchar(255) NOT NULL default '',
		  `primary_code` varchar(15) NOT NULL default '',
		  `secondary_code` varchar(15) NOT NULL default '',
		  `is_base` tinyint(1) unsigned NOT NULL default '0',
		  `sort_key` int(10) unsigned NOT NULL default 0,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$i = 0;
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (10, 'smile.png', ':-)', ':)', 1, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (12, 'wink.png', ';-)', ';)', 1, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (1, 'happy.png', '=)', '', 1, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (2, 'frown.png', ':-(', ':(', 1, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (14, 'bigsmile.png', ':D', '', 1, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (11, 'tongue.png', ':-P', ':P', 1, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (17, 'unsure.png', ':-/', ':/', 1, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (5, 'crying.png', ':cry:', '', 0, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (6, 'cool.png', '8-)', '8)', 1, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (9, 'rolleyes.png', ':roll:', '', 1, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (7, 'confused.png', ':confused:', ':??:', 1, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (13, 'shock.png', ':shock:', '', 1, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (15, 'holy.png', 'O:-)', 'O:)', 0, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (16, 'oops.png', ':ops:', '', 0, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (4, 'eek.png', ':o', '', 0, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (3, 'evil.png', ':evil:', '', 0, ".(++$i).");");
		$this->db->sql_qry("INSERT INTO `".BS_TB_SMILEYS."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (8, 'biggrin.png', ':-O', '', 1, ".(++$i).");");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_SUBSCR.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_SUBSCR."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `forum_id` int(10) unsigned NOT NULL default '0',
		  `topic_id` int(10) unsigned NOT NULL default '0',
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `sub_date` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `topic_id` (`topic_id`),
		  KEY `user_id` (`user_id`),
		  KEY `forum_id` (`forum_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_TASKS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_TASKS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `task_title` varchar(100) NOT NULL default '',
		  `task_file` varchar(100) NOT NULL default '',
		  `task_interval` int(10) unsigned NOT NULL default '0',
		  `last_execution` int(10) unsigned NOT NULL default '0',
		  `enabled` tinyint(1) unsigned NOT NULL default '0',
		  `task_time` time default NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$this->db->sql_qry("INSERT INTO `".BS_TB_TASKS."`
									(`id`, `task_title`, `task_file`, `task_interval`, `task_time`, `last_execution`, `enabled`)
									VALUES
									(1, 'attachments', 'attachments.php', 259200, NULL, 0, 1);");
		$this->db->sql_qry("INSERT INTO `".BS_TB_TASKS."`
									(`id`, `task_title`, `task_file`, `task_interval`, `task_time`, `last_execution`, `enabled`)
									VALUES
									(2, 'registrations', 'registrations.php', 604800, NULL, 0, 1);");
		$this->db->sql_qry("INSERT INTO `".BS_TB_TASKS."`
									(`id`, `task_title`, `task_file`, `task_interval`, `task_time`, `last_execution`, `enabled`)
									VALUES
									(3, 'change_email_pw', 'change_email_pw.php', 604800, NULL, 0, 1);");
		$this->db->sql_qry("INSERT INTO `".BS_TB_TASKS."`
									(`id`, `task_title`, `task_file`, `task_interval`, `task_time`, `last_execution`, `enabled`)
									VALUES
									(4, 'logged_ips', 'logged_ips.php', 604800, NULL, 0, 1);");
		$this->db->sql_qry("INSERT INTO `".BS_TB_TASKS."`
									(`id`, `task_title`, `task_file`, `task_interval`, `task_time`, `last_execution`, `enabled`)
									VALUES
									(5, 'subscriptions', 'subscriptions.php', 604800, NULL, 0, 1);");
		$this->db->sql_qry("INSERT INTO `".BS_TB_TASKS."`
									(`id`, `task_title`, `task_file`, `task_interval`, `task_time`, `last_execution`, `enabled`)
									VALUES
									(6, 'email_notification', 'email_notification.php', 86400, NULL, 0, 1);");
		$this->db->sql_qry("INSERT INTO `".BS_TB_TASKS."`
									(`id`, `task_title`, `task_file`, `task_interval`, `task_time`, `last_execution`, `enabled`)
									VALUES
									(7, 'events', 'events.php', 86400, '00:00:00', 0, 1);");
		$this->db->sql_qry("INSERT INTO `".BS_TB_TASKS."`
									(`id`, `task_title`, `task_file`, `task_interval`, `last_execution`, `enabled`, `task_time`)
									VALUES
									(8, 'error_log', 'error_log.php', 259200, 0, 1, NULL);");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_THEMES.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_THEMES."` (
		  `id` smallint(3) unsigned NOT NULL auto_increment,
		  `theme_folder` varchar(20) NOT NULL default '',
		  `theme_name` varchar(50) NOT NULL default '',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$this->db->sql_qry("INSERT INTO `".BS_TB_THEMES."` (`id`, `theme_folder`, `theme_name`)
									VALUES (1, 'default', 'Script-solution');");
		$this->db->sql_qry("INSERT INTO `".BS_TB_THEMES."` (`id`, `theme_folder`, `theme_name`)
									VALUES (2, 'green_gray', 'Green-Gray');");
		$this->db->sql_qry("INSERT INTO `".BS_TB_THEMES."` (`id`, `theme_folder`, `theme_name`)
									VALUES (3, 'black_red', 'Black-Red');");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_THREADS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_THREADS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `rubrikid` int(10) unsigned NOT NULL default '0',
		  `name` varchar(255) NOT NULL default '',
		  `post_time` int(10) unsigned NOT NULL default '0',
		  `post_user` int(10) unsigned NOT NULL default '0',
		  `symbol` tinyint(2) unsigned NOT NULL default '0',
		  `type` int(10) NOT NULL default '0',
		  `comallow` tinyint(1) unsigned NOT NULL default '0',
		  `views` int(10) unsigned NOT NULL default '0',
		  `moved` tinyint(1) unsigned NOT NULL default '0',
		  `posts` int(10) unsigned NOT NULL default '0',
		  `post_an_user` varchar(150) default '',
		  `post_an_mail` varchar(150) default '',
		  `lastpost_id` int(10) unsigned NOT NULL default '0',
		  `lastpost_time` int(10) unsigned NOT NULL default '0',
		  `lastpost_user` int(10) unsigned NOT NULL default '0',
		  `lastpost_an_user` varchar(150) default '',
		  `important` tinyint(1) unsigned NOT NULL default '0',
		  `thread_closed` tinyint(1) unsigned NOT NULL default '0',
		  `moved_rid` int(10) unsigned NOT NULL default '0',
		  `moved_tid` int(10) unsigned NOT NULL default '0',
		  `locked` tinyint(3) unsigned NOT NULL default 0,
		  PRIMARY KEY  (`id`),
		  KEY `rubrikid` (`rubrikid`),
		  KEY `post_user` (`post_user`),
		  KEY `lastpost_id` (`lastpost_id`),
		  KEY `lastpost_user` (`lastpost_user`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_USER.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_USER."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `user_name` varchar(50) NOT NULL default '',
		  `user_pw` varchar(32) NOT NULL default '',
		  `user_email` varchar(255) NOT NULL default '',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$admin_login = addslashes($this->functions->get_session_var('admin_login'));
		$admin_pw = $this->functions->get_session_var('admin_pw');
		$admin_email = $this->functions->get_session_var('admin_email');
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_USER."`
			 (`id`, `user_name`, `user_pw`, `user_email`)
			 VALUES
			 (1, '".$admin_login."', '".md5($admin_pw)."', '".$admin_email."');"
		);
		
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_USER_BANS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_USER_BANS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `baned_user` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `user_id` (`user_id`),
		  KEY `baned_user` (`baned_user`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_USER_FIELDS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_USER_FIELDS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `field_name` varchar(30) NOT NULL default '',
		  `field_type` enum('int','line','text','enum','date') NOT NULL default 'line',
		  `field_length` smallint(5) unsigned NOT NULL default '0',
		  `field_sort` int(10) unsigned NOT NULL default '0',
		  `field_show_type` tinyint(3) unsigned NOT NULL default '0',
		  `display_name` varchar(50) NOT NULL default '',
		  `allowed_values` varchar(255) NOT NULL default '',
		  `field_validation` varchar(100) NOT NULL default '',
		  `field_suffix` varchar(100) NOT NULL default '',
		  `field_custom_display` text NOT NULL,
		  `field_is_required` tinyint(1) unsigned NOT NULL default '0',
		  `field_edit_notice` text NOT NULL,
		  `display_always` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_USER_FIELDS."` SET `id` = 1,
			`field_name` = 'hp',
			`field_type` = 'line',
			`field_length` = 255,
			`field_sort` = 2,
			`field_show_type` = 11,
			`display_name` = 'Homepage',
			`allowed_values` = '',
			`field_validation` = '".addslashes('/^http:\\/\\/(\\S+)$/')."',
			`field_suffix` = '',
			`field_custom_display` = '".addslashes('<a target="_blank" class="{link_class}" href="{value}">{value}</a>')."',
			`field_is_required` = 0,
			`field_edit_notice` = '',
			`display_always` = 0;"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_USER_FIELDS."` SET `id` = 2,
			`field_name` = 'icq',
			`field_type` = 'int',
			`field_length` = 15,
			`field_sort` = 3,
			`field_show_type` = 9,
			`display_name` = 'ICQ',
			`allowed_values` = '',
			`field_validation` = '',
			`field_suffix` = '',
			`field_custom_display` = '".addslashes('<img src="http://online.mirabilis.com/scripts/online.dll?icq={value}&amp;img=5" alt="" align="top" /> {value}')."',
			`field_is_required` = 0,
			`field_edit_notice` = '',
			`display_always` = 1;"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_USER_FIELDS."` SET `id` = 3,
			`field_name` = 'irc',
			`field_type` = 'line',
			`field_length` = 120,
			`field_sort` = 4,
			`field_show_type` = 9,
			`display_name` = 'IRC',
			`allowed_values` = '',
			`field_validation` = '',
			`field_suffix` = '',
			`field_custom_display` = '".addslashes('<a class="{link_class}" href="{value}">{value}</a>')."',
			`field_is_required` = 0,
			`field_edit_notice` = '',
			`display_always` = 1;"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_USER_FIELDS."` SET `id` = 4,
			`field_name` = 'birthday',
			`field_type` = 'date',
			`field_length` = 0,
			`field_sort` = 1,
			`field_show_type` = 15,
			`display_name` = 'Geburtstag',
			`allowed_values` = '',
			`field_validation` = '',
			`field_suffix` = '',
			`field_custom_display` = '',
			`field_is_required` = 1,
			`field_edit_notice` = '( DD.MM.YYYY )',
			`display_always` = 1;"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_USER_GROUPS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_USER_GROUPS."` (
		  `id` tinyint(2) unsigned NOT NULL auto_increment,
		  `group_title` varchar(50) NOT NULL default '',
		  `group_color` varchar(6) NOT NULL default '',
		  `group_rank_filled_image` varchar(255) NOT NULL default '',
		  `group_rank_empty_image` varchar(255) NOT NULL default '',
		  `overrides_mod` tinyint(1) unsigned NOT NULL default '0',
		  `is_super_mod` tinyint(1) unsigned NOT NULL default '0',
		  `view_memberlist` tinyint(1) unsigned NOT NULL default '0',
		  `view_linklist` tinyint(1) unsigned NOT NULL default '0',
		  `view_stats` tinyint(1) unsigned NOT NULL default '0',
		  `view_calendar` tinyint(1) unsigned NOT NULL default '0',
		  `view_search` tinyint(1) unsigned NOT NULL default '0',
		  `view_userdetails` tinyint(1) unsigned NOT NULL default '0',
		  `edit_own_posts` tinyint(1) unsigned NOT NULL default '0',
		  `delete_own_posts` tinyint(1) unsigned NOT NULL default '0',
		  `edit_own_threads` tinyint(1) unsigned NOT NULL default '0',
		  `delete_own_threads` tinyint(1) unsigned NOT NULL default '0',
		  `openclose_own_threads` tinyint(1) unsigned NOT NULL default '0',
		  `send_mails` tinyint(1) unsigned NOT NULL default '0',
		  `add_new_link` tinyint(1) unsigned NOT NULL default '0',
		  `attachments_add` tinyint(1) unsigned NOT NULL default '0',
		  `attachments_download` tinyint(1) unsigned NOT NULL default '0',
		  `add_cal_event` tinyint(1) unsigned NOT NULL default '0',
		  `edit_cal_event` tinyint(1) unsigned NOT NULL default '0',
		  `delete_cal_event` tinyint(1) unsigned NOT NULL default '0',
		  `subscribe_forums` tinyint(1) unsigned NOT NULL default '0',
		  `view_user_ip` tinyint(1) unsigned NOT NULL default '0',
		  `is_visible` tinyint(1) unsigned NOT NULL default '0',
		  `view_online_locations` tinyint(1) unsigned NOT NULL default '0',
		  `disable_ip_blocks` tinyint(1) unsigned NOT NULL default '0',
		  `enter_board` tinyint(1) unsigned NOT NULL default '0',
		  `view_user_online_detail` tinyint(1) unsigned NOT NULL default '0',
		  `always_edit_poll_options` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$this->db->sql_qry("INSERT INTO `".BS_TB_USER_GROUPS."` SET
									`id` = ".BS_STATUS_ADMIN.",
									`group_title` = 'Administratoren',
									`group_color` = '990000',
									`group_rank_filled_image` = 'images/ranks/admin.gif',
									`group_rank_empty_image` = 'images/ranks/admin.gif',
									`overrides_mod` = 1,
									`is_super_mod` = 1,
									`is_visible` = 1,
									`view_memberlist` = 1,
									`view_linklist` = 1,
									`view_stats` = 1,
									`view_calendar` = 1,
									`view_search` = 1,
									`view_userdetails` = 1,
									`view_online_locations` = 1,
									`edit_own_posts` = 1,
									`delete_own_posts` = 1,
									`edit_own_threads` = 1,
									`delete_own_threads` = 1,
									`openclose_own_threads` = 1,
									`send_mails` = 1,
									`add_new_link` = 1,
									`attachments_add` = 1,
									`attachments_download` = 1,
									`add_cal_event` = 1,
									`edit_cal_event` = 1,
									`delete_cal_event` = 1,
									`subscribe_forums` = 1,
									`disable_ip_blocks` = 1,
									`view_user_ip` = 1,
									`enter_board` = 1,
									`view_user_online_detail` = 1,
									`always_edit_poll_options` = 1;");
		$this->db->sql_qry("INSERT INTO `".BS_TB_USER_GROUPS."` SET
									`id` = ".BS_STATUS_USER.",
									`group_title` = 'User',
									`group_color` = '3F5E88',
									`group_rank_filled_image` = 'images/ranks/user_filled.gif',
									`group_rank_empty_image` = 'images/ranks/user_empty.gif',
									`overrides_mod` = 0,
									`is_super_mod` = 0,
									`is_visible` = 1,
									`view_memberlist` = 1,
									`view_linklist` = 1,
									`view_stats` = 1,
									`view_calendar` = 1,
									`view_search` = 1,
									`view_userdetails` = 1,
									`view_online_locations` = 1,
									`edit_own_posts` = 1,
									`delete_own_posts` = 0,
									`edit_own_threads` = 1,
									`delete_own_threads` = 0,
									`openclose_own_threads` = 0,
									`send_mails` = 1,
									`add_new_link` = 1,
									`attachments_add` = 1,
									`attachments_download` = 1,
									`add_cal_event` = 0,
									`edit_cal_event` = 0,
									`delete_cal_event` = 0,
									`subscribe_forums` = 0,
									`disable_ip_blocks` = 0,
									`view_user_ip` = 0,
									`enter_board` = 1,
									`view_user_online_detail` = 0,
									`always_edit_poll_options` = 0;");
		$this->db->sql_qry("INSERT INTO `".BS_TB_USER_GROUPS."` SET
									`id` = ".BS_STATUS_GUEST.",
									`group_title` = 'Gäste',
									`group_color` = '',
									`group_rank_filled_image` = '',
									`group_rank_empty_image` = '',
									`overrides_mod` = 0,
									`is_super_mod` = 0,
									`is_visible` = 0,
									`view_memberlist` = 0,
									`view_linklist` = 1,
									`view_stats` = 0,
									`view_calendar` = 0,
									`view_search` = 1,
									`view_userdetails` = 1,
									`view_online_locations` = 1,
									`edit_own_posts` = 0,
									`delete_own_posts` = 0,
									`edit_own_threads` = 0,
									`delete_own_threads` = 0,
									`openclose_own_threads` = 0,
									`send_mails` = 1,
									`add_new_link` = 0,
									`attachments_add` = 0,
									`attachments_download` = 0,
									`add_cal_event` = 0,
									`edit_cal_event` = 0,
									`delete_cal_event` = 0,
									`subscribe_forums` = 0,
									`disable_ip_blocks` = 0,
									`view_user_ip` = 0,
									`enter_board` = 1,
									`view_user_online_detail` = 0,
									`always_edit_poll_options` = 0;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.BS_TB_RANKS.'"...');
		$this->db->sql_qry("CREATE TABLE `".BS_TB_RANKS."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `rank` varchar(80) NOT NULL default '',
		  `post_to` smallint(5) unsigned NOT NULL default '0',
		  `post_from` smallint(5) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_RANKS."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (1, 'Neuling', 10, 0);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_RANKS."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (2, 'Dauergast', 500, 301);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_RANKS."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (3, 'Erfahren', 300, 151);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_RANKS."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (4, 'Fortgeschritten', 150, 51);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_RANKS."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (5, 'Flaschengeist', 50, 11);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_RANKS."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (6, 'Forum-Beherrscher', 800, 501);"
		);
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_RANKS."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (7, 'Forum-Gott', 2000, 801);"
		);
		$this->add_to_log_success();
		
		
		$this->add_to_log('Generating DB-Cache...');
		
		// now we have to create the cache-data
		include_once(PLIB_Path::inner().'src/cache/cache.php');
		include_once(PLIB_Path::inner().'src/cache/db_cache.php');
		include_once(PLIB_Path::inner().'src/cache/cache_container.php');
		
		// we have to instantiate the cache-container here because the write_to_db() method needs it
		$this->cachecon = &new BS_CacheContainer($this);
		$this->cachecon->regenerate_caches();
		
		// regenerate event-cache
		$events = new BS_Cache_Events();
		$events->refresh_events();
		$events->refresh_birthdays();
		$events->write_to_db();
		
		$this->add_to_log_success();
	}
}
?>