<?php
/**
 * Contains the add-link-action
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
 * The add-link-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_linklist_addlink extends BS_Front_Action_Base
{
	function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$ips = FWS_Props::get()->ips();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// check if the user has permission to add a link
		if($cfg['enable_linklist'] == 0 || !$auth->has_global_permission('add_new_link'))
			return 'The linklist is disabled or you have no permission to add links';

		// check for spam
		$time = time();
		$spam_linkadd_on = $auth->is_ipblock_enabled('spam_linkadd');
		if($spam_linkadd_on)
		{
			if($ips->entry_exists('linkadd'))
				return 'linkipsperre';
		}

		// grab input-params
		$link = $input->get_var('link_url','post',FWS_Input::STRING);
		$new_category = $input->get_var('new_category','post',FWS_Input::STRING);
		$category = $input->get_var('link_category','post',FWS_Input::STRING);

		// check if the link exists
		$link = FWS_StringHelper::correct_homepage($link);
		if(BS_DAO::get_links()->url_exists($link))
			return 'linkschoneingefuegt';

		if(trim($link) == '')
			return 'fillallfields';

		$post_text = $input->get_var('text','post',FWS_Input::STRING);
		$text = '';
		$error = BS_PostingUtils::prepare_message_for_db($text,$post_text,'desc');
		if($error != '')
			return $error;

		// insert the link into the database
		$sql_category = ($new_category != '') ? $new_category : $category;
		$active = ($cfg['linklist_activate_links'] == 1 && !$user->is_admin()) ? 0 : 1;
		
		if($sql_category == '')
			return 'fillallfields';

		$fields = array(
			'link_url' => $link,
			'category' => $sql_category,
			'link_desc' => $text,
			'link_desc_posted' => $post_text,
			'link_date' => $time,
			'user_id' => $user->get_user_id(),
			'active' => $active
		);
		BS_DAO::get_links()->create($fields);

		// make the ip-entry, if necessary
		$ips->add_entry('linkadd');

		// write PM's to the admins if enabled
		if($cfg['get_email_new_link'] == 1 && $active == 0)
		{
			$email = BS_EmailFactory::get_instance()->get_new_link_mail();
			foreach(BS_DAO::get_user()->get_users_by_groups(array(BS_STATUS_ADMIN)) as $adata)
			{
				$email->set_recipient($adata['user_email']);
				$email->send_mail();
			}
		}

		$this->set_action_performed(true);
		$this->add_link($locale->lang('back'),BS_URL::get_mod_url('linklist'));
		if($active == 1)
			$this->set_success_msg($locale->lang('success_'.BS_ACTION_ADD_LINK.'_activated'));
		else
			$this->set_success_msg($locale->lang('success_'.BS_ACTION_ADD_LINK.'_not_activated'));

		return '';
	}
}
?>