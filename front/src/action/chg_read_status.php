<?php
/**
 * Contains the chg_read_status-action
 * 
 * @package			Boardsolution
 * @subpackage	front.src.action
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
 * The chg_read_status-action
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_chg_read_status extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$unread = FWS_Props::get()->unread();
		$locale = FWS_Props::get()->locale();
		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		$read = $input->correct_var(
			BS_URL_LOC,'get',FWS_Input::STRING,array('read','unread'),'read'
		);
		$mode = $input->correct_var(
			BS_URL_MODE,'get',FWS_Input::STRING,array('topics','forum','all'),'topics'
		);
		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::ID);

		switch($mode)
		{
			case 'topics':
				$id_str = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
				if(!($ids = FWS_StringHelper::get_ids($id_str)))
					return 'Invalid id-string got via GET';

				if($read == 'read')
					$unread->mark_topics_read($ids);
				else
					$unread->mark_topics_unread($ids);

				$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
				if($fid != null)
					$this->add_link($locale->lang('back'),BS_URL::get_topics_url($fid,$site));
				else
					$this->add_link($locale->lang('back'),BS_URL::get_mod_url('unread'));
				break;

			case 'forum':
				$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
				if($fid == null)
					return 'Invalid forum-id "'.$fid.'"';

				$unread->mark_forum_read($fid);

				if($input->get_var(BS_URL_ACTION,'get',FWS_Input::STRING) == 'topics')
					$this->add_link($locale->lang('back'),BS_URL::get_topics_url($fid,$site));
				else
					$this->add_link($locale->lang('back'),BS_URL::get_forums_url());
				break;

			case 'all':
				$unread->mark_all_read();

				$this->add_link($locale->lang('forumindex'),BS_URL::get_forums_url());
				break;
		}

		$this->set_success_msg($locale->lang('success_'.BS_ACTION_CHANGE_READ_STATUS.'_'.$read));
		$this->set_action_performed(true);

		return '';
	}
}
?>