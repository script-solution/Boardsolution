<?php
/**
 * Contains the items-submodule for cfgitems
 * 
 * @version			$Id$
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
		
		$renderer->add_action(BS_ACP_ACTION_SAVE_SETTINGS,'save');
		$renderer->add_action(BS_ACP_ACTION_REVERT_SETTING,'revert');

		// set group-id
		$gid = $input->get_var('gid','get',FWS_Input::ID);
		if($gid == null)
			$gid = $input->set_var('gid','get',1);
		
		// load group
		$helper = BS_ACP_Module_Config_Helper::get_instance();
		$helper->get_manager()->load_group($gid);

		// add bread crumb
		$gid = $input->get_var('gid','get',FWS_Input::ID);
		if($gid != null)
		{
			$manager = BS_ACP_Module_Config_Helper::get_instance()->get_manager();
			$title = $locale->lang($manager->get_group($gid)->get_title(),false);
			$url = BS_URL::get_acpsub_url();
			$url->set('gid',$gid);
			$renderer->add_breadcrumb($title,$url->to_url());
		}
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();

		$gid = $input->get_var('gid','get',FWS_Input::ID);
		if($gid == null)
		{
			$this->report_error();
			return;
		}
		
		$helper = BS_ACP_Module_Config_Helper::get_instance();
		$manager = $helper->get_manager();
		$view = new BS_ACP_Module_Config_View_Default();
		$manager->display($view);
		
		$perline = 6;
		$hidden_fields = BS_URL::get_acpmod_comps();
		$hidden_fields['action'] = 'search';
		
		$url = BS_URL::get_acpsub_url();
		$url->set('gid',$gid);
		
		$tpl->add_variables(array(
			'form_target' => $url->to_url(),
			'action_type' => BS_ACP_ACTION_SAVE_SETTINGS,
			'title' => $locale->lang($manager->get_group($gid)->get_title(),false),
			'items' => $view->get_items(),
			'form_target' => $input->get_var('SERVER_PHPSELF','server',FWS_Input::STRING),
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
}
?>