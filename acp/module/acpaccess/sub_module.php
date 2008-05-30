<?php
/**
 * Contains the module-submodule for acpaccess
 * 
 * @version			$Id: sub_module.php 713 2008-05-20 21:59:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_ACPACCESS_MODULE => 'module'
		);
	}
	
	public function run()
	{
		$module = $this->input->get_var('module','get',PLIB_Input::STRING);
		if(BS_ACP_Module_ACPAccess_Helper::get_instance()->get_module_name($module) === '')
		{
			$this->_report_error();
			return;
		}

		$options = BS_ACP_Module_ACPAccess_Helper::get_instance()->get_group_options();
		$user = array();
		$groups = array();
		foreach(BS_DAO::get_acpaccess()->get_by_module($module) as $data)
		{
			if($data['access_type'] == 'user')
				$user[$data['access_value']] = $data['user_name'];
			else
				$groups[] = $data['access_value'];
		}
		
		$groupcombo = new PLIB_HTML_ComboBox('groups[]','groups',$groups,null,count($options),true);
		$groupcombo->set_options($options);
		$groupcombo->set_css_attribute('width','100%');
		
		$usercombo = new PLIB_HTML_ComboBox('user_intern','user_intern',array(),null,5,true);
		$usercombo->set_options($user);
		$usercombo->set_css_attribute('width','100%');

		$mod = BS_ACP_Module_ACPAccess_Helper::get_instance()->get_module_name($module);
		
		$this->_request_formular(false,false);
		
		$this->tpl->add_variables(array(
			'module' => $module,
			'action_type' => BS_ACP_ACTION_ACPACCESS_MODULE,
			'group_combo' => $groupcombo->to_html(),
			'search_url' => $this->url->get_standalone_url('acp','user_search','&amp;comboid=user_intern'),
			'module_name' => $this->locale->lang($mod),
			'user_combo' => $usercombo->to_html(),
			'current_user_permissions' => count($user) > 0 ? implode(', ',$user) : '-',
		));
	}
	
	public function get_location()
	{
		$module = $this->input->get_var('module','get',PLIB_Input::STRING);
		return array(
			$this->locale->lang('edit_permissions_for_module') => $this->url->get_acpmod_url(
				0,'&amp;action=module&amp;module='.$module
			)
		);
	}
}
?>