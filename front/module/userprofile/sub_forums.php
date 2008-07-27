<?php
/**
 * Contains the forums-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The forums submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_forums extends BS_Front_SubModule
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
		
		$doc->add_action(BS_ACTION_UNSUBSCRIBE_FORUM,array('unsubscribe','forums'));
		$doc->add_action(BS_ACTION_SUBSCRIBE_ALL,'subscribeall');

		$doc->add_breadcrumb($locale->lang('forums'),$url->get_url(0,'&amp;'.BS_URL_LOC.'=forums'));
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
		$url = PLIB_Props::get()->url();
		$forums = PLIB_Props::get()->forums();

		// has the user the permission to view the subscriptions?
		if($cfg['enable_email_notification'] == 0)
		{
			$this->report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}

		$site = $input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		if($site == null)
			$site = 1;

		// display delete info
		if(($delete = $input->get_var('delete','post')) != null &&
			PLIB_Array_Utils::is_integer($delete))
		{
			$subscr = BS_DAO::get_subscr()->get_subscr_forums_of_user($user->get_user_id(),$delete);
			$names = array();
			foreach($subscr as $data)
			{
				$forum = $forums->get_node($data['forum_id']);
				if($forum !== false)
					$names[] = $forum->get_name();
			}
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$loc = '&amp;'.BS_URL_LOC.'='.$input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
			$string_ids = implode(',',$delete);
			$yes_url = $url->get_url(
				0,
				$loc.'&amp;'.BS_URL_AT.'='.BS_ACTION_UNSUBSCRIBE_FORUM
					.'&amp;'.BS_URL_DEL.'='.$string_ids.'&amp;'.BS_URL_SITE.'='.$site,'&amp;',true
			);
			$no_url = $url->get_url(0,$loc.'&amp;'.BS_URL_SITE.'='.$site);
			$target = $url->get_url(
				'redirect',
				'&amp;'.BS_URL_LOC.'=del_subscr&amp;'.BS_URL_ID.'='.$string_ids
					.'&amp;'.BS_URL_SITE.'='.$site
			);

			$functions->add_delete_message(
				sprintf($locale->lang('delete_subscr_forums'),$namelist),
				$yes_url,$no_url,$target
			);
		}

		// collect the forum-ids
		$forum_ids = array();
		$sub_data = array();
		foreach(BS_DAO::get_subscr()->get_subscr_forums_of_user($user->get_user_id()) as $data)
		{
			$forum_ids[$data['forum_id']] = true;
			$sub_data[$data['forum_id']] = $data;
		}

		$end = BS_SUBSCR_FORUMS_PER_PAGE;
		$num = count($forum_ids);
		$pagination = new BS_Pagination($end,$num);
		$tpl->add_variables(array(
			'target_url' => $url->get_url(0,'&amp;'.BS_URL_LOC.'=forums&amp;'.BS_URL_SITE.'='.$site),
			'action_type' => BS_ACTION_UNSUBSCRIBE_FORUM,
			'num' => $num
		));

		$start = $pagination->get_start();
		$tplforums = array();
		$nodes = $forums->get_nodes_with_ids(array_keys($forum_ids));
		for($index = 0;$index < $num;$index++)
		{
			if($index >= $start && $index < $start + $end)
			{
				$data = $nodes[$index]->get_data();
				if($data->get_lastpost_time() > 0)
					$lastpost = PLIB_Date::get_date($data->get_lastpost_time());
				else
					$lastpost = $locale->lang('notavailable');
				$tplforums[] = array(
					'subscribe_date' => PLIB_Date::get_date($sub_data[$data->get_id()]['sub_date']),
					'last_post' => $lastpost,
					'id' => $sub_data[$data->get_id()]['id'],
					'position' => BS_ForumUtils::get_instance()->get_forum_path($data->get_id(),false)
				);
			}
		}
		
		$tpl->add_array('forums',$tplforums,false);

		$murl = $url->get_url(0,'&amp;'.BS_URL_LOC.'=forums&amp;'.BS_URL_SITE.'={d}');
		$functions->add_pagination($pagination,$murl);

		$murl = $url->get_url(
			0,'&amp;'.BS_URL_LOC.'=forums&amp;'.BS_URL_SITE.'='.$site
				.'&amp;'.BS_URL_AT.'='.BS_ACTION_SUBSCRIBE_ALL,'&amp;',true
		);
		$tpl->add_variables(array(
			'subscribe_all_url' => $murl
		));
	}
}
?>