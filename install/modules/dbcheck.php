<?php
/**
 * Contains the dbcheck module for the installation
 * 
 * @version			$Id: dbcheck.php 543 2008-04-10 07:32:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	install
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The dbcheck-module
 * 
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_Install_dbcheck extends BS_Install
{
	public function run()
	{
		$prefix = $this->functions->get_session_var('table_prefix');
		$type = $this->functions->get_session_var('install_type');
		
		$this->tpl->set_template('step_config.htm',0);
		
		$configs = array();
		$configs[] = array('type' => 'separator');
		
		if($type == 'update')
		{
			$tables = array(
				'acp_access','activation','attachments','avatars','banlist','cache','change_pw',
				'config','events','forums','intern','ip','languages','links','moderators','pms',
				'poll_votes','polls','posts','profiles','search','sessions','smileys','subscriptions',
				'tasks','themes','topics','user','user_bans','user_fields','user_groups','user_ranks'
			);
			
			$len = count($tables);
			for($i = 0;$i < $len;$i++)
			{
				$configs[] = $this->functions->get_config_status(
					$prefix.$tables[$i].':',$this->_check[$tables[$i]],$this->locale->lang('ok'),$this->locale->lang('notok')
				);
			}
		}
		else
		{
			$tables = array(
				'acp_access','activation','attachments','avatars','banlist',
				'bots','cache','change_email','change_pw','config','events',
				'forums','intern','languages','links','log_errors','log_ips',
				'moderators','pms','polls','poll_votes','posts','profiles',
				'search','sessions','smileys','subscriptions','tasks','themes',
				'topics','user','user_bans','user_fields','user_groups','user_ranks'
			);
			
			$len = count($tables);
			for($i = 0;$i < $len;$i++)
			{
				$configs[] = $this->functions->get_config_status(
					$prefix.$tables[$i].':',!$this->_check[$tables[$i]],$this->locale->lang('notavailable'),
					$this->locale->lang('available')
				);
			}
		}
		
		$this->tpl->add_array('configs',$configs);
		$this->tpl->add_variables(array(
			'prefix' => $prefix,
			'show_table_prefix' => true,
			'title' => $this->locale->lang('step_dbcheck')
		));
		
		echo $this->tpl->parse_template();
	}
	
	public function check_inputs(&$check)
	{
		$prefix = $this->functions->get_session_var('table_prefix');
		$type = $this->functions->get_session_var('install_type');
		$host = $this->functions->get_session_var('host');
		$login = $this->functions->get_session_var('login');
		$password = $this->functions->get_session_var('password');
		$database = $this->functions->get_session_var('database');
		
		$con = @mysql_connect($host,$login,$password);
		@mysql_select_db($database,$con);
		
		$errors = array();
		if($type == 'update')
		{
			$fields = array(
				'acp_access'			=> array(
					'id','module','access_type','access_value'
				),
				'activation'			=> array(
					'user_id','user_key'
				),
				'attachments'			=> array(
					'id','pm_id','thread_id','post_id','poster_id','attachment_size',
					'attachment_path','downloads'
				),
				'avatars'			=> array(
					'id','av_pfad','user'
				),
				'banlist'			=> array(
					'id','bann_name','bann_type'
				),
				'cache'			=> array(
					'table_name','table_content'
				),
				'change_pw'			=> array(
					'user_id','user_key','email_date'
				),
				'config'			=> array(
					'posts_per_page','threads_per_page','links_per_page','members_per_page','spam_post_on','spam_post_time',
					'spam_thread_on','spam_thread_time','spam_reg_on','spam_reg_time','spam_threadview_on',
					'spam_threadview_time','spam_pm_on','spam_pm_time','spam_linkadd_on','spam_linkadd',
					'spam_linkview_on','spam_linkview_time','spam_email_on','spam_email_time','spam_search_on',
					'spam_search_time','forum_title','post_max_smileys','post_max_pics','post_smileys_on',
					'post_bbcode_on','post_max_length','post_max_string_len','post_parse_urls','post_code_highlight',
					'post_stats_type','post_font_pool','post_show_edited','signature_smileys_on','signature_bbcode_on',
					'signature_max_length','thread_max_title_len','thread_hot_posts_count','thread_hot_views_count','profile_max_img_width',
					'profile_max_img_height','profile_min_user_len','profile_max_user_len','profile_max_pw_len','profile_max_img_filesize',
					'profile_max_avatars','profile_user_special_chars','enable_linklist','linklist_activate_links','linklist_smileys_on',
					'linklist_bbcode_on','events_in_calendar','pm_max_inbox','pm_max_outbox','enable_pms',
					'get_email_new_account','get_email_new_link','account_activation','enable_email_notification','enable_emails',
					'max_poll_options','cookie_path','cookie_domain','board_email','show_always_page_split',
					'badwords_highlight','badwords_spaces_around','badwords','badwords_default_replacement','enable_badwords',
					'enable_memberlist','enable_stats','enable_faq','enable_calendar','enable_search',
					'enable_avatars','enable_polls','enable_events','enable_gzip','enable_security_code',
					'attachments_max_number','attachments_max_per_post','attachments_per_user','attachments_max_space_usage','attachments_max_filesize',
					'attachments_filetypes','attachments_enable','attachments_images_show','attachments_images_width','attachments_images_height',
					'attachments_images_resize_method','default_forum_style','allow_custom_style','default_forum_lang','allow_custom_lang',
					'current_topic_enable','current_topic_loc','current_topic_num','default_timezone','default_daylight_saving',
					'enable_board','board_disabled_text','mod_edit_posts','mod_delete_posts','mod_split_posts',
					'mod_edit_topics','mod_delete_topics','mod_move_topics','mod_openclose_topics','mod_mark_topics_important',
					'mod_color','mod_rank_filled_image','mod_rank_empty_image','hide_denied_forums','enable_signature_images',
					'allow_ghost_mode','max_forum_subscriptions','max_topic_subscriptions','enable_signatures','default_bbcode_mode',
					'enable_post_count','enable_user_ranks','profile_max_user_changes','profile_max_login_tries','ip_validation_type',
					'validate_user_agent','display_similar_topics','similar_topic_num','default_posts_order','use_captcha_for_guests',
					'events_cache','enable_calendar_events'
				),
				'events'			=> array(
					'id','tid','user_id','event_title','event_begin','event_end',
					'announced_user','max_announcements','description','event_location','timeout',
				
				),
				'forums'			=> array(
					'id','parent_id','sortierung','forum_name','description','forum_type',
					'forum_is_intern','forum_is_closed','increase_experience','display_subforums','threads',
					'posts','lastpost_id','permission_thread','permission_poll','permission_event',
					'permission_post'
				),
				'intern'			=> array(
					'id','fid','access_type','access_value'
				),
				'ip'			=> array(
					'ip','type','time'
				),
				'languages'			=> array(
					'id','lang_folder','lang_name'
				),
				'links'			=> array(
					'id','category','link_url','link_desc','link_desc_posted','clicks',
					'votes','vote_points','link_date','user_id','active'
				),
				'moderators'			=> array(
					'id','user_id','rid'
				),
				'pms'			=> array(
					'id','receiver_id','sender_id','pm_type','pm_title','pm_text',
					'pm_text_posted','pm_date','pm_read'
				),
				'poll_votes'			=> array(
					'poll_id','user_id'
				),
				'polls'			=> array(
					'id','pid','option_name','option_value','multichoice'
				),
				'posts'			=> array(
					'id','rubrikid','threadid','post_user','post_time','text',
					'text_posted','post_an_user','post_an_mail','use_smileys','use_bbcode',
					'ip_adresse','edit_lock','edited_times','edited_date','edited_user'
				),
				'profiles'			=> array(
					'id','avatar','signatur','registerdate','posts','exppoints','logins','lastlogin',
					'active','banned','linkvotes','default_font','allow_pms',
					'ghost_mode','last_unread_update','online','bbcode_mode','attach_signature',
					'allow_board_emails','forum_style','forum_lang','default_email_notification','timezone',
					'daylight_saving','user_group','enable_pm_email','email_display_mode','emails_include_post',
					'signature_posted','username_changes','login_tries','store_unread_in_cookie',
					'posts_order','unsent_posts','email_notification_type','last_email_notification',
					'last_search_time'
				),
				'search'			=> array(
					'id','session_id','search_date','result_ids','result_type','keywords',
					'search_mode'
				),
				'sessions'			=> array(
					'session_id','user_id','user_ip','date','location','user_agent',
					'unread_topics','session_data'
				),
				'smileys'			=> array(
					'id','smiley_path','primary_code','secondary_code','is_base'
				),
				'subscriptions'			=> array(
					'id','forum_id','topic_id','user_id','sub_date'
				),
				'tasks'			=> array(
					'id','task_title','task_file','task_interval','task_time','last_execution',
					'enabled'
				),
				'themes'			=> array(
					'id','theme_folder','theme_name'
				),
				'topics'			=> array(
					'id','rubrikid','name','post_time','post_user','symbol',
					'type','comallow','views','moved','posts',
					'post_an_user','post_an_mail','lastpost_id','lastpost_time','lastpost_user',
					'lastpost_an_user','important','thread_closed','last_status_ch_from','last_edited_from',
					'moved_rid','moved_tid','status_lock','edit_lock'
				),
				'user'			=> array(
					'id','user_name','user_pw','user_email'
				),
				'user_bans'			=> array(
					'id','user_id','baned_user'
				),
				'user_fields'			=> array(
					'id','field_name','field_type','field_length','field_sort','field_show_type',
					'display_name','allowed_values','field_validation','field_suffix','field_custom_display',
					'field_is_required','field_edit_notice','display_always'
				),
				'user_groups'			=> array(
					'id','group_title','group_color','group_rank_filled_image','group_rank_empty_image','overrides_mod',
					'is_super_mod','is_visible','view_memberlist','view_linklist','view_stats',
					'view_calendar','view_search','view_userdetails','view_online_locations','edit_own_posts',
					'delete_own_posts','edit_own_threads','delete_own_threads','openclose_own_threads','send_mails',
					'add_new_link','attachments_add','attachments_download','add_cal_event','edit_cal_event',
					'delete_cal_event','subscribe_forums','disable_ip_blocks','view_user_ip','enter_board',
					'view_user_online_detail','always_edit_poll_options'
				),
				'user_ranks'			=> array(
					'id','rank','post_to','post_from'
				),
			);
			
			foreach($fields as $table => $sql_fields)
			{
				$check[$table] = @mysql_query(
					"SELECT ".implode(',',$sql_fields)." FROM `".$prefix.$table."` LIMIT 1",$con
				);
				if(!$check[$table])
					$errors[] = '<b>'.$prefix.$table.'</b>: MySQL-ERROR: '.mysql_error($con);
			}
		}
		else
		{
			$tables = array(
				'acp_access','activation','attachments','avatars','banlist',
				'bots','cache','change_email','change_pw','config','events',
				'forums','intern','languages','links','log_errors','log_ips',
				'moderators','pms','polls','poll_votes','posts','profiles',
				'search','sessions','smileys','subscriptions','tasks','themes',
				'topics','user','user_bans','user_fields','user_groups','user_ranks'
			);
			
			$count = 0;
			foreach($tables as $name)
			{
				$check[$name] = @mysql_query("SELECT * FROM ".$prefix.$name." LIMIT 1",$con);
				if($check[$name])
					$count++;
			}
			
			if($count > 0)
				$errors[] = $this->locale->lang('table_exists_error');
		}
		
		return array(count($errors) == 0,$errors);
	}
}
?>