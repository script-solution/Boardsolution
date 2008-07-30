<?php
/**
 * Contains the topics-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The topics submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_topics extends BS_Front_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_UNSUBSCRIBE_TOPIC,array('unsubscribe','topics'));

		$renderer->add_breadcrumb($locale->lang('threads'),$url->get_url(0,'&amp;'.BS_URL_LOC.'=topics'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$cfg = PLIB_Props::get()->cfg();
		$input = PLIB_Props::get()->input();
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$tpl = PLIB_Props::get()->tpl();
		$unread = PLIB_Props::get()->unread();
		$url = PLIB_Props::get()->url();

		// has the user the permission to view the subscriptions?
		if($cfg['enable_email_notification'] == 0)
		{
			$this->report_error(PLIB_Document_Messages::NO_ACCESS);
			return;
		}

		$site = $input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		if($site == null)
			$site = 1;

		// display delete-notice
		if(($delete = $input->get_var('delete','post')) != null &&
			PLIB_Array_Utils::is_integer($delete))
		{
			$subscr = BS_DAO::get_subscr()->get_subscr_topics_of_user($user->get_user_id(),$delete);
			$names = array();
			foreach($subscr as $data)
				$names[] = $data['name'];
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$loc = '&amp;'.BS_URL_LOC.'='.$input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
			$string_ids = implode(',',$delete);
			$yes_url = $url->get_url(
				0,
				$loc.'&amp;'.BS_URL_AT.'='.BS_ACTION_UNSUBSCRIBE_TOPIC
					.'&amp;'.BS_URL_DEL.'='.$string_ids.'&amp;'.BS_URL_SITE.'='.$site,'&amp;',true
			);
			$no_url = $url->get_url(0,$loc.'&amp;'.BS_URL_SITE.'='.$site);
			$target = $url->get_url(
				'redirect','&amp;'.BS_URL_LOC.'=del_subscr&amp;'.BS_URL_ID.'='.$string_ids
					.'&amp;'.BS_URL_SITE.'='.$site
			);

			$functions->add_delete_message(
				sprintf($locale->lang('delete_subscr_topics'),$namelist),
				$yes_url,$no_url,$target
			);
		}

		$end = BS_SUBSCR_TOPICS_PER_PAGE;
		$num = BS_DAO::get_subscr()->get_subscr_topics_count($user->get_user_id());
		$pagination = new BS_Pagination($end,$num);
		
		$tpl->add_variables(array(
			'target_url' => $url->get_url(0,'&amp;'.BS_URL_LOC.'=topics&amp;'.BS_URL_SITE.'='.$site),
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

		$topics = array();
		$sublist = BS_DAO::get_subscr()->get_subscr_topics_of_user(
			$user->get_user_id(),array(),$pagination->get_start(),$end
		);
		foreach($sublist as $data)
		{
			$info = BS_TopicUtils::get_instance()->get_displayed_name($data['name']);
			$topic_name = '<a title="'.$info['complete'].'"';
			$topic_name .= ' href="'.$url->get_url('posts','&amp;'.BS_URL_FID.'='.$data['rubrikid']
																										.'&amp;'.BS_URL_TID.'='.$data['topic_id']).'">';
			$topic_name .= $info['displayed'].'</a>';

			$lastpost = $data['lastpost_time'] > 0 ? PLIB_Date::get_date($data['lastpost_time']) : $locale->lang('notavailable');
			
			$topics[] = array(
				'topic_status' => BS_TopicUtils::get_instance()->get_status_data(
					$cache,$data,$unread->is_unread_thread($data['topic_id'])
				),
				'topic_symbol' => BS_TopicUtils::get_instance()->get_symbol(
					$cache,$data['type'],$data['symbol']
				),
				'subscribe_date' => PLIB_Date::get_date($data['sub_date']),
				'last_post' => $lastpost,
				'topic_name' => $topic_name,
				'topic_id' => $data['id'],
				'position' => BS_ForumUtils::get_instance()->get_forum_path($data['rubrikid'],false)
			);
		}

		$murl = $url->get_url(0,'&amp;'.BS_URL_LOC.'=topics&amp;'.BS_URL_SITE.'={d}');
		$functions->add_pagination($pagination,$murl);
		
		$tpl->add_array('topics',$topics);
	}
}
?>