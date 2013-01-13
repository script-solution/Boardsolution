<?php
/**
 * Contains the module-submodule for acpaccess
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
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
 * The module sub-module for the acpaccess-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_acpaccess_module extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_ACPACCESS_MODULE,'module');

		$module = $input->get_var('module','get',FWS_Input::STRING);
		$renderer->add_breadcrumb(
			$locale->lang('edit_permissions_for_module'),
			BS_URL::get_acpsub_url()->set('module',$module)->to_url()
		);
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();

		$module = $input->get_var('module','get',FWS_Input::STRING);
		if(BS_ACP_Module_ACPAccess_Helper::get_module_name($module) === '')
		{
			$this->report_error();
			return;
		}

		$options = BS_ACP_Module_ACPAccess_Helper::get_group_options();
		$user = array();
		$groups = array();
		foreach(BS_DAO::get_acpaccess()->get_by_module($module) as $data)
		{
			if($data['access_type'] == 'user')
				$user[$data['access_value']] = $data['user_name'];
			else
				$groups[] = $data['access_value'];
		}
		
		$groupcombo = new FWS_HTML_ComboBox('groups[]','groups',$groups,null,count($options),true);
		$groupcombo->set_options($options);
		$groupcombo->set_css_attribute('width','100%');
		
		$usercombo = new FWS_HTML_ComboBox('user_intern','user_intern',array(),null,5,true);
		$usercombo->set_options($user);
		$usercombo->set_css_attribute('width','100%');

		$mod = BS_ACP_Module_ACPAccess_Helper::get_module_name($module);
		
		$this->request_formular();
		
		$tpl->add_variables(array(
			'module' => $module,
			'action_type' => BS_ACP_ACTION_ACPACCESS_MODULE,
			'group_combo' => $groupcombo->to_html(),
			'search_url' => BS_URL::get_acpmod_url('usersearch')->set('comboid','user_intern')->to_url(),
			'module_name' => $locale->lang($mod),
			'user_combo' => $usercombo->to_html(),
			'current_user_permissions' => count($user) > 0 ? implode(', ',$user) : '-',
		));
	}
}
?>