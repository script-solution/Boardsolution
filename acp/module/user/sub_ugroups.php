<?php
/**
 * Contains the ugroups-submodule for user
 * 
 * @version			$Id: sub_ugroups.php 737 2008-05-23 18:26:46Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The ugroups sub-module for the user-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_user_ugroups extends BS_ACP_SubModule
{
	/**
	 * The user-ids
	 *
	 * @var array
	 */
	private $_ids = null;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_ids = $this->input->get_var('delete','post');
		if($this->_ids === null)
			$this->_ids = $this->input->get_var('ids','get',PLIB_Input::STRING);
		
		if(!is_array($this->_ids))
			$this->_ids = PLIB_Array_Utils::advanced_explode(',',$this->_ids);
	}
	
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_USER_EDIT_UGROUPS => 'ugroups'
		);
	}
	
	public function run()
	{
		// invalid ids?
		if(count($this->_ids) == 0 || !PLIB_Array_Utils::is_integer($this->_ids))
		{
			$this->_report_error();
			return;
		}
		
		// collect groups
		$group_options = array();
		$maingroups = array();
		foreach($this->cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST)
			{
				if($gdata['is_visible'] == 1)
					$maingroups[$gdata['id']] = $gdata['group_title'];
				$group_options[$gdata['id']] = $gdata['group_title'];
			}
		}

		$this->tpl->add_variables(array(
			'user_ids' => implode(',',$this->_ids),
			'groups' => $group_options,
			'maingroups' => $maingroups,
			'action_type' => BS_ACP_ACTION_USER_EDIT_UGROUPS,
			'target_url' => $this->url->get_acpmod_url(0,'&amp;action=ugroups')
		));
		
		$this->_request_formular();

		// grab user from db
		$user = array();
		$userlist = BS_DAO::get_profile()->get_users_by_ids(
			$this->_ids,'u.`'.BS_EXPORT_USER_NAME.'`','ASC'
		);
		foreach($userlist as $data)
		{
			$groups = PLIB_Array_Utils::advanced_explode(',',$data['user_group']);
			$current = $this->auth->get_usergroup_list($data['user_group'],false,true,true);

			$gdata = $this->cache->get_cache('user_groups')->get_element($groups[0]);
			$sel_groups = $groups;
			unset($sel_groups[0]);
			
			$user[] = array(
				'id' => $data['id'],
				'is_own_user' => $data['id'] == $this->user->get_user_id(),
				'user_name' => BS_ACP_Utils::get_instance()->get_userlink($data['id'],$data['user_name']),
				'current' => $current,
				'main_group' => $gdata['id'],
				'other_groups' => $sel_groups
			);
		}

		$this->tpl->add_variables(array(
			'user' => $user,
			'back_url' => $this->url->get_acpmod_url()
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('edit_groups') => $this->url->get_acpmod_url(
				0,'&amp;action=ugroups&amp;ids='.implode(',',$this->_ids)
			)
		);
	}
}
?>