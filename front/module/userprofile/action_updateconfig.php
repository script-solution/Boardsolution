<?php
/**
 * Contains the update-config-action
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * The update-config-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_updateconfig extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// the user has to be loggedin
		if(!$user->is_loggedin())
			return 'You are a guest';

		$fonts = explode(',',$cfg['post_font_pool']);
		FWS_Array_Utils::trim($fonts);

		$email_display_mode = $input->correct_var('email_display_mode','post',FWS_Input::STRING,
																							array('hide','jumble','default'),'default');
		$post_def_font = $input->get_var('default_font','post',FWS_Input::STRING);
		$default_font = ($post_def_font !== 0 && in_array($post_def_font,$fonts)) ? $post_def_font : 0;
		if($cfg['enable_pms'] == 1)
			$allow_pms = $input->get_var('allow_pms','post',FWS_Input::INT_BOOL);
		else
			$allow_pms = $user->get_profile_val('allow_pms');
		$ghost_mode = $input->get_var('ghost_mode','post',FWS_Input::INT_BOOL);
		$bbcode_mode = $input->correct_var(
			'bbcode_mode','post',FWS_Input::STRING,array('simple','advanced','applet'),'simple'
		);
		$attach_signature = $input->get_var('attach_signature','post',FWS_Input::INT_BOOL);
		$allow_board_emails = $input->get_var('allow_board_emails','post',FWS_Input::INT_BOOL);
		$default_email_notification = $input->get_var(
			'default_email_notification','post',FWS_Input::INT_BOOL
		);
		$timezone = $input->get_var('timezone','post',FWS_Input::STRING);
		$enable_pm_email = $input->get_var('enable_pm_email','post',FWS_Input::INT_BOOL);
		$emails_include_post = $input->get_var('emails_include_post','post',FWS_Input::INT_BOOL);
		$posts_order = $input->correct_var('posts_order','post',FWS_Input::STRING,array('ASC','DESC'),'ASC');
		$email_notification_options = array('immediatly','1day','2days','1week');
		$email_notification_type = $input->correct_var(
			'email_notification_type','post',FWS_Input::STRING,$email_notification_options,'immediatly'
		);
		$startmodule = $input->correct_var(
			'startmodule','post',FWS_Input::STRING,array('portal','forums'),'portal'
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
		if($cfg['allow_custom_lang'] == 1)
		{
			$forum_lang = $input->get_var('lang','post',FWS_Input::INTEGER);
			if($forum_lang == 0 || $cache->get_cache('languages')->element_exists_with(array('id' => $forum_lang)))
				$lang = $forum_lang;
			else
				$lang = $cfg['default_forum_lang'];

			$fields['forum_lang'] = $lang;
			$user->set_profile_val('forum_lang',$lang);
		}

		// the theme
		if($cfg['allow_custom_style'] == 1)
		{
			$forum_style = $input->get_var('theme','post',FWS_Input::INTEGER);
			if($forum_style == 0 || $cache->get_cache('themes')->element_exists_with(array('id' => $forum_style)))
				$style = $forum_style;
			else
				$style = $cfg['default_forum_style'];

			$fields['forum_style'] = $style;
			$user->set_profile_val('forum_style',$style);
		}
		
		if(@timezone_open($timezone) === false)
			return 'Invalid timezone "'.$timezone.'"';

		if($cfg['allow_ghost_mode'] == 1)
			$fields['ghost_mode'] = $ghost_mode;

		if($cfg['enable_pms'] == 1)
			$fields['allow_pms'] = $allow_pms;

		if($cfg['enable_email_notification'] == 1)
		{
			$fields['default_email_notification'] = $default_email_notification;
			$fields['email_notification_type'] = $email_notification_type;
			$fields['emails_include_post'] = $emails_include_post;
		}

		if($cfg['enable_emails'] == 1)
			$fields['allow_board_emails'] = $allow_board_emails;

		if($cfg['enable_signatures'] == 1)
			$fields['attach_signature'] = $attach_signature;

		if($cfg['posts_enable_bbcode'] || $cfg['sig_enable_bbcode'] ||
			 $cfg['desc_enable_bbcode'])
			$fields['bbcode_mode'] = $bbcode_mode;

		BS_DAO::get_profile()->update_user_by_id($fields,$user->get_user_id());

		$user->set_profile_val('email_display_mode',$email_display_mode);
		if($cfg['enable_pms'] == 1)
			$user->set_profile_val('allow_pms',$allow_pms);
		$user->set_profile_val('default_font',$default_font);
		if($cfg['allow_ghost_mode'] == 1)
			$user->set_profile_val('ghost_mode',$ghost_mode);
		if($cfg['posts_enable_bbcode'] || $cfg['sig_enable_bbcode'] ||
			 $cfg['desc_enable_bbcode'])
			$user->set_profile_val('bbcode_mode',$bbcode_mode);
		if($cfg['enable_signatures'] == 1)
			$user->set_profile_val('attach_signature',$attach_signature);
		if($cfg['enable_emails'] == 1)
			$user->set_profile_val('allow_board_emails',$allow_board_emails);
		if($cfg['enable_email_notification'] == 1)
		{
			$user->set_profile_val('default_email_notification',$default_email_notification);
			$user->set_profile_val('email_notification_type',$email_notification_type);
			$user->set_profile_val('emails_include_post',$emails_include_post);
		}
		$user->set_profile_val('timezone',$timezone);
		$user->set_profile_val('enable_pm_email',$enable_pm_email);
		$user->set_profile_val('posts_order',$posts_order);
		$user->set_profile_val('startmodule',$startmodule);

		$this->set_action_performed(true);
		$this->add_link(
			$locale->lang('back'),BS_URL::get_sub_url('userprofile','config')
		);

		return '';
	}
}
?>