<?php
/**
 * Contains the add-submodule for user
 * 
 * @version			$Id: sub_add.php 676 2008-05-08 09:02:28Z nasmussen $
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
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_USER_ADD => 'add'
		);
	}
	
	public function run()
	{
		$this->_request_formular(false,false);

		// group combos
		$groups = array();
		foreach($this->cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST)
				$groups[$gdata['id']] = $gdata['group_title'];
		}
		
		$this->tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_USER_ADD,
			'groups' => $groups,
			'main_group' => BS_STATUS_USER,
			'other_groups' => array()
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('register_user') => $this->url->get_acpmod_url(0,'&amp;action=add')
		);
	}
}
?>