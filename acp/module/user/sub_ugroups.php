<?php
/**
 * Contains the ugroups-submodule for user
 * 
 * @version			$Id$
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
		$renderer = $doc->use_default_renderer();

		$this->_ids = $input->get_var('delete','post');
		if($this->_ids === null)
			$this->_ids = $input->get_var('ids','get',PLIB_Input::STRING);
		
		if(!is_array($this->_ids))
			$this->_ids = PLIB_Array_Utils::advanced_explode(',',$this->_ids);
		
		$renderer->add_action(BS_ACP_ACTION_USER_EDIT_UGROUPS,'ugroups');

		$renderer->add_breadcrumb(
			$locale->lang('edit_groups'),
			$url->get_acpmod_url(0,'&amp;action=ugroups&amp;ids='.implode(',',$this->_ids))
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$cache = PLIB_Props::get()->cache();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$auth = PLIB_Props::get()->auth();
		$user = PLIB_Props::get()->user();

		// invalid ids?
		if(count($this->_ids) == 0 || !PLIB_Array_Utils::is_integer($this->_ids))
		{
			$this->report_error();
			return;
		}
		
		// collect groups
		$group_options = array();
		$maingroups = array();
		foreach($cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST)
			{
				if($gdata['is_visible'] == 1)
					$maingroups[$gdata['id']] = $gdata['group_title'];
				$group_options[$gdata['id']] = $gdata['group_title'];
			}
		}

		$tpl->add_variables(array(
			'user_ids' => implode(',',$this->_ids),
			'groups' => $group_options,
			'maingroups' => $maingroups,
			'action_type' => BS_ACP_ACTION_USER_EDIT_UGROUPS,
			'target_url' => $url->get_acpmod_url(0,'&amp;action=ugroups')
		));
		
		$this->request_formular();

		// grab user from db
		$users = array();
		$userlist = BS_DAO::get_profile()->get_users_by_ids(
			$this->_ids,'u.`'.BS_EXPORT_USER_NAME.'`','ASC'
		);
		foreach($userlist as $data)
		{
			$groups = PLIB_Array_Utils::advanced_explode(',',$data['user_group']);
			$current = $auth->get_usergroup_list($data['user_group'],false,true,true);

			$gdata = $cache->get_cache('user_groups')->get_element($groups[0]);
			$sel_groups = $groups;
			unset($sel_groups[0]);
			
			$users[] = array(
				'id' => $data['id'],
				'is_own_user' => $data['id'] == $user->get_user_id(),
				'user_name' => BS_ACP_Utils::get_instance()->get_userlink($data['id'],$data['user_name']),
				'current' => $current,
				'main_group' => $gdata['id'],
				'other_groups' => $sel_groups
			);
		}

		$tpl->add_variables(array(
			'user' => $users,
			'back_url' => $url->get_acpmod_url()
		));
	}
}
?>