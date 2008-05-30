<?php
/**
 * Contains the update-SQL-class.
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The update-SQL-class. Performs the update of the database from BS 1.2x to BS 1.3
 * 
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_InstallSQL_update extends BS_InstallSQL
{
	public function run()
	{
		include(PLIB_Path::inner().'config/community.php');
		include(PLIB_Path::inner().'config/mysql.php');
		include(PLIB_Path::inner().'src/mysql.php');
		$prefix = $this->functions->get_session_var('table_prefix');
		
		// we have to init $this->db here because we need it later on
		$this->db = new BS_MySQL(BS_MYSQL_HOST,BS_MYSQL_LOGIN,BS_MYSQL_PASSWORD,BS_MYSQL_DATABASE);
		
		// change default charset and collation of the db
		if($this->db->get_server_version() >= '4.1')
		{
			$this->db->sql_qry(
				'ALTER DATABASE `'.BS_MYSQL_DATABASE.'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;'
			);
		}
		
		$this->add_to_log('Creating Table "'.$prefix.'change_email"...');
		$this->db->sql_qry(
			"CREATE TABLE `".$prefix."change_email` (
				`user_id` int(10) unsigned NOT NULL default '0',
				`user_key` varchar(32) NOT NULL default '',
				`email_address` varchar(255) NOT NULL default '',
				`email_date` int(10) unsigned NOT NULL default '0',
				PRIMARY KEY (`user_id`)
			) TYPE=MyISAM;"
		);
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
		
		$this->add_to_log('Modifying Table "'.$prefix.'config"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."config`
			 DROP `links_per_page`,
			 DROP `post_max_smileys`,
			 DROP `post_max_pics`,
			 DROP `post_smileys_on`,
			 DROP `post_bbcode_on`,
			 DROP `post_max_length`,
			 DROP `post_max_string_len`,
			 DROP `post_parse_urls`,
			 DROP `post_code_highlight`,
			 DROP `signature_smileys_on`,
			 DROP `signature_bbcode_on`,
			 DROP `signature_max_length`,
			 DROP `linklist_smileys_on`,
			 DROP `linklist_bbcode_on`,
			 DROP `enable_signature_images`,
			 DROP `default_bbcode_mode`,
			 ADD `enable_portal` tinyint(1) unsigned NOT NULL default 0,
			 ADD `enable_portal_news` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `news_forums` text NOT NULL,
			 ADD `news_count` int(10) unsigned NOT NULL default 0 ,
			 ADD `mail_method` enum('mail','smtp') NOT NULL DEFAULT 'mail' ,
			 ADD `smtp_host` varchar(255) NOT NULL default '' ,
			 ADD `smtp_port` int(10) unsigned NOT NULL default 0 ,
			 ADD `smtp_login` varchar(255) NOT NULL default '' ,
			 ADD `smtp_password` varchar(255) NOT NULL default '' ,
			 ADD `smtp_use_ltgt` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `enable_moderators` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `mod_lock_topics` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `enable_news_feeds` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `allow_email_changes` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `msgs_default_bbcode_mode` enum('simple','advanced','applet') NOT NULL DEFAULT 'simple' ,
			 ADD `msgs_parse_urls` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `msgs_code_highlight` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `msgs_code_line_numbers` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `msgs_max_line_length` mediumint(4) unsigned NOT NULL default 0 ,
			 ADD `msgs_allow_java_applet` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `posts_enable_smileys` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `posts_enable_bbcode` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `posts_max_length` int(10) unsigned NOT NULL default 0 ,
			 ADD `posts_max_images` mediumint(4) unsigned NOT NULL default 0 ,
			 ADD `posts_max_smileys` mediumint(4) unsigned NOT NULL default 0 ,
			 ADD `posts_allowed_tags` text NOT NULL,
			 ADD `sig_enable_smileys` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `sig_enable_bbcode` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `sig_max_length` int(10) unsigned NOT NULL default 0 ,
			 ADD `sig_max_images` mediumint(4) unsigned NOT NULL default 0 ,
			 ADD `sig_max_smileys` mediumint(4) unsigned NOT NULL default 0 ,
			 ADD `sig_allowed_tags` text NOT NULL ,
			 ADD `lnkdesc_enable_smileys` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `lnkdesc_enable_bbcode` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `lnkdesc_max_length` int(10) unsigned NOT NULL default 0 ,
			 ADD `lnkdesc_max_images` mediumint(4) unsigned NOT NULL default 0 ,
			 ADD `lnkdesc_max_smileys` mediumint(4) unsigned NOT NULL default 0 ,
			 ADD `lnkdesc_allowed_tags` text NOT NULL ,
			 ADD `enable_error_log` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `error_log_days` mediumint(4) unsigned NOT NULL default 0 ,
			 ADD `confirm_email_addresses` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `display_ministats` tinyint(1) unsigned NOT NULL default 0 ,
			 ADD `ip_log_days` mediumint(4) unsigned NOT NULL default 0 ,
			 CHANGE `current_topic_loc` `current_topic_loc` enum('top','bottom','portal') NOT NULL DEFAULT 'portal' ,
			 CHANGE `enable_calendar_events` `enable_calendar_events` tinyint(1) unsigned NOT NULL default 0 ;"
		);
		
		$this->db->sql_qry(
			"UPDATE `".BS_TB_DESIGN."` SET
			 `current_topic_loc` = 'portal',
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
			 `ip_log_days` = 21,
			 `enable_board` = 0"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Modifying Table "'.$prefix.'cache"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."cache`
			 CHANGE `table_name` `table_name`
							enum('banlist','intern','languages','moderators','smileys','themes','user_groups',
									 'user_ranks','config','user_fields','stats','tasks','acp_access','bots')
							NOT NULL DEFAULT 'banlist';"
		);
		
		$this->db->sql_qry(
			"INSERT INTO ".$prefix."cache (table_name,table_content) VALUES ('bots','a:0:{}')"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Modifying Table "'.$prefix.'change_pw"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."change_pw`
			 CHANGE `user_id` `user_id` int(10) unsigned NOT NULL DEFAULT '0' ,
			 CHANGE `email_date` `email_date` int(10) unsigned NOT NULL DEFAULT '0';"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Modifying Table "'.$prefix.'events"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."events`
			 CHANGE `max_announcements` `max_announcements` mediumint(5) NOT NULL DEFAULT '0';"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Modifying Table "'.$prefix.'forums"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."forums`
			 CHANGE `increase_experience` `increase_experience` tinyint(1) unsigned NOT NULL DEFAULT '0' ,
			 CHANGE `display_subforums` `display_subforums` tinyint(1) unsigned NOT NULL DEFAULT '0',
			 CHANGE `permission_thread` `permission_thread` text NOT NULL ,
			 CHANGE `permission_event` `permission_event` text NOT NULL ,
			 CHANGE `permission_poll` `permission_poll` text NOT NULL ,
			 CHANGE `permission_post` `permission_post` text NOT NULL ;"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Removing Table "'.$prefix.'ip"...');
		$this->db->sql_qry(
			"DROP TABLE `".$prefix."ip`;"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$prefix.'log_errors"...');
		$this->db->sql_qry(
			"CREATE TABLE `".$prefix."log_errors` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`query` text NOT NULL,
				`user_id` int(10) unsigned NOT NULL default 0,
				`date` int(10) unsigned NOT NULL default 0,
				`message` text NOT NULL,
				`backtrace` text NOT NULL,
				PRIMARY KEY (`id`)
			) TYPE=MyISAM;"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Creating Table "'.$prefix.'log_ips"...');
		$this->db->sql_qry(
			"CREATE TABLE `".$prefix."log_ips` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`user_ip` varchar(15) NOT NULL default '',
				`user_id` int(10) unsigned NOT NULL default 0,
				`user_agent` varchar(255) NOT NULL default '',
				`date` int(10) unsigned NOT NULL default 0,
				`action` varchar(20) NOT NULL default '',
				PRIMARY KEY (`id`)
			) TYPE=MyISAM;"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Modifying Table "'.$prefix.'profiles"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."profiles`
			 ADD `unread_topics` text NOT NULL ,
			 CHANGE `bbcode_mode` `bbcode_mode` enum('simple','advanced','applet') NOT NULL DEFAULT 'simple' ,
			 CHANGE `last_search_time` `last_search_time` int(10) unsigned NOT NULL DEFAULT '0';"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Modifying Table "'.$prefix.'sessions"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."sessions`
			 DROP `unread_topics`;"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Modifying Table "'.$prefix.'smileys"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."smileys`
			 ADD `sort_key` int(10) unsigned NOT NULL default 0;"
		);
		
		$qry = $this->db->sql_qry('SELECT id FROM '.$prefix.'smileys ORDER BY id ASC');
		for($i = 1;$data = $this->db->sql_fetch_assoc($qry);$i++)
		{
			$this->db->sql_qry(
				'UPDATE '.$prefix.'smileys SET sort_key = '.$i.' WHERE id = '.$data['id']
			);
		}
		$this->db->sql_free($qry);
		$this->add_to_log_success();
		
		$this->add_to_log('Modifying Table "'.$prefix.'tasks"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."tasks`
			 CHANGE `task_interval` `task_interval` int(10) unsigned NOT NULL DEFAULT '0' ,
			 CHANGE `last_execution` `last_execution` int(10) unsigned NOT NULL DEFAULT '0' ,
			 CHANGE `enabled` `enabled` tinyint(1) unsigned NOT NULL DEFAULT '0';"
		);
		
		$this->db->sql_qry(
			"INSERT INTO `".BS_TB_TASKS."`
				(`task_title`, `task_file`, `task_interval`, `last_execution`, `enabled`, `task_time`)
				VALUES
				('error_log', 'error_log.php', 259200, 0, 1, NULL);"
		);
		
		$this->db->sql_qry(
			"UPDATE ".$prefix."tasks SET
			 task_title = 'change_email_pw',
			 task_file = 'change_email_pw.php'
			 WHERE task_title = 'change_pw'"
		);
		$this->db->sql_qry(
			"UPDATE ".$prefix."tasks SET
			 task_title = 'logged_ips',
			 task_file = 'logged_ips.php'
			 WHERE task_title = 'blocked_ips'"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Modifying Table "'.$prefix.'topics"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."topics`
			 ADD `locked` tinyint(3) unsigned NOT NULL default 0;"
		);
		
		$qry = $this->db->sql_qry(
			'SELECT id,status_lock,edit_lock FROM '.$prefix.'topics
			 WHERE status_lock = 1 OR edit_lock = 1'
		);
		while($data = $this->db->sql_fetch_assoc($qry))
		{
			$locked = 0;
			if($data['edit_lock'])
				$locked |= BS_LOCK_TOPIC_EDIT;
			if($data['status_lock'])
				$locked |= BS_LOCK_TOPIC_OPENCLOSE;
			
			$this->db->sql_qry('UPDATE '.$prefix.'topics SET locked = '.$locked.' WHERE id = '.$data['id']);
		}
		$this->db->sql_free($qry);
		
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."topics`
			 DROP `last_status_ch_from`,
			 DROP `last_edited_from`,
			 DROP `status_lock`,
			 DROP `edit_lock`;"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Modifying Table "'.$prefix.'user"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."user`
			 CHANGE `user_name` `user_name` varchar(50) NOT NULL default '';"
		);
		$this->add_to_log_success();
		
		$this->add_to_log('Modifying Table "'.$prefix.'user_groups"...');
		$this->db->sql_qry(
			"ALTER TABLE `".$prefix."user_groups`
			 CHANGE `is_visible` `is_visible` tinyint(1) unsigned NOT NULL DEFAULT '0' ,
			 CHANGE `view_online_locations` `view_online_locations` tinyint(1) unsigned NOT NULL DEFAULT '0' ,
			 CHANGE `view_user_online_detail` `view_user_online_detail` tinyint(1) unsigned NOT NULL DEFAULT '0' ,
			 CHANGE `always_edit_poll_options` `always_edit_poll_options` tinyint(1) unsigned NOT NULL DEFAULT '0';"
		);
		$this->add_to_log_success();
		
		// change charset and collation
		if($this->db->get_server_version() >= '4.1')
		{
			$this->add_to_log('Changing charset to "utf8" and collation to "utf8_general_ci"...');
			$this->_change_charset();
			$this->add_to_log_success();
		}
		
		$this->add_to_log('Generating DB-Cache...');
		
		// now we have to create the cache-data
		include_once(PLIB_Path::inner().'src/cache/cache.php');
		include_once(PLIB_Path::inner().'src/cache/db_cache.php');
		include_once(PLIB_Path::inner().'src/cache/cache_container.php');
		
		// we have to instantiate the cache-container here because the write_to_db() method needs it
		$this->cachecon = &new BS_CacheContainer($this);
		// Note: we suppress warnings here because we're reading the cache from db.
		// We have changed the charset and therefore this might lead to errors.
		@$this->cachecon->regenerate_caches();
		
		// regenerate event-cache
		$events = new BS_Cache_Events();
		$events->refresh_events();
		$events->refresh_birthdays();
		$events->write_to_db();
		
		$this->add_to_log_success();
	}
	
	/**
	 * Changes the charset and collation in every table and field of Boardsolution.
	 * Will only work with MySQL >= 4.1
	 *
	 */
	public function _change_charset()
	{
		$charset = 'utf8';
		$collate = 'utf8_general_ci';
		
		$text_fields = array('varchar','enum','text','longtext');
		$constants = get_defined_constants();
		$tables = array();
		foreach($constants as $k => $v)
		{
			if(PLIB_String::substr($k,0,6) == 'BS_TB_')
				$tables[] = $v;
		}
		
		// now change it in every table
		foreach($tables as $table)
		{
			// change default charset and collation of the table
			$this->db->sql_qry(
				'ALTER TABLE '.$table.' DEFAULT CHARACTER SET '.$charset.' COLLATE '.$collate.';'
			);
			
			$fields = '';
			
			// collect the fields we want to change in this table
			$qry = $this->db->sql_qry('SHOW COLUMNS FROM '.$table);
			while($data = $this->db->sql_fetch_assoc($qry))
			{
				// check if it is a text-field
				$is_text = false;
				foreach($text_fields as $tf)
				{
					if(PLIB_String::strpos($data['Type'],$tf) !== false)
					{
						$is_text = true;
						break;
					}
				}
				
				// add the field?
				if($is_text)
				{
					$fields .= 'CHANGE `'.$data['Field'].'` `'.$data['Field'].'` '.$data['Type'];
					$fields .= ' CHARACTER SET '.$charset.' COLLATE '.$collate.' ';
					$fields .= $data['Null'] == 'NO' ? 'NOT NULL' : 'NULL';
					$fields .= ',';			
				}
			}
			$this->db->sql_free($qry);
			
			// if there are fields to change do it
			if($fields != '')
			{
				$this->db->sql_qry(
					'ALTER TABLE '.$table.' '.PLIB_String::substr($fields,0,-1).';'
				);
			}
		}
	}
}
?>