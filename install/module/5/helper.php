<?php
/**
 * Contains the helper-class for step5
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Helper-methods for the step5
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_5_Helper extends FWS_UtilBase
{
	/**
	 * @return array all tables of BS (constant-name => $prefix.table-name)
	 */
	public static function get_tables()
	{
		$user = FWS_Props::get()->user();
		$prefix = $user->get_session_data('table_prefix','bs_');
		return array(
			'BS_TB_ACP_ACCESS' => $prefix.'acp_access',
			'BS_TB_ACTIVATION' => $prefix.'activation',
			'BS_TB_ATTACHMENTS' => $prefix.'attachments',
			'BS_TB_AVATARS' => $prefix.'avatars',
			'BS_TB_BANS' => $prefix.'banlist',
			'BS_TB_BBCODES' => $prefix.'bbcodes',
			'BS_TB_BOTS' => $prefix.'bots',
			'BS_TB_CACHE' => $prefix.'cache',
			'BS_TB_CHANGE_EMAIL' => $prefix.'change_email',
			'BS_TB_CHANGE_PW' => $prefix.'change_pw',
			'BS_TB_CONFIG' => $prefix.'cfg',
			'BS_TB_CONFIG_GROUPS' => $prefix.'cfg_groups',
			'BS_TB_EVENTS' => $prefix.'events',
			'BS_TB_EVENT_ANN' => $prefix.'events_announcements',
			'BS_TB_FORUMS' => $prefix.'forums',
			'BS_TB_FORUMS_PERM' => $prefix.'forums_perm',
			'BS_TB_INTERN' => $prefix.'intern',
			'BS_TB_LANGS' => $prefix.'languages',
			'BS_TB_LINKS' => $prefix.'links',
			'BS_TB_LINK_VOTES' => $prefix.'links_votes',
			'BS_TB_LOG_ERRORS' => $prefix.'log_errors',
			'BS_TB_LOG_IPS' => $prefix.'log_ips',
			'BS_TB_MODS' => $prefix.'moderators',
			'BS_TB_PMS' => $prefix.'pms',
			'BS_TB_POLL' => $prefix.'polls',
			'BS_TB_POLL_VOTES' => $prefix.'poll_votes',
			'BS_TB_POSTS' => $prefix.'posts',
			'BS_TB_PROFILES' => $prefix.'profiles',
			'BS_TB_RANKS' => $prefix.'user_ranks',
			'BS_TB_SEARCH' => $prefix.'search',
			'BS_TB_SESSIONS' => $prefix.'sessions',
			'BS_TB_SMILEYS' => $prefix.'smileys',
			'BS_TB_SUBSCR' => $prefix.'subscriptions',
			'BS_TB_TASKS' => $prefix.'tasks',
			'BS_TB_THEMES' => $prefix.'themes',
			'BS_TB_THREADS' => $prefix.'topics',
			'BS_TB_UNREAD' => $prefix.'unread',
			'BS_TB_UNREAD_HIDE' => $prefix.'unread_hide',
			'BS_TB_UNSENT_POSTS' => $prefix.'unsent_posts',
			'BS_TB_USER' => $prefix.'user',
			'BS_TB_USER_BANS' => $prefix.'user_bans',
			'BS_TB_USER_FIELDS' => $prefix.'user_fields',
			'BS_TB_USER_GROUPS' => $prefix.'user_groups'
		);
	}
	
	/**
	 * Generates the settings
	 */
	public static function generate_settings()
	{
		$db = FWS_Props::get()->db();
		$user = FWS_Props::get()->user();
		$consts = self::get_tables();
		
		$groups = array(
			############# GENERAL ##############
			array(
				'name' => 'general',
				'title' => 'general',
				'subgroups' => array(
					array(
						'name' => 'default',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_board',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'board_disabled_text',
								'type' => 'multiline',
								'properties' => "width=90%\nheight=80px",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => "Das Board ist aufgrund von Wartungsarbeiten vor&uuml;bergehend deaktiviert."
									." Bitte haben Sie Verst&auml;ndnis."
							),
						),
					),
					array(
						'name' => 'name_cookies',
						'title' => '',
						'items' => array(
							array(
								'name' => 'board_url',
								'type' => 'line',
								'properties' => "size=50\nmaxlen=500",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => $user->get_session_data('board_url')
							),
							array(
								'name' => 'forum_title',
								'type' => 'line',
								'properties' => "size=50\nmaxlen=500",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'Boardsolution'
							),
							array(
								'name' => 'cookie_path',
								'type' => 'line',
								'properties' => "size=30\nmaxlen=100",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => '/'
							),
							array(
								'name' => 'cookie_domain',
								'type' => 'line',
								'properties' => "size=30\nmaxlen=100",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => ''
							),
						),
					),
					array(
						'name' => 'locale',
						'title' => '',
						'items' => array(
							array(
								'name' => 'default_timezone',
								'type' => 'timezone',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'Europe/Berlin'
							),
							array(
								'name' => 'default_forum_lang',
								'type' => 'languages',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 3
							),
							array(
								'name' => 'allow_custom_lang',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'default_forum_style',
								'type' => 'themes',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'allow_custom_style',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
						),
					),
					array(
						'name' => 'other',
						'title' => '',
						'items' => array(
							array(
								'name' => 'hide_denied_forums',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'display_denied_options',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'display_ministats',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'show_always_page_split',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'enable_modrewrite',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 0
							),
							array(
								'name' => 'enable_gzip',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 0
							),
							array(
								'name' => 'enable_error_log',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'error_log_days',
								'type' => 'int',
								'properties' => "size=5\nmaxlen=4",
								'suffix' => '%days',
								'affects_msgs' => 0,
								'default' => 7
							),
						),
					),
				),
			),
			############# SECURITY ##############
			array(
				'name' => 'security',
				'title' => 'security',
				'subgroups' => array(
					array(
						'name' => 'default',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_security_code',
								'type' => 'yesno',
								'properties' => '',
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'use_captcha_for_guests',
								'type' => 'yesno',
								'properties' => '',
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'profile_max_login_tries',
								'type' => 'int',
								'properties' => "size=2\nmaxlen=2",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 2
							),
							array(
								'name' => 'ip_validation_type',
								'type' => 'enum',
								'properties' => "type=combo\nA.B.C.D=ip_validation_A.B.C.D\n"
									."A.B.C=ip_validation_A.B.C\nA.B=ip_validation_A.B\nnone=ip_validation_none",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'A.B.C.D'
							),
							array(
								'name' => 'validate_user_agent',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'ip_log_days',
								'type' => 'line',
								'properties' => "size=2\nmaxlen=4",
								'suffix' => '%days',
								'affects_msgs' => 0,
								'default' => 5
							),
						)
					),
				)
			),
			############# MODERATORS ##############
			array(
				'name' => 'moderators',
				'title' => 'moderators',
				'subgroups' => array(
					array(
						'name' => 'default',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_moderators',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'mod_color',
								'type' => 'color',
								'properties' => "size=6\nmaxlen=6",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => '008000'
							),
							array(
								'name' => 'mod_rank_filled_image',
								'type' => 'line',
								'properties' => "size=40\nmaxlen=255",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'images/ranks/mod.gif'
							),
							array(
								'name' => 'mod_rank_empty_image',
								'type' => 'line',
								'properties' => "size=40\nmaxlen=255",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'images/ranks/mod.gif'
							),
							array(
								'name' => 'mod_edit_posts',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'mod_delete_posts',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'mod_split_posts',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'mod_edit_topics',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'mod_delete_topics',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'mod_move_topics',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'mod_openclose_topics',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'mod_lock_topics',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'mod_mark_topics_important',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
						),
					),
				),
			),
			############# MESSAGING ##############
			array(
				'name' => 'messaging',
				'title' => 'messaging',
				'subgroups' => array(
					array(
						'name' => 'default',
						'title' => '',
						'items' => array(
							array(
								'name' => 'board_email',
								'type' => 'line',
								'properties' => "size=30\nmaxlen=100",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'board@domain.de'
							),
							array(
								'name' => 'mail_method',
								'type' => 'enum',
								'properties' => "type=radio\nmail=mail_method_mail\nsmtp=mail_method_smtp",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'mail'
							),
							array(
								'name' => 'smtp_host',
								'type' => 'line',
								'properties' => "size=50\nmaxlen=255",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => ''
							),
							array(
								'name' => 'smtp_port',
								'type' => 'int',
								'properties' => "size=5\nmaxlen=10",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 25
							),
							array(
								'name' => 'smtp_login',
								'type' => 'line',
								'properties' => "size=30\nmaxlen=255",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => ''
							),
							array(
								'name' => 'smtp_password',
								'type' => 'password',
								'properties' => "size=30\nmaxlen=255",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => ''
							),
							array(
								'name' => 'smtp_use_ltgt',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
						),
					),
					array(
						'name' => 'email_notification',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_email_notification',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'max_topic_subscriptions',
								'type' => 'int',
								'properties' => "size=6\nmaxlen=10",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 20
							),
							array(
								'name' => 'max_forum_subscriptions',
								'type' => 'int',
								'properties' => "size=6\nmaxlen=10",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 3
							),
						),
					),
					array(
						'name' => 'pms',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_pms',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'spam_pm',
								'type' => 'spam',
								'properties' => "",
								'suffix' => '%seconds',
								'affects_msgs' => 0,
								'default' => 60
							),
							array(
								'name' => 'pm_max_inbox',
								'type' => 'int',
								'properties' => "size=6\nmaxlen=4",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 0
							),
							array(
								'name' => 'pm_max_outbox',
								'type' => 'int',
								'properties' => "size=6\nmaxlen=4",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 0
							),
						),
					),
					array(
						'name' => 'emails',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_emails',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'spam_email',
								'type' => 'spam',
								'properties' => "",
								'suffix' => '%seconds',
								'affects_msgs' => 0,
								'default' => 120
							),
						),
					),
				),
			),
			############# FORMATTING ##############
			array(
				'name' => 'message_formating',
				'title' => 'message_formating',
				'subgroups' => array(
					array(
						'name' => 'general',
						'title' => 'general',
						'items' => array(
							array(
								'name' => 'msgs_default_bbcode_mode',
								'type' => 'enum',
								'properties' => "type=combo\n"
									."simple=bbcode_mode_simple\nadvanced=bbcode_mode_advanced\napplet=bbcode_mode_applet",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'advanced'
							),
							array(
								'name' => 'msgs_allow_java_applet',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'msgs_max_line_length',
								'type' => 'int',
								'properties' => "size=5\nmaxlen=5",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 100
							),
							array(
								'name' => 'msgs_parse_urls',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 1
							),
							array(
								'name' => 'msgs_code_highlight',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 1
							),
							array(
								'name' => 'msgs_code_line_numbers',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 1
							),
						),
					),
					array(
						'name' => 'formating_posts_pms',
						'title' => 'formating_posts_pms',
						'items' => array(
							array(
								'name' => 'posts_enable_bbcode',
								'custom_title' => 'enable_bbcode',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 1
							),
							array(
								'name' => 'posts_enable_smileys',
								'custom_title' => 'enable_smileys',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 1
							),
							array(
								'name' => 'posts_max_images',
								'custom_title' => 'max_images',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=4",
								'suffix' => '',
								'affects_msgs' => 1,
								'default' => 10
							),
							array(
								'name' => 'posts_max_smileys',
								'custom_title' => 'max_smileys',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=4",
								'suffix' => '',
								'affects_msgs' => 1,
								'default' => 100
							),
							array(
								'name' => 'posts_max_length',
								'custom_title' => 'max_length',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=9",
								'suffix' => '',
								'affects_msgs' => 1,
								'default' => 25000
							),
							array(
								'name' => 'posts_allowed_tags',
								'custom_title' => 'allowed_tags',
								'type' => 'line',
								'properties' => "size=70\nmaxlen=20000",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 'b,i,u,size,font,color,url,mail,img,code,quote,list,topic,'
									.'post,left,right,center,s,sup,sub,att,attimg'
							),
						),
					),
					array(
						'name' => 'signatur',
						'title' => 'signature',
						'items' => array(
							array(
								'name' => 'sig_enable_bbcode',
								'custom_title' => 'enable_bbcode',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 1
							),
							array(
								'name' => 'sig_enable_smileys',
								'custom_title' => 'enable_smileys',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 1
							),
							array(
								'name' => 'sig_max_images',
								'custom_title' => 'max_images',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=4",
								'suffix' => '',
								'affects_msgs' => 1,
								'default' => 1
							),
							array(
								'name' => 'sig_max_smileys',
								'custom_title' => 'max_smileys',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=4",
								'suffix' => '',
								'affects_msgs' => 1,
								'default' => 3
							),
							array(
								'name' => 'sig_max_length',
								'custom_title' => 'max_length',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=9",
								'suffix' => '',
								'affects_msgs' => 1,
								'default' => 500
							),
							array(
								'name' => 'sig_max_height',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=9",
								'suffix' => '%pixel',
								'affects_msgs' => 0,
								'default' => 100
							),
							array(
								'name' => 'sig_allowed_tags',
								'custom_title' => 'allowed_tags',
								'type' => 'line',
								'properties' => "size=70\nmaxlen=20000",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 'b,i,u,size,font,color,url,mail,img,code,quote,list,topic,post'
							),
						),
					),
					array(
						'name' => 'linklist_descriptions',
						'title' => 'linklist_descriptions',
						'items' => array(
							array(
								'name' => 'desc_enable_bbcode',
								'custom_title' => 'enable_bbcode',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 1
							),
							array(
								'name' => 'desc_enable_smileys',
								'custom_title' => 'enable_smileys',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 1
							),
							array(
								'name' => 'desc_max_images',
								'custom_title' => 'max_images',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=4",
								'suffix' => '',
								'affects_msgs' => 1,
								'default' => 1
							),
							array(
								'name' => 'desc_max_smileys',
								'custom_title' => 'max_smileys',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=4",
								'suffix' => '',
								'affects_msgs' => 1,
								'default' => 3
							),
							array(
								'name' => 'desc_max_length',
								'custom_title' => 'max_length',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=9",
								'suffix' => '',
								'affects_msgs' => 1,
								'default' => 500
							),
							array(
								'name' => 'desc_allowed_tags',
								'custom_title' => 'allowed_tags',
								'type' => 'line',
								'properties' => "size=70\nmaxlen=20000",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 'b,i,u,size,font,color,url,mail,img,code,quote,list,topic,post'
							),
						),
					),
				),
			),
			############# BADWORDS ##############
			array(
				'name' => 'badwords',
				'title' => 'badwords',
				'subgroups' => array(
					array(
						'name' => 'default',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_badwords',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 0
							),
							array(
								'name' => 'badwords_spaces_around',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => 1
							),
							array(
								'name' => 'badwords_highlight',
								'type' => 'line',
								'properties' => "size=40\nmaxlen=100",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => '<i>{value}</i>'
							),
							array(
								'name' => 'badwords_default_replacement',
								'type' => 'line',
								'properties' => "size=30\nmaxlen=100",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => '*censored*'
							),
							array(
								'name' => 'badwords_definitions',
								'type' => 'multiline',
								'properties' => "width=90%\nheight=200px",
								'suffix' => '',
								'affects_msgs' => 2,
								'default' => ''
							),
						),
					),
				),
			),
			############# TOPICS ##############
			array(
				'name' => 'threads',
				'title' => 'threads',
				'subgroups' => array(
					array(
						'name' => 'default',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_polls',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'enable_events',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
						),
					),
					array(
						'name' => 'topiclists',
						'title' => '',
						'items' => array(
							array(
								'name' => 'current_topic_enable',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'current_topic_loc',
								'type' => 'currenttopicloc',
								'properties' => "type=check\ntop=forums_top\nbottom=forums_bottom\nportal=portal",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'top'
							),
							array(
								'name' => 'current_topic_num',
								'type' => 'int',
								'properties' => "size=5\nmaxlen=3",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 5
							),
							array(
								'name' => 'display_similar_topics',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'similar_topic_num',
								'type' => 'int',
								'properties' => "size=5\nmaxlen=3",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 5
							),
						),
					),
					array(
						'name' => 'topics',
						'title' => '',
						'items' => array(
							array(
								'name' => 'thread_hot_posts_count',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=10",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 15
							),
							array(
								'name' => 'thread_hot_views_count',
								'type' => 'int',
								'properties' => "size=4\nmaxlen=10",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 300
							),
							array(
								'name' => 'thread_max_title_len',
								'type' => 'int',
								'properties' => "size=5\nmaxlen=5",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 40
							),
							array(
								'name' => 'threads_per_page',
								'type' => 'int',
								'properties' => "size=3\nmaxlen=3",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 20
							),
							array(
								'name' => 'max_poll_options',
								'type' => 'int',
								'properties' => "size=3\nmaxlen=2",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 15
							),
						),
					),
					array(
						'name' => 'other',
						'title' => '',
						'items' => array(
							array(
								'name' => 'spam_thread',
								'type' => 'spam',
								'properties' => "",
								'suffix' => '%seconds',
								'affects_msgs' => 0,
								'default' => 60
							),
							array(
								'name' => 'spam_threadview',
								'type' => 'spam',
								'properties' => "",
								'suffix' => '%seconds',
								'affects_msgs' => 0,
								'default' => 1800
							),
						),
					),
				),
			),
			############# POSTS ##############
			array(
				'name' => 'posts',
				'title' => 'posts',
				'subgroups' => array(
					array(
						'name' => 'default',
						'title' => '',
						'items' => array(
							array(
								'name' => 'spam_post',
								'type' => 'spam',
								'properties' => "",
								'suffix' => '%seconds',
								'affects_msgs' => 0,
								'default' => 60
							),
							array(
								'name' => 'post_font_pool',
								'type' => 'line',
								'properties' => "size=80\nmaxlen=1000",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'Verdana,Helvetica,Courier New,Arial'
							),
							array(
								'name' => 'posts_per_page',
								'type' => 'int',
								'properties' => "size=3\nmaxlen=3",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 15
							),
							array(
								'name' => 'default_posts_order',
								'type' => 'enum',
								'properties' => "type=radio\nASC=posts_order_ascending\nDESC=posts_order_descending",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'ASC'
							),
							array(
								'name' => 'post_show_edited',
								'type' => 'enum',
								'properties' => "type=combo\nalways=always\nnot_lastpost=not_lastpost\nnever=never",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'always'
							),
						),
					),
				),
			),
			############# USER ##############
			array(
				'name' => 'user',
				'title' => 'setting_group_user',
				'subgroups' => array(
					array(
						'name' => 'default',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_registrations',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'get_email_new_account',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'account_activation',
								'type' => 'enum',
								'properties' => "type=combo\n"
									."none=account_act_none\nemail=account_act_email\nadmin=account_act_admin",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'email'
							),
							array(
								'name' => 'spam_reg',
								'type' => 'spam',
								'properties' => "",
								'suffix' => '%seconds',
								'affects_msgs' => 0,
								'default' => 3600
							),
						),
					),
					array(
						'name' => 'avatars',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_avatars',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'profile_max_img_size',
								'type' => 'size',
								'properties' => "size=5\nmaxlen=5",
								'suffix' => '%pixel',
								'affects_msgs' => 0,
								'default' => '150x120'
							),
							array(
								'name' => 'profile_max_img_filesize',
								'type' => 'int',
								'properties' => "size=8\nmaxlen=6",
								'suffix' => 'KB',
								'affects_msgs' => 0,
								'default' => 50
							),
							array(
								'name' => 'profile_max_avatars',
								'type' => 'int',
								'properties' => "size=2\nmaxlen=2",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 8
							),
						),
					),
					array(
						'name' => 'experience',
						'title' => '',
						'items' => array(
							array(
								'name' => 'post_stats_type',
								'type' => 'enum',
								'properties' => "type=combo\n"
									."disabled=disabled\ncurrent_rank=current_rank\ncontinuous=continuous\n"
									."newbie_friendly=newbie_friendly",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'newbie_friendly'
							),
							array(
								'name' => 'enable_user_ranks',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'enable_post_count',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
						),
					),
					array(
						'name' => 'profile',
						'title' => '',
						'items' => array(
							array(
								'name' => 'force_fill_of_empty_req_fields',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'always_color_usernames',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 0
							),
							array(
								'name' => 'allow_email_changes',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'confirm_email_addresses',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'profile_max_user_changes',
								'type' => 'int',
								'properties' => "size=2\nmaxlen=2",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 4
							),
							array(
								'name' => 'profile_min_user_len',
								'type' => 'int',
								'properties' => "size=2\nmaxlen=2",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 3
							),
							array(
								'name' => 'profile_max_user_len',
								'type' => 'int',
								'properties' => "size=2\nmaxlen=2",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 30
							),
							array(
								'name' => 'profile_max_pw_len',
								'type' => 'int',
								'properties' => "size=2\nmaxlen=2",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 30
							),
							array(
								'name' => 'profile_user_special_chars',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 0
							),
						),
					),
					array(
						'name' => 'signature',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_signatures',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'allow_ghost_mode',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
						),
					),
				),
			),
			############# MODULES ##############
			array(
				'name' => 'modules',
				'title' => 'modules',
				'subgroups' => array(
					array(
						'name' => 'default',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_memberlist',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'enable_linklist',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'enable_stats',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'enable_faq',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'enable_calendar',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'enable_search',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
						)
					),
					array(
						'name' => 'linklist',
						'title' => 'linklist',
						'items' => array(
							array(
								'name' => 'spam_linkview',
								'type' => 'spam',
								'properties' => "",
								'suffix' => '%seconds',
								'affects_msgs' => 0,
								'default' => 3600
							),
							array(
								'name' => 'spam_linkadd',
								'type' => 'spam',
								'properties' => "",
								'suffix' => '%seconds',
								'affects_msgs' => 0,
								'default' => 60
							),
							array(
								'name' => 'linklist_activate_links',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'get_email_new_link',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
						),
					),
					array(
						'name' => 'other',
						'title' => 'acpcat_other',
						'items' => array(
							array(
								'name' => 'members_per_page',
								'type' => 'int',
								'properties' => "size=5\nmaxlen=5",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 20
							),
							array(
								'name' => 'spam_search',
								'type' => 'spam',
								'properties' => "",
								'suffix' => '%seconds',
								'affects_msgs' => 0,
								'default' => 10
							),
							array(
								'name' => 'enable_calendar_events',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
						),
					),
				),
			),
			############# PORTAL ##############
			array(
				'name' => 'portal',
				'title' => 'portal',
				'subgroups' => array(
					array(
						'name' => 'default',
						'title' => '',
						'items' => array(
							array(
								'name' => 'enable_portal',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'enable_portal_news',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'news_count',
								'type' => 'int',
								'properties' => "size=5\nmaxlen=10",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 10
							),
							array(
								'name' => 'enable_news_feeds',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'news_forums',
								'type' => 'forums',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => ''
							),
						),
					),
				),
			),
			############# ATTACHMENTS ##############
			array(
				'name' => 'attachments',
				'title' => 'attachments',
				'subgroups' => array(
					array(
						'name' => 'default',
						'title' => '',
						'items' => array(
							array(
								'name' => 'attachments_enable',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'attachments_max_number',
								'type' => 'int',
								'properties' => "size=5\nmaxlen=6",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 0
							),
							array(
								'name' => 'attachments_max_per_post',
								'type' => 'int',
								'properties' => "size=5\nmaxlen=6",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 3
							),
							array(
								'name' => 'attachments_per_user',
								'type' => 'int',
								'properties' => "size=5\nmaxlen=6",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 0
							),
							array(
								'name' => 'attachments_max_filesize',
								'type' => 'int',
								'properties' => "size=10\nmaxlen=10",
								'suffix' => 'KB',
								'affects_msgs' => 0,
								'default' => 500
							),
							array(
								'name' => 'attachments_filetypes',
								'type' => 'line',
								'properties' => "size=80\nmaxlen=255",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'zip|rar|tar|ini|txt|jpeg|png|jpg|gif|xml'
							),
						),
					),
					array(
						'name' => 'images',
						'title' => '',
						'items' => array(
							array(
								'name' => 'attachments_images_show',
								'type' => 'yesno',
								'properties' => "",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 1
							),
							array(
								'name' => 'attachments_images_size',
								'type' => 'size',
								'properties' => "size=5\nmaxlen=5",
								'suffix' => '%pixel',
								'affects_msgs' => 0,
								'default' => '300x200'
							),
							array(
								'name' => 'attachments_images_resize_method',
								'type' => 'enum',
								'properties' => "type=combo\n"
									."width_fixed=width_fixed\nheight_fixed=height_fixed\nboth_fixed=both_fixed",
								'suffix' => '',
								'affects_msgs' => 0,
								'default' => 'width_fixed'
							),
						),
					),
				),
			),
		);
		
		$i = 1;
		foreach($groups as $group)
		{
			$values = $group;
			unset($values['items']);
			unset($values['subgroups']);
			$values['sort'] = $i++;
			$values['parent_id'] = 0;
			
			// do we have to create the group?
			$gid = $db->insert($consts['BS_TB_CONFIG_GROUPS'],$values);
			
			if(isset($group['subgroups']))
			{
				$a = 1;
				foreach($group['subgroups'] as $sub)
				{
					$values = $sub;
					unset($values['items']);
					$values['sort'] = $a++;
					$values['parent_id'] = $gid;
					
					// create sub-group
					$subgid = $db->insert($consts['BS_TB_CONFIG_GROUPS'],$values);
					
					$x = 1;
					foreach($sub['items'] as $item)
					{
						$val = $item['default'];
						$item['sort'] = $x++;
						$item['group_id'] = $subgid;
						$item['value'] = $val;
						$item['default'] = $val;
						if(!isset($item['custom_title']))
							$item['custom_title'] = '';
						
						$db->insert($consts['BS_TB_CONFIG'],$item);
					}
				}
			}
		}
	}
}
?>