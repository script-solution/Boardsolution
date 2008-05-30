<?php
/**
 * Contains the update-config-action
 *
 * @version			$Id: action_updateconfig.php 713 2008-05-20 21:59:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The update-config-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_updateconfig extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$info['reload'] = false;

		// nothing to do?
		if(!$this->input->isset_var('submit','post'))
			return '';

		// the user has to be loggedin
		if(!$this->user->is_loggedin())
			return 'You are a guest';

		$fonts = explode(',',$this->cfg['post_font_pool']);
		PLIB_Array_Utils::trim($fonts);

		$email_display_mode = $this->input->correct_var('email_display_mode','post',PLIB_Input::STRING,
																							array('hide','jumble','default'),'default');
		$post_def_font = $this->input->get_var('default_font','post',PLIB_Input::STRING);
		$default_font = ($post_def_font !== 0 && in_array($post_def_font,$fonts)) ? $post_def_font : 0;
		if($this->cfg['enable_pms'] == 1)
			$allow_pms = $this->input->get_var('allow_pms','post',PLIB_Input::INT_BOOL);
		else
			$allow_pms = $this->user->get_profile_val('allow_pms');
		$ghost_mode = $this->input->get_var('ghost_mode','post',PLIB_Input::INT_BOOL);
		$bbcode_mode = $this->input->correct_var(
			'bbcode_mode','post',PLIB_Input::STRING,array('simple','advanced','applet'),'simple'
		);
		$attach_signature = $this->input->get_var('attach_signature','post',PLIB_Input::INT_BOOL);
		$allow_board_emails = $this->input->get_var('allow_board_emails','post',PLIB_Input::INT_BOOL);
		$default_email_notification = $this->input->get_var(
			'default_email_notification','post',PLIB_Input::INT_BOOL
		);
		$timezone = $this->input->get_var('timezone','post',PLIB_Input::STRING);
		$enable_pm_email = $this->input->get_var('enable_pm_email','post',PLIB_Input::INT_BOOL);
		$emails_include_post = $this->input->get_var('emails_include_post','post',PLIB_Input::INT_BOOL);
		$posts_order = $this->input->correct_var('posts_order','post',PLIB_Input::STRING,array('ASC','DESC'),'ASC');
		$email_notification_options = array('immediatly','1day','2days','1week');
		$email_notification_type = $this->input->correct_var(
			'email_notification_type','post',PLIB_Input::STRING,$email_notification_options,'immediatly'
		);
		$startmodule = $this->input->correct_var(
			'startmodule','post',PLIB_Input::STRING,array('portal','forums'),'portal'
		);

		$fields = array(
			'email_display_mode' => $email_display_mode,
			'default_font' => $default_font,
			'timezone' => $timezone,
			'enable_pm_email' => $enable_pm_email,
			'posts_order' => $posts_order,
			'startmodule' => $startmodule
		);

		// the language
		if($this->cfg['allow_custom_lang'] == 1)
		{
			$forum_lang = $this->input->get_var('lang','post',PLIB_Input::INTEGER);
			if($forum_lang == 0 || $this->cache->get_cache('languages')->element_exists_with(array('id' => $forum_lang)))
				$lang = $forum_lang;
			else
				$lang = $this->cfg['default_forum_lang'];

			// reload to change the language
			if($this->user->get_profile_val('forum_lang') != $lang)
				$info['reload'] = true;

			$fields['forum_lang'] = $lang;
			$this->user->set_profile_val('forum_lang',$lang);
		}

		// the theme
		if($this->cfg['allow_custom_style'] == 1)
		{
			$forum_style = $this->input->get_var('theme','post',PLIB_Input::INTEGER);
			if($forum_style == 0 || $this->cache->get_cache('themes')->element_exists_with(array('id' => $forum_style)))
				$style = $forum_style;
			else
				$style = $this->cfg['default_forum_style'];

			// reload to change the style
			if($this->user->get_profile_val('forum_style') != $style)
				$info['reload'] = true;

			$fields['forum_style'] = $style;
			$this->user->set_profile_val('forum_style',$style);
		}
		
		if(@timezone_open($timezone) === false)
			return 'Invalid timezone "'.$timezone.'"';

		// we have to reload the page if the timezone or daylightsaving has changed
		if($timezone != $this->user->get_profile_val('timezone'))
			$info['reload'] = true;

		if($this->cfg['allow_ghost_mode'] == 1)
			$fields['ghost_mode'] = $ghost_mode;

		if($this->cfg['enable_pms'] == 1)
			$fields['allow_pms'] = $allow_pms;

		if($this->cfg['enable_email_notification'] == 1)
		{
			$fields['default_email_notification'] = $default_email_notification;
			$fields['email_notification_type'] = $email_notification_type;
			$fields['emails_include_post'] = $emails_include_post;
		}

		if($this->cfg['enable_emails'] == 1)
			$fields['allow_board_emails'] = $allow_board_emails;

		if($this->cfg['enable_signatures'] == 1)
			$fields['attach_signature'] = $attach_signature;

		if($this->cfg['posts_enable_bbcode'] || $this->cfg['sig_enable_bbcode'] ||
			 $this->cfg['lnkdesc_enable_bbcode'])
			$fields['bbcode_mode'] = $bbcode_mode;

		BS_DAO::get_profile()->update_user_by_id($fields,$this->user->get_user_id());

		$this->user->set_profile_val('email_display_mode',$email_display_mode);
		if($this->cfg['enable_pms'] == 1)
			$this->user->set_profile_val('allow_pms',$allow_pms);
		$this->user->set_profile_val('default_font',$default_font);
		if($this->cfg['allow_ghost_mode'] == 1)
			$this->user->set_profile_val('ghost_mode',$ghost_mode);
		if($this->cfg['posts_enable_bbcode'] || $this->cfg['sig_enable_bbcode'] ||
			 $this->cfg['lnkdesc_enable_bbcode'])
			$this->user->set_profile_val('bbcode_mode',$bbcode_mode);
		if($this->cfg['enable_signatures'] == 1)
			$this->user->set_profile_val('attach_signature',$attach_signature);
		if($this->cfg['enable_emails'] == 1)
			$this->user->set_profile_val('allow_board_emails',$allow_board_emails);
		if($this->cfg['enable_email_notification'] == 1)
		{
			$this->user->set_profile_val('default_email_notification',$default_email_notification);
			$this->user->set_profile_val('email_notification_type',$email_notification_type);
			$this->user->set_profile_val('emails_include_post',$emails_include_post);
		}
		$this->user->set_profile_val('timezone',$timezone);
		$this->user->set_profile_val('enable_pm_email',$enable_pm_email);
		$this->user->set_profile_val('posts_order',$posts_order);
		$this->user->set_profile_val('startmodule',$startmodule);

		$this->set_action_performed(true);
		$this->add_link(
			$this->locale->lang('back'),$this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=config')
		);

		return '';
	}
}
?>