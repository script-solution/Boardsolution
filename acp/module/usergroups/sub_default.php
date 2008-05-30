<?php
/**
 * Contains the default-submodule for usergroups
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the usergroups-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_usergroups_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_DELETE_USER_GROUPS => 'delete'
		);
	}
	
	public function run()
	{
		if(($delete = $this->input->get_var('delete','post')) != null)
		{
			$ids = implode(',',$delete);
			$names = array();
			foreach(BS_DAO::get_usergroups()->get_by_ids($delete) as $group)
				$names[] = $group['group_title'];
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				$this->locale->lang('delete_group_notice').'<br /><br />'
					.sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(0,'&amp;at='.BS_ACP_ACTION_DELETE_USER_GROUPS.'&amp;ids='.$ids),
				$this->url->get_acpmod_url()
			);
		}
		
		$helper = BS_ACP_Module_UserGroups_Helper::get_instance();
		$predef_groups = $helper->get_predef_groups();
		
		$groups = array();
		foreach($this->cache->get_cache('user_groups') as $data)
		{
			$groups[] = array(
				'id' => $data['id'],
				'group_name' => $this->auth->get_colored_groupname($data['id']),
				'is_visible' => BS_ACP_Utils::get_instance()->get_yesno($data['is_visible']),
				'is_super_mod' => BS_ACP_Utils::get_instance()->get_yesno($data['is_super_mod']),
				'is_no_predefined_group' => !in_array($data['id'],$predef_groups)
			);
		}

		$this->tpl->add_array('groups',$groups);
	}
	
	public function get_location()
	{
		return array();
	}
}
?>