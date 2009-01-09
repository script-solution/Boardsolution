<?php
/**
 * Contains the full-installation-SQL-class.
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The full-installation-SQL-class.
 * Creates all tables in the database and inserts the initially required data.
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_5_SQL_Full extends BS_Install_Module_5_SQL_Base
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
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_ACP_ACCESS'].'"...');
		$db->execute("CREATE TABLE `".$consts['BS_TB_ACP_ACCESS']."` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `module` varchar(200) NOT NULL,
		  `access_type` enum('user','group') NOT NULL,
		  `access_value` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_ACTIVATION'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_ACTIVATION']}` (
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `user_key` varchar(32) NOT NULL,
		  PRIMARY KEY  (`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_ATTACHMENTS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_ATTACHMENTS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `pm_id` int(10) unsigned NOT NULL default '0',
		  `thread_id` int(10) unsigned NOT NULL default '0',
		  `post_id` int(10) unsigned NOT NULL default '0',
		  `poster_id` int(10) unsigned NOT NULL default '0',
		  `attachment_size` int(10) unsigned NOT NULL default '0',
		  `attachment_path` varchar(255) NOT NULL,
		  `downloads` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `poster_id` (`poster_id`),
		  KEY `thread_id` (`thread_id`),
		  KEY `post_id` (`post_id`),
		  KEY `pm_id` (`pm_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_AVATARS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_AVATARS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `av_pfad` varchar(255) NOT NULL,
		  `user` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `user` (`user`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_BANS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_BANS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `bann_name` varchar(255) NOT NULL,
		  `bann_type` varchar(5) NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
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
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_BOTS'].'"...');
		$db->execute(
			"CREATE TABLE `{$consts['BS_TB_BOTS']}` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `bot_name` varchar(255) NOT NULL,
			  `bot_match` varchar(255) NOT NULL,
			  `bot_ip_start` varchar(15) NOT NULL,
			  `bot_ip_end` varchar(15) NOT NULL,
			  `bot_access` tinyint(1) unsigned NOT NULL,
			  PRIMARY KEY  (`id`)
			) TYPE=MyISAM"
		);
		
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Googlebot', 'Googlebot/', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('YahooBot', 'Yahoo! Slurp;', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('MSNBot', 'msnbot/', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('ZyBorg', 'ZyBorg/', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('SeekBot', 'Seekbot/', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Exabot', 'Exabot/', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Gigabot', 'Gigabot/', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('iCCrawler', 'iCCrawler', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Mediapartners-Google', 'Mediapartners-Google/', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('MJ12bot', 'MJ12bot/', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('W3C-Validator', 'W3C_Validator/', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Accoona', 'Accoona-AI-Agent/', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('MSN-Media-Bot', 'msnbot-media/1.0', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Twiceler-Bot', 'Twiceler-0.9', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('RedBot', 'RedBot/redbot-1.0', '', '', 1);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_BOTS']."`
			 (`bot_name`, `bot_match`, `bot_ip_start`, `bot_ip_end`, `bot_access`)
			 VALUES ('Ask.com', 'Ask Jeeves/Teoma;', '', '', 1);"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_CACHE'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_CACHE']}` (
		  `table_name` enum('banlist','intern','languages','moderators','themes','user_groups',
		  									'user_ranks','config','user_fields','stats','tasks','acp_access',
		  									'bots') NOT NULL,
		  `table_content` longtext NOT NULL,
		  PRIMARY KEY  (`table_name`)
		) TYPE=MyISAM;");
		
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('banlist');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('intern');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('languages');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('moderators');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('themes');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('user_groups');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('user_ranks');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('config');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('user_fields');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('stats');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('tasks');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('acp_access');");
		$db->execute("INSERT INTO `{$consts['BS_TB_CACHE']}` (`table_name`) VALUES('bots');");
		$this->add_to_log_success();
		
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
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_CHANGE_EMAIL'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_CHANGE_EMAIL']}` (
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `user_key` varchar(32) NOT NULL,
		  `email_address` varchar(255) NOT NULL,
		  `email_date` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_CHANGE_PW'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_CHANGE_PW']}` (
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `user_key` varchar(32) NOT NULL,
		  `email_date` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_EVENTS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_EVENTS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `tid` int(10) unsigned NOT NULL default '0',
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `event_title` varchar(200) NOT NULL,
		  `event_begin` int(10) unsigned NOT NULL default '0',
		  `event_end` int(10) unsigned NOT NULL default '0',
		  `max_announcements` mediumint(5) NOT NULL default '0',
		  `description` text NOT NULL,
		  `description_posted` text NOT NULL,
		  `event_location` varchar(100) NOT NULL,
		  `timeout` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `tid` (`tid`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_EVENT_ANN'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_EVENT_ANN']}` (
		  `event_id` int(10) unsigned NOT NULL,
		  `user_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY  (`event_id`,`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_FORUMS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_FORUMS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `parent_id` int(10) unsigned NOT NULL default '0',
		  `sortierung` int(10) unsigned NOT NULL default '0',
		  `forum_name` varchar(50) NOT NULL,
		  `description` varchar(255) NOT NULL,
		  `forum_type` enum('contains_cats','contains_threads') NOT NULL,
		  `forum_is_intern` tinyint(1) unsigned NOT NULL default '0',
		  `threads` int(10) unsigned NOT NULL default '0',
		  `posts` int(10) unsigned NOT NULL default '0',
		  `lastpost_id` int(10) NOT NULL default '0',
		  `increase_experience` tinyint(1) unsigned NOT NULL default '0',
		  `display_subforums` tinyint(1) unsigned NOT NULL default '0',
		  `forum_is_closed` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `lastpost_id` (`lastpost_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_FORUMS_PERM'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_FORUMS_PERM']}` (
		  `forum_id` int(10) unsigned NOT NULL,
		  `group_id` int(10) unsigned NOT NULL,
		  `type` enum('reply','topic','poll','event') NOT NULL,
		  PRIMARY KEY  (`forum_id`,`group_id`,`type`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_INTERN'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_INTERN']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `fid` int(10) unsigned NOT NULL default '0',
		  `access_type` enum('group','user') NOT NULL,
		  `access_value` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `fid` (`fid`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_LANGS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_LANGS']}` (
		  `id` smallint(3) unsigned NOT NULL auto_increment,
		  `lang_folder` varchar(20) NOT NULL,
		  `lang_name` varchar(50) NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_LANGS']."` (`id`, `lang_folder`, `lang_name`) VALUES
			 (1, 'ger_sie', 'Deutsch (Sie-Version)');"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_LANGS']."` (`id`, `lang_folder`, `lang_name`) VALUES
			 (2, 'en', 'English');"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_LANGS']."` (`id`, `lang_folder`, `lang_name`) VALUES
			 (3, 'ger_du', 'Deutsch (Du-Version)');"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_LANGS']."` (`id`, `lang_folder`, `lang_name`) VALUES
			 (4, 'dk', 'Dansk');"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_LANGS']."` (`id`, `lang_folder`, `lang_name`) VALUES
			 (5, 'fra', 'Français');"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_LINKS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_LINKS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `category` varchar(100) NOT NULL,
		  `link_url` varchar(255) NOT NULL,
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
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_LINK_VOTES'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_LINK_VOTES']}` (
		  `user_id` int(10) unsigned NOT NULL,
		  `link_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY  (`user_id`,`link_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_LOG_ERRORS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_LOG_ERRORS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `query` text NOT NULL,
		  `user_id` int(10) unsigned NOT NULL,
		  `date` int(10) unsigned NOT NULL,
		  `message` text NOT NULL,
		  `backtrace` text NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_LOG_IPS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_LOG_IPS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `user_ip` varchar(15) NOT NULL,
		  `user_id` int(10) unsigned NOT NULL,
		  `user_agent` varchar(255) NOT NULL,
		  `date` int(10) unsigned NOT NULL,
		  `action` varchar(20) NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_MODS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_MODS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `rid` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `rid` (`rid`),
		  KEY `user_id` (`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_PMS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_PMS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `receiver_id` int(10) unsigned NOT NULL default '0',
		  `sender_id` int(10) unsigned NOT NULL default '0',
		  `pm_type` enum('inbox','outbox') NOT NULL,
		  `pm_title` varchar(80) NOT NULL,
		  `pm_text` text NOT NULL,
		  `pm_date` int(10) unsigned NOT NULL default '0',
		  `pm_read` tinyint(1) unsigned NOT NULL default '0',
		  `pm_text_posted` text NOT NULL,
		  `attachment_count` tinyint(3) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `receiver_id` (`receiver_id`),
		  KEY `sender_id` (`sender_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_POLL'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_POLL']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `pid` int(10) unsigned NOT NULL default '0',
		  `option_name` varchar(100) NOT NULL,
		  `option_value` int(10) unsigned NOT NULL default '0',
		  `multichoice` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `pid` (`pid`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_POLL_VOTES'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_POLL_VOTES']}` (
		  `poll_id` int(10) unsigned NOT NULL default '0',
		  `user_id` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`poll_id`,`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_POSTS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_POSTS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `rubrikid` int(10) unsigned NOT NULL default '0',
		  `threadid` int(10) unsigned NOT NULL default '0',
		  `post_user` int(10) unsigned NOT NULL default '0',
		  `post_time` int(10) unsigned NOT NULL default '0',
		  `text` text NOT NULL,
		  `post_an_user` varchar(150) default NULL,
		  `post_an_mail` varchar(150) default NULL,
		  `use_smileys` tinyint(1) unsigned NOT NULL default '0',
		  `use_bbcode` tinyint(1) unsigned NOT NULL default '0',
		  `ip_adresse` varchar(15) NOT NULL,
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
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_PROFILES'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_PROFILES']}` (
		  `id` int(10) unsigned NOT NULL default '0',
		  `add_hp` varchar(255) NOT NULL,
		  `add_icq` int(15) default NULL,
		  `add_irc` varchar(120) NOT NULL,
		  `add_wohnort` varchar(255) NOT NULL default '',
		  `add_Hobbys` varchar(255) NOT NULL,
		  `avatar` int(10) unsigned NOT NULL default '0',
		  `signatur` text NOT NULL,
		  `registerdate` int(10) unsigned NOT NULL default '0',
		  `posts` int(10) unsigned NOT NULL default '0',
		  `exppoints` int(10) unsigned NOT NULL default '0',
		  `logins` int(10) unsigned NOT NULL default '0',
		  `lastlogin` int(10) unsigned NOT NULL default '0',
		  `active` tinyint(1) unsigned NOT NULL default '0',
		  `banned` tinyint(1) unsigned NOT NULL default '0',
		  `default_font` varchar(50) NOT NULL,
		  `allow_pms` tinyint(1) unsigned NOT NULL default '0',
		  `ghost_mode` tinyint(1) unsigned NOT NULL default '0',
		  `last_unread_update` int(10) unsigned NOT NULL default '0',
		  `online` tinyint(1) unsigned NOT NULL default '0',
		  `bbcode_mode` enum('simple','advanced','applet') NOT NULL,
		  `attach_signature` tinyint(1) unsigned NOT NULL default '1',
		  `allow_board_emails` tinyint(1) unsigned NOT NULL default '0',
		  `forum_style` smallint(3) unsigned NOT NULL default '0',
		  `forum_lang` smallint(3) unsigned NOT NULL default '0',
		  `default_email_notification` tinyint(1) unsigned NOT NULL default '0',
		  `timezone` varchar(100) NOT NULL default 'Europe/Berlin',
		  `daylight_saving` tinyint(1) unsigned NOT NULL default '0',
		  `user_group` text NOT NULL,
		  `enable_pm_email` tinyint(1) unsigned NOT NULL default '0',
		  `email_display_mode` enum('hide','jumble','default') NOT NULL,
		  `emails_include_post` tinyint(1) unsigned NOT NULL default '0',
		  `signature_posted` text NOT NULL,
		  `add_birthday` date NOT NULL default '0000-00-00',
		  `username_changes` tinyint(3) unsigned NOT NULL default '0',
		  `login_tries` tinyint(3) unsigned NOT NULL default '0',
		  `store_unread_in_cookie` tinyint(1) unsigned NOT NULL default '0',
		  `posts_order` enum('ASC','DESC') NOT NULL,
		  `email_notification_type` enum('immediatly','1day','2days','1week') NOT NULL,
		  `last_email_notification` int(10) unsigned NOT NULL default '0',
		  `last_search_time` int(10) unsigned NOT NULL default '0',
		  `startmodule` enum('portal','forums') NOT NULL default 'portal',
		  PRIMARY KEY  (`id`),
		  KEY `avatar` (`avatar`)
		) TYPE=MyISAM;");
		
		$db->execute("INSERT INTO `".$consts['BS_TB_PROFILES']."` SET
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
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_SEARCH'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_SEARCH']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `session_id` varchar(32) NOT NULL,
		  `search_date` int(10) unsigned NOT NULL default '0',
		  `result_ids` text NOT NULL,
		  `result_type` enum('topics','posts','pms','pm_history') NOT NULL,
		  `keywords` text NOT NULL,
		  `search_mode` enum('default','user_posts','user_topics','pms','topic','history','similar_topics') NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_SESSIONS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_SESSIONS']}` (
		  `session_id` varchar(32) NOT NULL,
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `user_ip` varchar(15) NOT NULL,
		  `date` int(10) unsigned NOT NULL default '0',
		  `location` varchar(255) NOT NULL,
		  `user_agent` varchar(255) NOT NULL,
		  `session_data` text NOT NULL,
		  PRIMARY KEY  (`session_id`),
		  KEY `user_id` (`user_id`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_SMILEYS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_SMILEYS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `smiley_path` varchar(255) NOT NULL,
		  `primary_code` varchar(15) NOT NULL,
		  `secondary_code` varchar(15) NOT NULL,
		  `is_base` tinyint(1) unsigned NOT NULL default '0',
		  `sort_key` int(10) unsigned NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$i = 0;
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (10, 'smile.png', ':-)', ':)', 1, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (12, 'wink.png', ';-)', ';)', 1, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (1, 'happy.png', '=)', '', 1, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (2, 'frown.png', ':-(', ':(', 1, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (14, 'bigsmile.png', ':D', '', 1, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (11, 'tongue.png', ':-P', ':P', 1, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (17, 'unsure.png', ':-/', ':/', 1, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (5, 'crying.png', ':cry:', '', 0, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (6, 'cool.png', '8-)', '8)', 1, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (9, 'rolleyes.png', ':roll:', '', 1, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (7, 'confused.png', ':confused:', ':??:', 1, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (13, 'shock.png', ':shock:', '', 1, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (15, 'holy.png', 'O:-)', 'O:)', 0, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (16, 'oops.png', ':ops:', '', 0, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (4, 'eek.png', ':o', '', 0, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (3, 'evil.png', ':evil:', '', 0, ".(++$i).");");
		$db->execute("INSERT INTO `".$consts['BS_TB_SMILEYS']."`
									(`id`, `smiley_path`, `primary_code`, `secondary_code`, `is_base`, `sort_key`)
									VALUES (8, 'biggrin.png', ':-O', '', 1, ".(++$i).");");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_SUBSCR'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_SUBSCR']}` (
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
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_TASKS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_TASKS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `task_title` varchar(100) NOT NULL,
		  `task_file` varchar(100) NOT NULL,
		  `task_interval` int(10) unsigned NOT NULL default '0',
		  `last_execution` int(10) unsigned NOT NULL default '0',
		  `enabled` tinyint(1) unsigned NOT NULL default '0',
		  `task_time` time default NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$db->execute(
			"INSERT INTO `{$consts['BS_TB_TASKS']}`
			(`id`, `task_title`, `task_file`, `task_interval`, `last_execution`, `enabled`, `task_time`)
			VALUES (1, 'attachments', 'attachments.php', 259200, 0, 1, '00:00:00');"
		);
		$db->execute(
			"INSERT INTO `{$consts['BS_TB_TASKS']}`
			(`id`, `task_title`, `task_file`, `task_interval`, `last_execution`, `enabled`, `task_time`)
			VALUES (2, 'registrations', 'registrations.php', 604800, 0, 1, '00:00:00');"
		);
		$db->execute(
			"INSERT INTO `{$consts['BS_TB_TASKS']}`
			(`id`, `task_title`, `task_file`, `task_interval`, `last_execution`, `enabled`, `task_time`)
			VALUES (3, 'change_email_pw', 'change_email_pw.php', 604800, 0, 1, '00:00:00');"
		);
		$db->execute(
			"INSERT INTO `{$consts['BS_TB_TASKS']}`
			(`id`, `task_title`, `task_file`, `task_interval`, `last_execution`, `enabled`, `task_time`)
			VALUES (4, 'logged_ips', 'logged_ips.php', 604800, 0, 1, '00:00:00');"
		);
		$db->execute(
			"INSERT INTO `{$consts['BS_TB_TASKS']}`
			(`id`, `task_title`, `task_file`, `task_interval`, `last_execution`, `enabled`, `task_time`)
			VALUES (5, 'subscriptions', 'subscriptions.php', 604800, 0, 1, '00:00:00');"
		);
		$db->execute(
			"INSERT INTO `{$consts['BS_TB_TASKS']}`
			(`id`, `task_title`, `task_file`, `task_interval`, `last_execution`, `enabled`, `task_time`)
			VALUES (6, 'email_notification', 'email_notification.php', 86400, 0, 1, '00:00:00');"
		);
		$db->execute(
			"INSERT INTO `{$consts['BS_TB_TASKS']}`
			(`id`, `task_title`, `task_file`, `task_interval`, `last_execution`, `enabled`, `task_time`)
			VALUES (7, 'error_log', 'error_log.php', 259200, 0, 1, '00:00:00');"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_THEMES'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_THEMES']}` (
		  `id` smallint(3) unsigned NOT NULL auto_increment,
		  `theme_folder` varchar(20) NOT NULL,
		  `theme_name` varchar(50) NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$db->execute("INSERT INTO `".$consts['BS_TB_THEMES']."` (`id`, `theme_folder`, `theme_name`)
									VALUES (1, 'default', 'Script-solution');");
		$db->execute("INSERT INTO `".$consts['BS_TB_THEMES']."` (`id`, `theme_folder`, `theme_name`)
									VALUES (2, 'desert', 'Desert');");
		$db->execute("INSERT INTO `".$consts['BS_TB_THEMES']."` (`id`, `theme_folder`, `theme_name`)
									VALUES (3, 'black_red', 'Black-Red');");
		$db->execute("INSERT INTO `".$consts['BS_TB_THEMES']."` (`id`, `theme_folder`, `theme_name`)
									VALUES (4, 'minimal', 'Minimal');");
		$db->execute("INSERT INTO `".$consts['BS_TB_THEMES']."` (`id`, `theme_folder`, `theme_name`)
									VALUES (5, 'bots', 'Bots');");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_THREADS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_THREADS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `rubrikid` int(10) unsigned NOT NULL default '0',
		  `name` varchar(255) NOT NULL,
		  `post_time` int(10) unsigned NOT NULL default '0',
		  `post_user` int(10) unsigned NOT NULL default '0',
		  `symbol` tinyint(2) unsigned NOT NULL default '0',
		  `type` int(10) NOT NULL default '0',
		  `comallow` tinyint(1) unsigned NOT NULL default '0',
		  `views` int(10) unsigned NOT NULL default '0',
		  `moved` tinyint(1) unsigned NOT NULL default '0',
		  `posts` int(10) unsigned NOT NULL default '0',
		  `post_an_user` varchar(150) default NULL,
		  `post_an_mail` varchar(150) default NULL,
		  `lastpost_id` int(10) unsigned NOT NULL default '0',
		  `lastpost_time` int(10) unsigned NOT NULL default '0',
		  `lastpost_user` int(10) unsigned NOT NULL default '0',
		  `lastpost_an_user` varchar(150) default NULL,
		  `important` tinyint(1) unsigned NOT NULL default '0',
		  `thread_closed` tinyint(1) unsigned NOT NULL default '0',
		  `moved_rid` int(10) unsigned NOT NULL default '0',
		  `moved_tid` int(10) unsigned NOT NULL default '0',
		  `locked` tinyint(3) unsigned NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `rubrikid` (`rubrikid`),
		  KEY `post_user` (`post_user`),
		  KEY `lastpost_id` (`lastpost_id`),
		  KEY `lastpost_user` (`lastpost_user`)
		) TYPE=MyISAM;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_UNREAD'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_UNREAD']}` (
		  `user_id` int(10) unsigned NOT NULL,
		  `post_id` int(10) unsigned NOT NULL,
		  `is_news` tinyint(1) unsigned NOT NULL,
		  PRIMARY KEY  (`user_id`,`post_id`)
		) TYPE=MyISAM");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_UNREAD_HIDE'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_UNREAD_HIDE']}` (
		  `user_id` int(10) unsigned NOT NULL,
		  `forum_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY  (`user_id`,`forum_id`)
		) TYPE=MyISAM");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_UNSENT_POSTS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_UNSENT_POSTS']}` (
		  `user_id` int(10) unsigned NOT NULL,
		  `post_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY  (`user_id`,`post_id`)
		) TYPE=MyISAM");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_USER'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_USER']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `user_name` varchar(50) NOT NULL,
		  `user_pw` varchar(32) NOT NULL,
		  `user_email` varchar(255) NOT NULL,
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$admin_login = addslashes($user->get_session_data('admin_login'));
		$admin_pw = $user->get_session_data('admin_pw');
		$admin_email = addslashes($user->get_session_data('admin_email'));
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_USER']."`
			 (`id`, `user_name`, `user_pw`, `user_email`)
			 VALUES
			 (1, '".$admin_login."', '".md5($admin_pw)."', '".$admin_email."');"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_USER_BANS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_USER_BANS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `user_id` int(10) unsigned NOT NULL default '0',
		  `baned_user` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `user_id` (`user_id`),
		  KEY `baned_user` (`baned_user`)
		) TYPE=MyISAM");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_USER_FIELDS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_USER_FIELDS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `field_name` varchar(30) NOT NULL,
		  `field_type` enum('int','line','text','enum','date') NOT NULL,
		  `field_length` smallint(5) unsigned NOT NULL default '0',
		  `field_sort` int(10) unsigned NOT NULL default '0',
		  `field_show_type` tinyint(3) unsigned NOT NULL default '0',
		  `display_name` varchar(50) NOT NULL,
		  `allowed_values` varchar(255) NOT NULL,
		  `field_validation` varchar(100) NOT NULL,
		  `field_suffix` varchar(100) NOT NULL,
		  `field_custom_display` text NOT NULL,
		  `field_is_required` tinyint(1) unsigned NOT NULL default '0',
		  `field_edit_notice` text NOT NULL,
		  `display_always` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_USER_FIELDS']."` SET `id` = 1,
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
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_USER_FIELDS']."` SET `id` = 2,
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
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_USER_FIELDS']."` SET `id` = 3,
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
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_USER_FIELDS']."` SET `id` = 4,
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
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_USER_GROUPS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_USER_GROUPS']}` (
		  `id` tinyint(2) unsigned NOT NULL auto_increment,
		  `group_title` varchar(50) NOT NULL,
		  `group_color` varchar(6) NOT NULL,
		  `group_rank_filled_image` varchar(255) NOT NULL,
		  `group_rank_empty_image` varchar(255) NOT NULL,
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
		  `view_useronline_list` tinyint(1) unsigned NOT NULL default '0',
		  `is_team` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$db->execute("INSERT INTO `".$consts['BS_TB_USER_GROUPS']."` SET
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
									`always_edit_poll_options` = 1,
									`view_useronline_list` = 1,
									`is_team` = 0;");
		$db->execute("INSERT INTO `".$consts['BS_TB_USER_GROUPS']."` SET
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
									`always_edit_poll_options` = 0,
									`view_useronline_list` = 1,
									`is_team` = 0;");
		$db->execute("INSERT INTO `".$consts['BS_TB_USER_GROUPS']."` SET
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
									`always_edit_poll_options` = 0,
									`view_useronline_list` = 1,
									`is_team` = 0;");
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$consts['BS_TB_RANKS'].'"...');
		$db->execute("CREATE TABLE `{$consts['BS_TB_RANKS']}` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `rank` varchar(80) NOT NULL,
		  `post_to` smallint(5) unsigned NOT NULL default '0',
		  `post_from` smallint(5) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		) TYPE=MyISAM;");
		
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_RANKS']."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (1, 'Neuling', 10, 0);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_RANKS']."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (2, 'Dauergast', 500, 301);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_RANKS']."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (3, 'Erfahren', 300, 151);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_RANKS']."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (4, 'Fortgeschritten', 150, 51);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_RANKS']."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (5, 'Flaschengeist', 50, 11);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_RANKS']."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (6, 'Forum-Beherrscher', 800, 501);"
		);
		$db->execute(
			"INSERT INTO `".$consts['BS_TB_RANKS']."` (`id`, `rank`, `post_to`, `post_from`)
			 VALUES (7, 'Forum-Gott', 2000, 801);"
		);
		$this->add_to_log_success();
		
		
		$this->add_to_log('Generating settings...');
		BS_Install_Module_5_Helper::generate_settings();
		$this->add_to_log_success();
		
		
		$this->add_to_log('Generating DB-Cache...');
		include_once(FWS_Path::server_app().'config/mysql.php');
		$cache = FWS_Props::get()->cache();
		$cache->refresh_all();
		$this->add_to_log_success();
	}
}
?>