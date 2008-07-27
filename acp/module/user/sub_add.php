<?php
/**
 * Contains the add-submodule for user
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add sub-module for the user-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_user_add extends BS_ACP_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		$doc->add_action(BS_ACP_ACTION_USER_ADD,'add');
		$doc->add_breadcrumb($locale->lang('register_user'),$url->get_acpmod_url(0,'&amp;action=add'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$cache = PLIB_Props::get()->cache();
		$tpl = PLIB_Props::get()->tpl();

		$this->request_formular(false,false);

		// group combos
		$groups = array();
		foreach($cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST)
				$groups[$gdata['id']] = $gdata['group_title'];
		}
		
		$tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_USER_ADD,
			'groups' => $groups,
			'main_group' => BS_STATUS_USER,
			'other_groups' => array()
		));
	}
}
?>