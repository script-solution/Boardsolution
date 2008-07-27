<?php
/**
 * Contains the email-factory
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Contains methods that build instances of {@link PLIB_Email_Base} for each email-text
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_EmailFactory extends PLIB_Singleton
{
	/**
	 * @return BS_EmailFactory the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 * You'll have to set the receivers by yourself.
	 *
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_new_link_mail()
	{
		$locale = PLIB_Props::get()->locale();
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();
		$functions = PLIB_Props::get()->functions();

		$locale->add_language_file('email');
		
		$title = sprintf($locale->lang('link_email_title'),$cfg['forum_title']);
		$text = $tpl->parse_string(
			$locale->lang('link_email_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'board_url' => $cfg['board_url']
			)
		);
		
		return $functions->get_mailer('',$title,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 * Note that the default-language will be used because the account has just been created and
	 * therefore the default-language is set.
	 *
	 * @param string $user_name the user-name
	 * @param string $user_email the receiver-email-address
	 * @param string $user_pw the password of the user
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_new_registration_mail($user_name,$user_email,$user_pw)
	{
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();

		$locale->add_language_file('email',$functions->get_def_lang_folder());
		
		$title = sprintf(
			$locale->lang('new_registration_email_title'),
			$cfg['forum_title']
		);
		$text = $tpl->parse_string(
			$locale->lang('new_registration_email_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'user_name' => $user_name,
				'user_pw' => $user_pw
			)
		);
		
		return $functions->get_mailer($user_email,$title,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 * It will be used the language with given id.
	 *
	 * @param int $langid the language-id to use for the email
	 * @param string $oldname the old user-name
	 * @param string $newname the new user-name
	 * @param string $oldemail the old email-address
	 * @param string $newemail the new email-address
	 * @param string $password if changed, the password
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_account_changed_mail($langid,$oldname,$newname,$oldemail,$newemail,$password)
	{
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();

		if(!PLIB_Helper::is_integer($langid) || $langid <= 0)
			PLIB_Helper::def_error('intgt0','langid',$langid);
		
		$lang_data = $cache->get_cache('languages')->get_element($langid);
		if($lang_data != null)
			$locale->add_language_file('email',$lang_data['lang_folder']);
		else
			$locale->add_language_file('email',$functions->get_def_lang_folder());

		$title = sprintf(
			$locale->lang('userdata_changed_email_title'),
			$cfg['forum_title']
		);
		$text = $tpl->parse_string(
			$locale->lang('userdata_changed_email_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'name_changed' => $oldname != $newname,
				'user_name' => $newname,
				'email_changed' => $oldemail != $newemail,
				'user_email' => $newemail,
				'pw_changed' => $password != '',
				'user_pw' => $password
			)
		);
		
		return $functions->get_mailer($oldemail,$title,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 *
	 * @param int $user_id the user-id
	 * @param string $user_email the email of the user
	 * @param string $user_key the key for the email-change
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_change_email_mail($user_id,$user_email,$user_key)
	{
		$locale = PLIB_Props::get()->locale();
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();

		$locale->add_language_file('email');
		
		$subject = sprintf($locale->lang('change_email_email_title'),$cfg['forum_title']);
		$text = $tpl->parse_string(
			$locale->lang('change_email_email_text'),
			array(
				'url' => $url->get_url(
					'conf_email','&'.BS_URL_ID.'='.$user_id.'&'.BS_URL_PID.'='.$user_key,'&',true
				),
				'email' => $user_email
			)
		);
		
		return $functions->get_mailer($user_email,$subject,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 *
	 * @param int $user_id the user-id
	 * @param string $user_email the email of the user
	 * @param string $user_key the key for the password-change
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_change_pw_mail($user_id,$user_email,$user_key)
	{
		$locale = PLIB_Props::get()->locale();
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();

		$locale->add_language_file('email');

		$subject = sprintf($locale->lang('pw_change_title'),$cfg['forum_title']);
		$text = $tpl->parse_string(
			$locale->lang('pw_change_text'),
			array(
				'url' => $url->get_frontend_url(
					'&'.BS_URL_ACTION.'=change_password&'.BS_URL_ID.'='.$user_id.'&'.BS_URL_KW.'='.$user_key,
					'&',false
				)
			)
		);
		
		return $functions->get_mailer($user_email,$subject,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 *
	 * @param int $user_id the id of the user
	 * @param string $user_email the email-address
	 * @param string $user_key the activation-key of the user
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_account_activation_mail($user_id,$user_email,$user_key)
	{
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();

		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		$locale->add_language_file('email');

		// send the email
		$subject = $locale->lang('account_activation_email_title');
		$text = $tpl->parse_string(
			$locale->lang('account_activation_email_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'url' => $url->get_url(
					'activate','&'.BS_URL_ID.'='.$user_id.'&'.BS_URL_KW.'='.$user_key,'&',true
				)
			)
		);
		
		return $functions->get_mailer($user_email,$subject,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 * You'll have to set the receivers by yourself. Note that the default language will be used.
	 * 
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_account_activated_mail()
	{
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();

		$locale->add_language_file('email');
		
		$subject = $locale->lang('account_activated_title');
		$text = $tpl->parse_string(
			$locale->lang('account_activated_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'board_url' => $url->get_frontend_url('','&',false)
			)
		);
		
		return $functions->get_mailer('',$subject,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 * You'll have to set the receivers by yourself. Note that the default language will be used.
	 * 
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_account_not_activated_mail()
	{
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();

		$locale->add_language_file('email');
		
		$subject = $locale->lang('account_not_activated_title');
		$text = $tpl->parse_string(
			$locale->lang('account_not_activated_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'board_url' => $url->get_frontend_url('','&',false)
			)
		);
		
		return $functions->get_mailer('',$subject,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 * You'll have to set the receivers by yourself. Note that the default language will be used.
	 * 
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_account_reactivated_mail()
	{
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$url = PLIB_Props::get()->url();

		$locale->add_language_file('email',$functions->get_def_lang_folder());
		
		$subject = $locale->lang('account_reactivated_title');
		$text = $tpl->parse_string(
			$locale->lang('account_reactivated_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'board_url' => $url->get_frontend_url('','&',false)
			)
		);
		
		return $functions->get_mailer('',$subject,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 * You'll have to set the receivers by yourself.
	 * 
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_account_deactivated_mail()
	{
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();

		$locale->add_language_file('email');
		
		$subject = $locale->lang('account_deactivated_title');
		$text = $tpl->parse_string(
			$locale->lang('account_deactivated_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'board_url' => $url->get_frontend_url('','&',false)
			)
		);
		
		return $functions->get_mailer('',$subject,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 *
	 * @param string $email the email-address
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_new_pm_mail($email)
	{
		$locale = PLIB_Props::get()->locale();
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();

		$locale->add_language_file('email');
		
		$subject = sprintf($locale->lang('new_pm_email_title'),$cfg['forum_title']);
		$text = $tpl->parse_string(
			$locale->lang('new_pm_email_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'board_url' => $url->get_frontend_url('','&',false)
			)
		);
		
		return $functions->get_mailer($email,$subject,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 *
	 * @param int $user_id the user-id
	 * @param string $user_name the user-name
	 * @param string $user_email the email-address
	 * @param string $user_pw the password
	 * @param string $user_key the key for the activation
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_register_mail($user_id,$user_name,$user_email,$user_pw,$user_key = '')
	{
		$locale = PLIB_Props::get()->locale();
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();
		$functions = PLIB_Props::get()->functions();
		$url = PLIB_Props::get()->url();

		$locale->add_language_file('email');

		$murl = '';
		if($cfg['account_activation'] == 'email')
			$murl = $url->get_url('activate','&user_id='.$user_id.'&user_key='.$user_key,'&',true);
		
		$subject = sprintf(
			$locale->lang('account_registration_email_title'),$cfg['forum_title']
		);
		$text = $tpl->parse_string(
			$locale->lang('account_registration_email_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'user_name' => $user_name,
				'user_pw' => $user_pw,
				'type' => $cfg['account_activation'],
				'url' => $murl
			)
		);
		
		return $functions->get_mailer($user_email,$subject,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 *
	 * @param string $user_name the username that has created an account
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_new_account_mail($user_name)
	{
		$locale = PLIB_Props::get()->locale();
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();

		$locale->add_language_file('email');

		$subject = sprintf(
			$locale->lang('newaccount_email_title'),
			$cfg['forum_title'],
			$user_name
		);
		$text = $tpl->parse_string(
			$locale->lang('newaccount_email_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'board_url' => $url->get_frontend_url('','&',false)
			)
		);
		
		return $functions->get_mailer('',$subject,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 *
	 * @param string $subject the subject-template
	 * @param string $text the text-template
	 * @param string $user_name the name of the user who gets the email
	 * @param string $user_email the receiver-address
	 * @param array $topics all topics to add
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_delayed_email_notification_mail($subject,$text,$user_name,$user_email,$topics)
	{
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();
		$functions = PLIB_Props::get()->functions();

		$subject = sprintf($subject,$cfg['forum_title']);
		$text = $tpl->parse_string(
			$text,
			array(
				'forum_name' => $cfg['forum_title'],
				'user_name' => $user_name,
				'topics' => $topics
			)
		);
		
		return $functions->get_mailer($user_email,$subject,$text);
	}
	
	/**
	 * Builds the instance of {@link PLIB_Email_Base} with the corresponding subject and text.
	 *
	 * @param int $current the current number of PMs in the inbox
	 * @param string $email the email-address to send the email to
	 * @return PLIB_Email_Base the email-instance
	 */
	public function get_pm_inbox_full_mail($current,$email)
	{
		$locale = PLIB_Props::get()->locale();
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();

		$locale->add_language_file('email');
		
		$subject = sprintf($locale->lang('pm_inbox_full_email_title'),$cfg['forum_title']);
		$text = $tpl->parse_string(
			$locale->lang('pm_inbox_full_email_text'),
			array(
				'percent' => round(100 / ($cfg['pm_max_inbox'] / $current),1),
				'current' => $current,
				'max' => $cfg['pm_max_inbox'],
				'board_url' => $url->get_frontend_url('','&',false)
			)
		);
		
		return $functions->get_mailer($email,$subject,$text);
	}
	
	/**
	 * Builds the subject, the text including and without the post.
	 *
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @param int $pid the post-id
	 * @param string $text the text
	 * @param string $user_name the user who has posted the entry
	 * @return array an array with:
	 * 	<code>array(
	 * 		'text_post' => ...,
	 * 		'text_def' => ...,
	 * 		'subject' => ...
	 * 	)
	 * 	</code>
	 */
	public function get_new_post_texts($fid,$tid,$pid,$text,$user_name)
	{
		$locale = PLIB_Props::get()->locale();
		$cfg = PLIB_Props::get()->cfg();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		$locale->add_language_file('email');
		
		$murl = $url->get_frontend_url(
			'&'.BS_URL_ACTION.'=posts&'.BS_URL_FID.'='.$fid
				.'&'.BS_URL_TID.'='.$tid,'&',false
		);
		if(BS_PostingUtils::get_instance()->get_posts_order() == 'ASC')
		{
			$post_num = BS_DAO::get_posts()->get_count_in_topic($tid);
			if($post_num > $cfg['posts_per_page'])
			{
				$pagination = new BS_Pagination($cfg['posts_per_page'],$post_num);
				$murl .= '&'.BS_URL_SITE.'='.$pagination->get_page_count();
			}
		}
		$murl .= '#b_'.$pid;
		
		$subject = sprintf($locale->lang('new_entry_title'),$cfg['forum_title']);
		$text_def = $tpl->parse_string(
			$locale->lang('new_entry_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'board_url' => $murl,
				'text' => '',
				'user_name' => ''
			)
		);
		$text_post = $tpl->parse_string(
			$locale->lang('new_entry_text'),
			array(
				'forum_name' => $cfg['forum_title'],
				'board_url' => $murl,
				'text' => PLIB_StringHelper::htmlspecialchars_back($text),
				'user_name' => $user_name
			)
		);
		
		return array(
			'subject' => $subject,
			'text_def' => $text_def,
			'text_post' => $text_post
		);
	}
}
?>