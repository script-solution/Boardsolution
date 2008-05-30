<?php
/**
 * Contains the items-submodule for cfgitems
 * 
 * @version			$Id: sub_items.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The items sub-module for the cfgitems-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_config_items extends BS_ACP_SubModule
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$gid = $this->input->get_var('gid','get',PLIB_Input::ID);
		if($gid == null)
			$gid = $this->input->set_var('gid','get',1);
		
		$helper = BS_ACP_Module_Config_Helper::get_instance();
		$helper->get_manager()->load_group($gid);
	}
	
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_SAVE_SETTINGS => 'save',
			BS_ACP_ACTION_REVERT_SETTING => 'revert'
		);
	}
	
	public function run()
	{
		$gid = $this->input->get_var('gid','get',PLIB_Input::ID);
		if($gid == null)
		{
			$this->_report_error();
			return;
		}
		
		$helper = BS_ACP_Module_Config_Helper::get_instance();
		$manager = $helper->get_manager();
		$view = new BS_ACP_Module_Config_View_Default();
		$manager->display($view);
		
		$perline = 6;
		$hidden_fields = $this->url->get_acpmod_comps();
		$hidden_fields['action'] = 'search';
		$this->tpl->add_variables(array(
			'form_target' => $this->url->get_acpmod_url(0,'&amp;action=items&amp;gid='.$gid),
			'action_type' => BS_ACP_ACTION_SAVE_SETTINGS,
			'title' => $this->locale->lang($manager->get_group($gid)->get_title(),false),
			'items' => $view->get_items(),
			'form_target' => $this->input->get_var('SERVER_PHPSELF','server',PLIB_Input::STRING),
			'hidden_fields' => $hidden_fields,
			'groups_per_line' => $perline,
			'group_rows' => $helper->get_groups($gid,$perline),
			'groups_width' => round(100 / $perline),
			'keyword' => '',
			'at' => BS_ACP_ACTION_REVERT_SETTING,
			'view' => 'items',
			'gid' => $gid,
			'display_affects_msgs_hints' => $view->has_affects_msgs_settings()
		));
	}
	
	public function get_location()
	{
		$gid = $this->input->get_var('gid','get',PLIB_Input::ID);
		if($gid == null)
			return array();
		
		$manager = BS_ACP_Module_Config_Helper::get_instance()->get_manager();
		$title = $this->locale->lang($manager->get_group($gid)->get_title(),false);
		return array(
			$title => $this->url->get_acpmod_url(0,'&amp;action=items&amp;gid='.$gid)
		);
	}
}
?>