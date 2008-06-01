<?php
/**
 * Contains the standalone-class for the ACP-user-search
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The popup to search for users in the ACP
 * 
 * @package			Boardsolution
 * @subpackage	acp.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Standalone_user_search extends BS_Standalone
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// we have to change some things for the ACP
		$this->locale->add_language_file('admin');		
		$this->tpl->set_path(PLIB_Path::inner().'acp/templates/');
	}
	
	public function get_template()
	{
		return 'popup_usersearch.htm';
	}
	
	public function run()
	{
		if(!$this->auth->has_acp_access())
			return;
		
		$comboid = $this->input->get_var('comboid','get',PLIB_Input::STRING);
		$search = $this->input->get_var('search','post',PLIB_Input::INTEGER);
		$user_name = $this->input->get_var('user_name','post',PLIB_Input::STRING);
		$user_email = $this->input->get_var('user_email','post',PLIB_Input::STRING);
		$reg_day = $this->input->get_var('reg_day','post',PLIB_Input::INTEGER);
		$reg_month = $this->input->get_var('reg_month','post',PLIB_Input::INTEGER);
		$reg_year = $this->input->get_var('reg_year','post',PLIB_Input::INTEGER);
		$user_groups = $this->input->get_var('user_groups','post');
		$reg_mode = $this->input->correct_var(
			'sreg_mode','post',PLIB_Input::STRING,array('ever','date'),'ever'
		);
		
		if($reg_day != null)
			$reg_time = PLIB_Date::get_timestamp(array(0,0,0,$reg_month,$reg_day,$reg_year));
		else
		{
			$reg_time = PLIB_Date::get_timestamp(array(
				0,0,0,
				PLIB_Date::get_formated_date('m'),
				PLIB_Date::get_formated_date('d'),
				PLIB_Date::get_formated_date('Y')
			));
		}
		
		$this->_request_formular();
		
		$this->tpl->add_variables(array(
			'combo_id' => $comboid
		));
		
		$group_options = array();
		foreach($this->cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST)
				$group_options[$gdata['id']] = $gdata['group_title'];
		}
		
		$date_types = array(
			'ever' => $this->locale->lang('indifferent'),
			'date' => $this->locale->lang('since_date')
		);
		
		$this->tpl->add_variables(array(
			'action_param' => BS_URL_ACTION,
			'action' => $this->url->get_standalone_url('acp','user_search','&amp;comboid='.$comboid),
			'user_name' => $user_name,
			'user_email' => $user_email,
			'date_types' => $date_types,
			'date_type' => $reg_mode,
			'reg_time' => $reg_time,
			'user_group_options' => $group_options,
			'user_groups' => $user_groups ? $user_groups : array(),
			'user_group_size' => min(5,count($group_options))
		));
		
		$users = array();
		if($search != null)
		{
			$this->tpl->add_variables(array(
				'action' => $this->url->get_standalone_url('acp','user_search')
			));
		
			$user_groups = is_array($user_groups) ? $user_groups : array();
			$userlist = BS_DAO::get_profile()->get_users_by_search(
				$user_name,$user_email,$reg_mode != 'ever' ? $reg_time : 0,$user_groups,
				'u.`'.BS_EXPORT_USER_NAME.'`'
			);
			foreach($userlist as $data)
			{
				$group_combo = $this->auth->get_usergroup_list($data['user_group'],false,false,true);
				
				$users[] = array(
					'id' => $data['id'],
					'user_name' => $data['user_name'],
					'user_email' => $data['user_email'],
					'user_group' => $group_combo
				);
			}
		}
		
		$this->tpl->add_array('users',$users);
	}
	
	public function require_board_access()
	{
		return false;
	}
}
?>