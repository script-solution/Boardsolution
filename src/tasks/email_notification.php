<?php
/**
 * Contains the email-notification-task
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The task which sends the delayed emails (daily, weekly, ...)
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_email_notification extends PLIB_Tasks_Base
{
	public function run()
	{
		// can we stop here?
		if($this->cfg['enable_email_notification'] == 0)
			return;

		$posts_to_user = array();
		$user_emails = array();
		
		// at first we collect all user who want to get notified
		$time = time();
		foreach(BS_DAO::get_unsentposts()->get_notification_list() as $row)
		{
			if(!isset($posts_to_user[$row['post_id']]))
				$posts_to_user[$row['post_id']] = array();
			$posts_to_user[$row['post_id']][] = $row['id'];
			
			if(!isset($user_emails[$row['id']]))
			{
				$user_emails[$row['id']] = array(
					'user_name' => $row['user_name'],
					'user_email' => $row['user_email'],
					'include_post' => $row['emails_include_post'],
					'language' => $row['forum_lang'] > 0 ? $row['forum_lang'] : $this->cfg['default_forum_lang'],
					'mail_text' => '',
					'last_topic' => -1
				);
			}
		}
		
		// now we have to fetch the required posts from the db
		$post_ids = array_keys($posts_to_user);
		if(is_array($post_ids) && count($post_ids) > 0)
		{
			foreach(BS_DAO::get_posts()->get_posts_for_email($post_ids) as $data)
			{
				foreach($posts_to_user[$data['id']] as $user_id)
				{
					$udata = &$user_emails[$user_id];

					$url = $this->url->get_frontend_url(
						'&'.BS_URL_ACTION.'=posts&'.BS_URL_FID.'='.$data['rubrikid']
							.'&'.BS_URL_TID.'='.$data['threadid'],
						'&',false
					);

					// include the post?
					if($udata['include_post'])
					{
						// we want to add the topic-name just once...
						if($data['threadid'] != $udata['last_topic'])
						{
							$udata['mail_text'] .= "\n".'=================================================='."\n";
							$udata['mail_text'] .= ':: '.$data['name']." :: \n";
							$udata['mail_text'] .= ':: '.$url." :: \n";
							$udata['mail_text'] .= '=================================================='."\n";
							$udata['mail_text'] .= "\n";
						}

						// add the post-text
						$udata['mail_text'] .= PLIB_StringHelper::htmlspecialchars_back($data['text_posted'])."\n";
						$udata['mail_text'] .= '--------------------------------------------------'."\n\n";
					}
					else if($data['threadid'] != $udata['last_topic'])
					{
						// just add the topic-URL
						$udata['mail_text'] .= $url."\n";
					}

					$udata['last_topic'] = $data['threadid'];
				}
			}

			// now we have to send the emails
			foreach($user_emails as $data)
			{
				$lang_entry = $this->_get_email_language_data($data['language']);

				// build text and title
				$text = sprintf(
					$lang_entry['delayed_email_notification_text'],
					$data['user_name'],
					$this->cfg['forum_title'],
					$data['mail_text']
				);
				$title = sprintf($lang_entry['delayed_email_notification_title'],$this->cfg['forum_title']);

				// send email
				$email = $this->functions->get_mailer($data['user_email'],$title,$text);
				$email->send_mail();
			}

			// finally we have to update the user-table so that this emails will not be sent again
			// and we have to store the current time
			if(count($user_emails) > 0)
			{
				$uids = array_keys($user_emails);
				BS_DAO::get_unsentposts()->delete_by_users($uids);
				BS_DAO::get_profile()->update_users_by_ids(array('last_email_notification' => $time),$uids);
			}
		}
	}

	/**
	 * retrieves the language-data for the given language and returns the required entries
	 *
	 * @param int $language the id of the language to use
	 * @return array an array with the two required entries
	 */
	private function _get_email_language_data($language)
	{
		static $languages = array();

		$data = $this->cache->get_cache('languages')->get_element($language);
		$lang_folder = $data['lang_folder'];

		if(!isset($languages[$lang_folder]))
		{
			$lang = $this->locale->get_language_entries('email',$lang_folder);
			$languages[$lang_folder] = array(
				'delayed_email_notification_text' => $lang['delayed_email_notification_text'],
				'delayed_email_notification_title' => $lang['delayed_email_notification_title']
			);
		}

		return $languages[$lang_folder];
	}
}
?>