<?php
/**
 * Contains the config-userprofile-submodule
 * 
 * @version			$Id: sub_config.php 705 2008-05-15 10:14:58Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The config submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_config extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_EDIT_PERS_CONFIG => 'updateconfig'
		);
	}
	
	public function run()
	{
		$this->_request_formular(false);
		
		$fonts = explode(',',$this->cfg['post_font_pool']);
		sort($fonts);

		$default_font_options = array(0 => '- '.$this->locale->lang('none').' -');
		for($i = 0;$i < count($fonts);$i++)
		{
			$current_font = trim($fonts[$i]);
			$default_font_options[$current_font] = $current_font;
		}

		$langs = array();
		if($this->cfg['allow_custom_lang'] == 1)
		{
			$langs[] = $this->locale->lang('default');
			foreach($this->cache->get_cache('languages') as $data)
				$langs[$data['id']] = $data['lang_name'];
		}

		$themes = array();
		if($this->cfg['allow_custom_style'] == 1)
		{
			$themes[0] = $this->locale->lang('default');
			foreach($this->cache->get_cache('themes') as $data)
				$themes[$data['id']] = $data['theme_name'];
		}

		$bbcode_mode_options = array(
			'simple' => $this->locale->lang('bbcode_mode_simple'),
			'advanced' => $this->locale->lang('bbcode_mode_advanced')
		);
		if($this->cfg['msgs_allow_java_applet'])
			$bbcode_mode_options['applet'] = $this->locale->lang('bbcode_mode_applet');
		
		$email_not_options = array(
			'immediatly' => $this->locale->lang('email_notify_immediatly'),
			'1day' => $this->locale->lang('email_notify_1day'),
			'2days' => $this->locale->lang('email_notify_2days'),
			'1week' => $this->locale->lang('email_notify_1week')
		);

		$posts_order_options = array(
			'ASC' => $this->locale->lang('posts_order_ascending'),
			'DESC' => $this->locale->lang('posts_order_descending')
		);

		$email_display_mode_options = array(
			'hide' => $this->locale->lang('email_display_mode_hide'),
			'jumble' => $this->locale->lang('email_display_mode_jumble'),
			'default' => $this->locale->lang('email_display_mode_default')
		);
		
		$startmodule_options = array(
			'portal' => $this->locale->lang('portal'),
			'forums' => $this->locale->lang('forums')
		);
		
		$enable_bbcode = $this->cfg['posts_enable_bbcode'] || $this->cfg['sig_enable_bbcode'] ||
			$this->cfg['lnkdesc_enable_bbcode'];
		
		$this->tpl->add_variables(array(
			'enable_pms' => $this->cfg['enable_pms'] == 1,
			'action_type' => BS_ACTION_EDIT_PERS_CONFIG,
			'ghost_mode_allowed' => $this->cfg['allow_ghost_mode'] == 1,
			'target_url' => $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=config'),
			'allow_custom_lang' => $this->cfg['allow_custom_lang'],
			'allow_custom_style' => $this->cfg['allow_custom_style'],
			'lang_options' => $langs,
			'def_lang' => $this->user->get_profile_val('forum_lang'),
			'theme_options' => $themes,
			'def_theme' => $this->user->get_profile_val('forum_style'),
			'email_display_mode_options' => $email_display_mode_options,
			'def_email_display_mode' => $this->user->get_profile_val('email_display_mode'),
			'def_allow_board_emails' => $this->user->get_profile_val('allow_board_emails'),
			'def_default_email_notification' => $this->user->get_profile_val('default_email_notification'),
			'email_notification_type_options' => $email_not_options,
			'def_email_notification_type' => $this->user->get_profile_val('email_notification_type'),
			'def_emails_include_post' => $this->user->get_profile_val('emails_include_post'),
			'def_enable_pm_email' => $this->user->get_profile_val('enable_pm_email'),
			'def_allow_pms' => $this->user->get_profile_val('allow_pms'),
			'def_ghost_mode' => $this->user->get_profile_val('ghost_mode'),
			'posts_order_options' => $posts_order_options,
			'def_posts_order' => $this->user->get_profile_val('posts_order'),
			'default_font_options' => $default_font_options,
			'def_default_font' => $this->user->get_profile_val('default_font'),
			'def_attach_signature' => $this->user->get_profile_val('attach_signature'),
			'bbcode_mode_options' => $bbcode_mode_options,
			'def_bbcode_mode' => $this->user->get_profile_val('bbcode_mode'),
			'current_time' => PLIB_Date::get_date(time(),true,false),
			'enable_email_notification' => $this->cfg['enable_email_notification'],
			'allow_board_emails' => $this->cfg['enable_emails'],
			'enable_signatures' => $this->cfg['enable_signatures'],
			'portal_enabled' => $this->cfg['enable_portal'],
			'def_startmodule' => $this->user->get_profile_val('startmodule'),
			'startmodule_options' => $startmodule_options,
			'enable_bbcode' => $enable_bbcode
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('profileconfig') => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=config')
		);
	}
}
?>