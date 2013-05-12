<?php
/**
 * Contains the forums-userprofile-submodule
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
 * The forums submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_forums extends BS_Front_SubModule
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
		
		$renderer->add_action(BS_ACTION_UNSUBSCRIBE_FORUM,array('unsubscribe','forums'));
		$renderer->add_action(BS_ACTION_SUBSCRIBE_ALL,'subscribeall');

		$renderer->add_breadcrumb($locale->lang('forums'),BS_URL::build_sub_url());
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
		$forums = FWS_Props::get()->forums();

		// has the user the permission to view the subscriptions?
		if($cfg['enable_email_notification'] == 0)
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}

		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
		if($site == null)
			$site = 1;

		// display delete info
		if(($delete = $input->get_var('delete','post')) != null &&
			FWS_Array_Utils::is_integer($delete))
		{
			$subscr = BS_DAO::get_subscr()->get_subscr_forums_of_user($user->get_user_id(),$delete);
			$names = array();
			foreach($subscr as $data)
			{
				$forum = $forums->get_node($data['forum_id']);
				if($forum !== false)
					$names[] = $forum->get_name();
			}
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			$string_ids = implode(',',$delete);
			
			$url = BS_URL::get_sub_url();
			$url->set(BS_URL_SITE,$site);
			
			$no_url = $url->to_url();
			
			$url->set(BS_URL_AT,BS_ACTION_UNSUBSCRIBE_FORUM);
			$url->set(BS_URL_DEL,$string_ids);
			$url->set_sid_policy(BS_URL::SID_FORCE);
			$yes_url = $url->to_url();
			
			$target = BS_URL::get_mod_url('redirect');
			$target->set(BS_URL_LOC,'del_subscr');
			$target->set(BS_URL_ID,$string_ids);
			$target->set(BS_URL_SITE,$site);

			$functions->add_delete_message(
				sprintf($locale->lang('delete_subscr_forums'),$namelist),
				$yes_url,$no_url,$target->to_url()
			);
		}

		// collect the forum-ids
		$forum_ids = array();
		$sub_data = array();
		foreach(BS_DAO::get_subscr()->get_subscr_forums_of_user($user->get_user_id()) as $data)
		{
			if($functions->has_access_to_intern_forum($user->get_user_id(),$user->get_all_user_groups(),$data['forum_id']))
			{
				$forum_ids[$data['forum_id']] = true;
				$sub_data[$data['forum_id']] = $data;
			}
		}

		$end = BS_SUBSCR_FORUMS_PER_PAGE;
		$num = count($forum_ids);
		$pagination = new BS_Pagination($end,$num);
		
		$url = BS_URL::get_sub_url();
		$url->set(BS_URL_SITE,$site);
		$tpl->add_variables(array(
			'target_url' => $url->to_url(),
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
					$lastpost = FWS_Date::get_date($data->get_lastpost_time());
				else
					$lastpost = $locale->lang('notavailable');
				$tplforums[] = array(
					'subscribe_date' => FWS_Date::get_date($sub_data[$data->get_id()]['sub_date']),
					'last_post' => $lastpost,
					'id' => $sub_data[$data->get_id()]['id'],
					'position' => BS_ForumUtils::get_forum_path($data->get_id(),false)
				);
			}
		}
		
		$tpl->add_variable_ref('forums',$tplforums);

		$pagination->populate_tpl(BS_URL::get_sub_url());

		$url->set(BS_URL_AT,BS_ACTION_SUBSCRIBE_ALL);
		$url->set_sid_policy(BS_URL::SID_FORCE);
		$tpl->add_variables(array(
			'subscribe_all_url' => $url->to_url()
		));
	}
}
?>