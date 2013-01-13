<?php
/**
 * Contains the new-mail-module
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
 * The new-mail-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_new_mail extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$auth = FWS_Props::get()->auth();
		$cfg = FWS_Props::get()->cfg();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($auth->has_global_permission('send_mails') && $cfg['enable_emails'] == 1);
		
		$renderer->add_action(BS_ACTION_SEND_EMAIL,'default');

		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_ID,$id);
		$renderer->add_breadcrumb($locale->lang('email'),$url->to_url());
	}
	
	/**
	 * @see BS_Front_Module::is_guest_only()
	 * @return boolean
	 */
	public function is_guest_only()
	{
		return true;
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		// check if the id is valid
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		if($id == null)
		{
			$this->report_error();
			return;
		}

		$data = BS_DAO::get_profile()->get_user_by_id($id);
		
		// check if the user has been found
		if($data === false)
		{
			$this->report_error();
			return;
		}

		// check if the user has allowed board emails
		if($data['allow_board_emails'] == 0)
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('user_disabled_emails'));
			return;
		}

		$form = $this->request_formular(false,true);
		
		if($input->isset_var('preview','post'))
		{
			$content_type_val = $form->get_input_value('content_type','plain');
			BS_PostingUtils::add_post_preview(
				'posts',$content_type_val == 'html',$content_type_val == 'html'
			);
		}
		
		$pform = new BS_PostingForm($locale->lang('text').':');
		$pform->add_form();
		
		$content_type_options = array(
			'plain' => $locale->lang('content_type_plain'),
			'html' => $locale->lang('content_type_html')
		);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_ID,$id);
		$tpl->add_variables(array(
			'target_url' => $url->to_url(),
			'receiver' => BS_UserUtils::get_link($id,$data['user_name'],$data['user_group']),
			'action_type' => BS_ACTION_SEND_EMAIL,
			'content_type_options' => $content_type_options
		));
	}
}
?>