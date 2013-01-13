<?php
/**
 * Contains the delete-pms-action
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
 * The delete-pms-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_deletepms extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		$locale = FWS_Props::get()->locale();
		$del = $input->get_var(BS_URL_DEL,"get",FWS_Input::STRING);
		if(!$user->is_loggedin() || $cfg['enable_pms'] == 0 || $del == null ||
				$user->get_profile_val('allow_pms') == 0)
			return 'You are a guest, PMs are disabled, no ids have been given or you\'ve disabled PMs';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		if(!($ids = FWS_StringHelper::get_ids($del)))
			return 'Invalid id-string got via GET';

		// collect attachments
		$me = $user->get_user_id();
		$ownids = array();
		foreach(BS_DAO::get_attachments()->get_by_pmids($ids) as $data)
		{
			if($data['poster_id'] == $me)
			{
				$ownids[] = $data['id'];
				// PM-attachments are used for 2 PMs: the PM of the sender and the PM of the receiver
				// Therefore we delete the attachment-file as soon as both PMs have been deleted
				if(BS_DAO::get_attachments()->get_attachment_count_of_path($data['attachment_path']) == 1)
					$functions->delete_attachment($data['attachment_path']);
			}
		}

		if(count($ownids) > 0)
			BS_DAO::get_attachments()->delete_by_pmids($ownids);

		BS_DAO::get_pms()->delete_pms_of_user($ids,$user->get_user_id());

		// finish
		$this->set_action_performed(true);
		$loc = $input->get_var(BS_URL_SUB,'get',FWS_Input::STRING);
		if($loc == 'pmsearch')
		{
			$id = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
			$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::ID);
			$murl = BS_URL::get_sub_url('userprofile','pmsearch');
			$murl->set(BS_URL_ID,$id);
			$murl->set(BS_URL_SITE,$site);
		}
		else
			$murl = BS_URL::get_sub_url();
		
		$this->add_link($locale->lang('back'),$murl);

		return '';
	}
}
?>