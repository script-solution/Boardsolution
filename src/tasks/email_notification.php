<?php
/**
 * Contains the email-notification-task
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
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
 * The task which sends the delayed emails (daily, weekly, ...)
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_email_notification extends FWS_Tasks_Base
{
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		
		// can we stop here?
		if($cfg['enable_email_notification'] == 0)
			return;

		$posts_to_user = array();
		$user_emails = array();
		
		// at first we collect all user who want to get notified
		$time = time();
		foreach(BS_DAO::get_unsentposts()->get_notification_list() as $row)
		{
			$ugroups = FWS_Array_Utils::advanced_explode(",",$row['user_group']);
			$pdata = BS_DAO::get_posts()->get_post_by_id($row['post_id']);

			if($functions->has_access_to_intern_forum($row['id'],$ugroups,$pdata['rubrikid']))
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
						'language' => $row['forum_lang'] > 0 ? $row['forum_lang'] : $cfg['default_forum_lang'],
						'mail_text' => '',
						'last_topic' => -1,
						'topics' => array()
					);
				}
			}
		}
		
		// now we have to fetch the required posts from the db
		$post_ids = array_keys($posts_to_user);
		if(is_array($post_ids) && count($post_ids) > 0)
		{
			$murl = BS_URL::get_frontend_url('redirect','&',false);
			
			foreach(BS_DAO::get_posts()->get_posts_for_email($post_ids) as $data)
			{
				$murl->set(BS_URL_LOC,'show_post');
				$murl->set(BS_URL_ID,$data['id']);
				
				foreach($posts_to_user[$data['id']] as $user_id)
				{
					$udata = &$user_emails[$user_id];

					// include the post?
					if($udata['include_post'])
					{
						// we want to add the topic-name just once...
						if($data['threadid'] != $udata['last_topic'])
						{
							$udata['topics'][] = array(
								'include_post' => true,
								'name' => $data['name'],
								'url' => $murl->to_url(),
								'posts' => array()
							);
						}

						// add the post-text
						$text = FWS_StringHelper::htmlspecialchars_back($data['text_posted']);
						$udata['topics'][count($udata['topics']) - 1]['posts'][] = array(
							'text' => $text,
							'user_name' => $data['user_name'] ? $data['user_name'] : $data['post_an_user']
						);
					}
					else if($data['threadid'] != $udata['last_topic'])
					{
						// just add the topic-URL
						$udata['topics'][] = array(
							'include_post' => false,
							'url' => $murl->to_url()
						);
					}

					$udata['last_topic'] = $data['threadid'];
				}
			}

			// now we have to send the emails
			foreach($user_emails as $data)
			{
				if(count($data['topics']) > 0)
				{
					$lang_entry = $this->_get_email_language_data($data['language']);
					$email = BS_EmailFactory::get_instance()->get_delayed_email_notification_mail(
						$lang_entry['delayed_email_notification_title'],
						$lang_entry['delayed_email_notification_text'],
						$data['user_name'],
						$data['user_email'],
						$data['topics']
					);
					$email->send_mail();
				}
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
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		static $languages = array();

		$data = $cache->get_cache('languages')->get_element($language);
		$lang_folder = $data['lang_folder'];

		if(!isset($languages[$lang_folder]))
		{
			$lang = $locale->get_language_entries('email',$lang_folder);
			$languages[$lang_folder] = array(
				'delayed_email_notification_text' => $lang['delayed_email_notification_text'],
				'delayed_email_notification_title' => $lang['delayed_email_notification_title']
			);
		}

		return $languages[$lang_folder];
	}
}
?>