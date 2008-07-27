<?php
/**
 * Contains the module-submodule for acpaccess
 * 
 * @version			$Id$
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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		$doc->add_action(BS_ACP_ACTION_ACPACCESS_MODULE,'module');

		$module = $input->get_var('module','get',PLIB_Input::STRING);
		$doc->add_breadcrumb(
			$locale->lang('edit_permissions_for_module'),
			$url->get_acpmod_url(0,'&amp;action=module&amp;module='.$module)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$locale = PLIB_Props::get()->locale();

		$module = $input->get_var('module','get',PLIB_Input::STRING);
		if(BS_ACP_Module_ACPAccess_Helper::get_instance()->get_module_name($module) === '')
		{
			$this->report_error();
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
		
		$this->request_formular(false,false);
		
		$tpl->add_variables(array(
			'module' => $module,
			'action_type' => BS_ACP_ACTION_ACPACCESS_MODULE,
			'group_combo' => $groupcombo->to_html(),
			'search_url' => $url->get_acpmod_url('usersearch','&amp;comboid=user_intern'),
			'module_name' => $locale->lang($mod),
			'user_combo' => $usercombo->to_html(),
			'current_user_permissions' => count($user) > 0 ? implode(', ',$user) : '-',
		));
	}
}
?>