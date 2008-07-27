<?php
/**
 * Contains the config-userprofile-submodule
 * 
 * @version			$Id$
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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		$doc->add_action(BS_ACTION_EDIT_PERS_CONFIG,'updateconfig');

		$doc->add_breadcrumb(
			$locale->lang('profileconfig'),
			$url->get_url(0,'&amp;'.BS_URL_LOC.'=config')
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$cfg = PLIB_Props::get()->cfg();
		$locale = PLIB_Props::get()->locale();
		$cache = PLIB_Props::get()->cache();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();

		$this->request_formular(false);
		
		$fonts = explode(',',$cfg['post_font_pool']);
		sort($fonts);

		$default_font_options = array(0 => '- '.$locale->lang('none').' -');
		for($i = 0;$i < count($fonts);$i++)
		{
			$current_font = trim($fonts[$i]);
			$default_font_options[$current_font] = $current_font;
		}

		$langs = array();
		if($cfg['allow_custom_lang'] == 1)
		{
			$langs[] = $locale->lang('default');
			foreach($cache->get_cache('languages') as $data)
				$langs[$data['id']] = $data['lang_name'];
		}

		$themes = array();
		if($cfg['allow_custom_style'] == 1)
		{
			$themes[0] = $locale->lang('default');
			foreach($cache->get_cache('themes') as $data)
				$themes[$data['id']] = $data['theme_name'];
		}

		$bbcode_mode_options = array(
			'simple' => $locale->lang('bbcode_mode_simple'),
			'advanced' => $locale->lang('bbcode_mode_advanced')
		);
		if($cfg['msgs_allow_java_applet'])
			$bbcode_mode_options['applet'] = $locale->lang('bbcode_mode_applet');
		
		$email_not_options = array(
			'immediatly' => $locale->lang('email_notify_immediatly'),
			'1day' => $locale->lang('email_notify_1day'),
			'2days' => $locale->lang('email_notify_2days'),
			'1week' => $locale->lang('email_notify_1week')
		);

		$posts_order_options = array(
			'ASC' => $locale->lang('posts_order_ascending'),
			'DESC' => $locale->lang('posts_order_descending')
		);

		$email_display_mode_options = array(
			'hide' => $locale->lang('email_display_mode_hide'),
			'jumble' => $locale->lang('email_display_mode_jumble'),
			'default' => $locale->lang('email_display_mode_default')
		);
		
		$startmodule_options = array(
			'portal' => $locale->lang('portal'),
			'forums' => $locale->lang('forums')
		);
		
		$enable_bbcode = $cfg['posts_enable_bbcode'] || $cfg['sig_enable_bbcode'] ||
			$cfg['desc_enable_bbcode'];
		
		$tpl->add_variables(array(
			'enable_pms' => $cfg['enable_pms'] == 1,
			'action_type' => BS_ACTION_EDIT_PERS_CONFIG,
			'ghost_mode_allowed' => $cfg['allow_ghost_mode'] == 1,
			'target_url' => $url->get_url('userprofile','&amp;'.BS_URL_LOC.'=config'),
			'allow_custom_lang' => $cfg['allow_custom_lang'],
			'allow_custom_style' => $cfg['allow_custom_style'],
			'lang_options' => $langs,
			'def_lang' => $user->get_profile_val('forum_lang'),
			'theme_options' => $themes,
			'def_theme' => $user->get_profile_val('forum_style'),
			'email_display_mode_options' => $email_display_mode_options,
			'def_email_display_mode' => $user->get_profile_val('email_display_mode'),
			'def_allow_board_emails' => $user->get_profile_val('allow_board_emails'),
			'def_default_email_notification' => $user->get_profile_val('default_email_notification'),
			'email_notification_type_options' => $email_not_options,
			'def_email_notification_type' => $user->get_profile_val('email_notification_type'),
			'def_emails_include_post' => $user->get_profile_val('emails_include_post'),
			'def_enable_pm_email' => $user->get_profile_val('enable_pm_email'),
			'def_allow_pms' => $user->get_profile_val('allow_pms'),
			'def_ghost_mode' => $user->get_profile_val('ghost_mode'),
			'posts_order_options' => $posts_order_options,
			'def_posts_order' => $user->get_profile_val('posts_order'),
			'default_font_options' => $default_font_options,
			'def_default_font' => $user->get_profile_val('default_font'),
			'def_attach_signature' => $user->get_profile_val('attach_signature'),
			'bbcode_mode_options' => $bbcode_mode_options,
			'def_bbcode_mode' => $user->get_profile_val('bbcode_mode'),
			'current_time' => PLIB_Date::get_date(time(),true,false),
			'enable_email_notification' => $cfg['enable_email_notification'],
			'allow_board_emails' => $cfg['enable_emails'],
			'enable_signatures' => $cfg['enable_signatures'],
			'portal_enabled' => $cfg['enable_portal'],
			'def_startmodule' => $user->get_profile_val('startmodule'),
			'startmodule_options' => $startmodule_options,
			'enable_bbcode' => $enable_bbcode
		));
	}
}
?>