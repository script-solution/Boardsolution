<?php
/**
 * Contains the module for the user-search in the frontend
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
 * Displays the popup with the front-end-user-search
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_user_search extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->set_template('popup_user_search.htm');
		$renderer->set_show_headline(false);
		$renderer->set_show_bottom(false);
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		
		// has the user the permission to view the user-search?
		if($cfg['enable_memberlist'] == 0 || !$auth->has_global_permission('view_memberlist'))
		{
			$this->report_error();
			return;
		}
		
		$hidden_fields = array();
		if(($sid = BS_URL::get_session_id()) !== false)
			$hidden_fields[$sid[0]] = $sid[1];
		$url = new BS_URL();
		$hidden_fields = array_merge($hidden_fields,$url->get_extern_vars());
		
		$name = $input->get_var(BS_URL_MS_NAME,'get',FWS_Input::STRING);
		$email = $input->get_var(BS_URL_MS_EMAIL,'get',FWS_Input::STRING);
		
		$limit = $cfg['members_per_page'];
		$num = BS_DAO::get_user()->get_search_user_count($name,$email);
		$pagination = new BS_Pagination($limit,$num);
		
		$tpl->add_variables(array(
			'num' => $num,
			'charset' => BS_HTML_CHARSET,
			'result_title' => sprintf($locale->lang('user_search_result'),$num),
			'search_target' => strtok(BS_FRONTEND_FILE,'?'),
			'hidden_fields' => $hidden_fields,
			'action_param' => BS_URL_ACTION,
			'action_value' => $input->get_var(BS_URL_ACTION,'get',FWS_Input::STRING),
			'name_param' => BS_URL_MS_NAME,
			'name_value' => $name,
			'email_param' => BS_URL_MS_EMAIL,
			'email_value' => $email
		));
		
		$current_module = $input->get_var('cmod','get',FWS_Input::STRING);
		
		$user_list = array();
		$users = BS_DAO::get_profile()->get_users_by_search(
			$name,$email,0,array(),'user_name','ASC',$pagination->get_start(),$limit
		);
		foreach($users as $data)
		{
			switch ($current_module)
			{
				case 'pmcompose':
					if($data['allow_pms'])
						$user_list[] = array(
								'user_name' => $data['user_name'],
								'email' => BS_UserUtils::get_displayed_email(
										$data['user_email'],$data['email_display_mode']
								),
								'user_group' => $auth->get_usergroup_list($data['user_group'],false,false),
						);
					break;
				
				default:
					$user_list[] = array(
							'user_name' => $data['user_name'],
							'email' => BS_UserUtils::get_displayed_email(
									$data['user_email'],$data['email_display_mode']
							),
							'user_group' => $auth->get_usergroup_list($data['user_group'],false,false),
					);
			}
		}
		
		$tpl->add_variable_ref('user_list',$user_list);
		
		$murl = BS_URL::get_mod_url();
		$murl->set(BS_URL_MS_NAME,$name);
		$murl->set(BS_URL_MS_EMAIL,$email);
		$pagination->populate_tpl($murl);
	}
}
?>