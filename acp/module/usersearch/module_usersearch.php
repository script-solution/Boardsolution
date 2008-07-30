<?php
/**
 * Contains the ACP-user-search-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The popup to search for users in the ACP
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_usersearch extends BS_ACP_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->set_template('popup_usersearch.htm');
		$renderer->set_show_headline(false);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$auth = PLIB_Props::get()->auth();
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		$comboid = $input->get_var('comboid','get',PLIB_Input::STRING);
		$search = $input->get_var('search','post',PLIB_Input::INTEGER);
		$user_name = $input->get_var('user_name','post',PLIB_Input::STRING);
		$user_email = $input->get_var('user_email','post',PLIB_Input::STRING);
		$reg_day = $input->get_var('reg_day','post',PLIB_Input::INTEGER);
		$reg_month = $input->get_var('reg_month','post',PLIB_Input::INTEGER);
		$reg_year = $input->get_var('reg_year','post',PLIB_Input::INTEGER);
		$user_groups = $input->get_var('user_groups','post');
		$reg_mode = $input->correct_var(
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
		
		$this->request_formular();
		
		$tpl->add_variables(array(
			'combo_id' => $comboid
		));
		
		$group_options = array();
		foreach($cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST)
				$group_options[$gdata['id']] = $gdata['group_title'];
		}
		
		$date_types = array(
			'ever' => $locale->lang('indifferent'),
			'date' => $locale->lang('since_date')
		);
		
		$tpl->add_variables(array(
			'action_param' => BS_URL_ACTION,
			'action' => $url->get_acpmod_url('usersearch','&amp;comboid='.$comboid),
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
			$tpl->add_variables(array(
				'action' => $url->get_acpmod_url('usersearch')
			));
		
			$user_groups = is_array($user_groups) ? $user_groups : array();
			$userlist = BS_DAO::get_profile()->get_users_by_search(
				$user_name,$user_email,$reg_mode != 'ever' ? $reg_time : 0,$user_groups,
				'u.`'.BS_EXPORT_USER_NAME.'`'
			);
			foreach($userlist as $data)
			{
				$group_combo = $auth->get_usergroup_list($data['user_group'],false,false,true);
				
				$users[] = array(
					'id' => $data['id'],
					'user_name' => $data['user_name'],
					'user_email' => $data['user_email'],
					'user_group' => $group_combo
				);
			}
		}
		
		$tpl->add_array('users',$users);
	}
}
?>