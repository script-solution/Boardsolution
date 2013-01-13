<?php
/**
 * Contains the topics-userprofile-submodule
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
 * The topics submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_topics extends BS_Front_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_UNSUBSCRIBE_TOPIC,array('unsubscribe','topics'));

		$renderer->add_breadcrumb($locale->lang('threads'),BS_URL::build_sub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();
		$unread = FWS_Props::get()->unread();
		// has the user the permission to view the subscriptions?
		if($cfg['enable_email_notification'] == 0)
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}

		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
		if($site == null)
			$site = 1;

		// display delete-notice
		if(($delete = $input->get_var('delete','post')) != null &&
			FWS_Array_Utils::is_integer($delete))
		{
			$subscr = BS_DAO::get_subscr()->get_subscr_topics_of_user($user->get_user_id(),$delete);
			$names = array();
			foreach($subscr as $data)
				$names[] = $data['name'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			$string_ids = implode(',',$delete);
			
			$url = BS_URL::get_sub_url();
			$url->set(BS_URL_SITE,$site);
			$no_url = $url->to_url();
			
			$url->set(BS_URL_AT,BS_ACTION_UNSUBSCRIBE_TOPIC);
			$url->set(BS_URL_DEL,$string_ids);
			$url->set_sid_policy(BS_URL::SID_FORCE);
			$yes_url = $url->to_url();
			
			$url = BS_URL::get_mod_url('redirect');
			$url->set(BS_URL_LOC,'del_subscr');
			$url->set(BS_URL_ID,$string_ids);
			$url->set(BS_URL_SITE,$site);
			$target = $url->to_url();

			$functions->add_delete_message(
				sprintf($locale->lang('delete_subscr_topics'),$namelist),
				$yes_url,$no_url,$target
			);
		}

		$end = BS_SUBSCR_TOPICS_PER_PAGE;
		$num = BS_DAO::get_subscr()->get_subscr_topics_count($user->get_user_id());
		$pagination = new BS_Pagination($end,$num);
		
		$url = BS_URL::get_sub_url();
		$url->set(BS_URL_SITE,$site);
		
		$tpl->add_variables(array(
			'target_url' => $url->to_url(),
			'action_type' => BS_ACTION_UNSUBSCRIBE_TOPIC,
			'num' => $num
		));

		// TODO create a method for this (we need it more than once, right?)
		$cache = array(
			'symbol_poll' =>				$user->get_theme_item_path('images/thread_type/poll.gif'),
			'symbol_event' =>				$user->get_theme_item_path('images/thread_type/event.gif'),

			'important_en' =>				$user->get_theme_item_path('images/thread_status/important_en.gif'),
			'important_dis' =>			$user->get_theme_item_path('images/thread_status/important_dis.gif'),
			'important_new_en' =>		$user->get_theme_item_path('images/thread_status/important_new_en.gif'),
			'important_new_dis' =>	$user->get_theme_item_path('images/thread_status/important_new_dis.gif'),

			'hot_en' =>							$user->get_theme_item_path('images/thread_status/hot_en.gif'),
			'hot_dis' =>						$user->get_theme_item_path('images/thread_status/hot_dis.gif'),
			'hot_new_en' =>					$user->get_theme_item_path('images/thread_status/hot_new_en.gif'),
			'hot_new_dis' =>				$user->get_theme_item_path('images/thread_status/hot_new_dis.gif'),

			'closed_en' =>					$user->get_theme_item_path('images/thread_status/closed_en.gif'),
			'closed_dis' =>					$user->get_theme_item_path('images/thread_status/closed_dis.gif'),
			'closed_new_en' =>			$user->get_theme_item_path('images/thread_status/closed_new_en.gif'),
			'closed_new_dis' =>			$user->get_theme_item_path('images/thread_status/closed_new_dis.gif'),

			'moved_en' =>						$user->get_theme_item_path('images/thread_status/moved_en.gif'),
			'moved_dis' =>					$user->get_theme_item_path('images/thread_status/moved_dis.gif'),
			'moved_new_en' =>				$user->get_theme_item_path('images/thread_status/moved_new_en.gif'),
			'moved_new_dis' =>			$user->get_theme_item_path('images/thread_status/moved_new_dis.gif')
		);

		$purl = BS_URL::get_mod_url('posts');
		
		$topics = array();
		$sublist = BS_DAO::get_subscr()->get_subscr_topics_of_user(
			$user->get_user_id(),array(),$pagination->get_start(),$end
		);
		foreach($sublist as $data)
		{
			$purl->set(BS_URL_FID,$data['rubrikid']);
			$purl->set(BS_URL_TID,$data['topic_id']);
			
			list($infod,$infoc) = BS_TopicUtils::get_displayed_name($data['name']);
			$topic_name = '<a title="'.$infoc.'" href="'.$purl->to_url().'">'.$infod.'</a>';

			$lastpost = $data['lastpost_time'] > 0 ?
				FWS_Date::get_date($data['lastpost_time']) : $locale->lang('notavailable');
			
			$topics[] = array(
				'topic_status' => BS_TopicUtils::get_status_data(
					$cache,$data,$unread->is_unread_thread($data['topic_id'])
				),
				'topic_symbol' => BS_TopicUtils::get_symbol(
					$cache,$data['type'],$data['symbol']
				),
				'subscribe_date' => FWS_Date::get_date($data['sub_date']),
				'last_post' => $lastpost,
				'topic_name' => $topic_name,
				'topic_id' => $data['id'],
				'position' => BS_ForumUtils::get_forum_path($data['rubrikid'],false)
			);
		}

		$pagination->populate_tpl(BS_URL::get_sub_url());
		
		$tpl->add_variable_ref('topics',$topics);
	}
}
?>