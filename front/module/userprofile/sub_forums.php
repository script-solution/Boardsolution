<?php
/**
 * Contains the forums-userprofile-submodule
 * 
 * @version			$Id: sub_forums.php 765 2008-05-24 21:14:51Z nasmussen $
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
	public function get_actions()
	{
		return array(
			BS_ACTION_UNSUBSCRIBE_FORUM => array('unsubscribe','forums'),
			BS_ACTION_SUBSCRIBE_ALL => 'subscribeall'
		);
	}
	
	public function run()
	{
		// has the user the permission to view the subscriptions?
		if($this->cfg['enable_email_notification'] == 0)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}

		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		if($site == null)
			$site = 1;

		// display delete info
		if(($delete = $this->input->get_var('delete','post')) != null &&
			PLIB_Array_Utils::is_integer($delete))
		{
			$subscr = BS_DAO::get_subscr()->get_subscr_forums_of_user($this->user->get_user_id(),$delete);
			$names = array();
			foreach($subscr as $data)
			{
				$forum = $this->forums->get_node($data['forum_id']);
				if($forum !== false)
					$names[] = $forum->get_name();
			}
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$loc = '&amp;'.BS_URL_LOC.'='.$this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
			$string_ids = implode(',',$delete);
			$yes_url = $this->url->get_url(
				0,
				$loc.'&amp;'.BS_URL_AT.'='.BS_ACTION_UNSUBSCRIBE_FORUM
					.'&amp;'.BS_URL_DEL.'='.$string_ids.'&amp;'.BS_URL_SITE.'='.$site,'&amp;',true
			);
			$no_url = $this->url->get_url(0,$loc.'&amp;'.BS_URL_SITE.'='.$site);
			$target = $this->url->get_url(
				'redirect',
				'&amp;'.BS_URL_LOC.'=del_subscr&amp;'.BS_URL_ID.'='.$string_ids
					.'&amp;'.BS_URL_SITE.'='.$site
			);

			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_subscr_forums'),$namelist),
				$yes_url,$no_url,$target
			);
		}

		// collect the forum-ids
		$forum_ids = array();
		$sub_data = array();
		foreach(BS_DAO::get_subscr()->get_subscr_forums_of_user($this->user->get_user_id()) as $data)
		{
			$forum_ids[$data['forum_id']] = true;
			$sub_data[$data['forum_id']] = $data;
		}

		$end = BS_SUBSCR_FORUMS_PER_PAGE;
		$num = count($forum_ids);
		$pagination = new BS_Pagination($end,$num);
		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=forums&amp;'.BS_URL_SITE.'='.$site),
			'action_type' => BS_ACTION_UNSUBSCRIBE_FORUM,
			'num' => $num
		));

		$start = $pagination->get_start();
		$tplforums = array();
		$forums = $this->forums->get_nodes_with_ids(array_keys($forum_ids));
		for($index = 0;$index < $num;$index++)
		{
			if($index >= $start && $index < $start + $end)
			{
				$data = $forums[$index]->get_data();
				if($data->get_lastpost_time() > 0)
					$lastpost = PLIB_Date::get_date($data->get_lastpost_time());
				else
					$lastpost = $this->locale->lang('notavailable');
				$tplforums[] = array(
					'subscribe_date' => PLIB_Date::get_date($sub_data[$data->get_id()]['sub_date']),
					'last_post' => $lastpost,
					'id' => $sub_data[$data->get_id()]['id'],
					'position' => BS_ForumUtils::get_instance()->get_forum_path($data->get_id(),false)
				);
			}
		}
		
		$this->tpl->add_array('forums',$tplforums,false);

		$url = $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=forums&amp;'.BS_URL_SITE.'={d}');
		$this->functions->add_pagination($pagination,$url);

		$url = $this->url->get_url(
			0,'&amp;'.BS_URL_LOC.'=forums&amp;'.BS_URL_SITE.'='.$site
				.'&amp;'.BS_URL_AT.'='.BS_ACTION_SUBSCRIBE_ALL,'&amp;',true
		);
		$this->tpl->add_variables(array(
			'subscribe_all_url' => $url
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('forums') => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=forums')
		);
	}
}
?>