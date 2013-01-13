<?php
/**
 * Contains the update-sig-action
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
 * The update-sig-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_updatesig extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// the user has to be loggedin
		if(!$user->is_loggedin())
			return 'You are a guest';

		if($cfg['enable_signatures'] == 0)
			return 'Signatures are disabled';

		$post_text = $input->get_var('text','post',FWS_Input::STRING);

		$text = '';
		$error = BS_PostingUtils::prepare_message_for_db($text,$post_text,'sig');
		if($error != '')
			return $error;

		BS_DAO::get_profile()->update_user_by_id(array(
			'signatur' => $text,
			'signature_posted' => $post_text
		),$user->get_user_id());

		$user->set_profile_val('signatur',$text);
		$user->set_profile_val('signature_posted',$post_text);

		$this->set_action_performed(true);
		$this->add_link(
			$locale->lang('back'),BS_URL::get_sub_url('userprofile','signature')
		);

		return '';
	}
}
?>