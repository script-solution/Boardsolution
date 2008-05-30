<?php
/**
 * Contains the user-activation module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The user-activation-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_useractivation extends BS_ACP_Module
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_USER_ACT_DELETE => 'delete',
			BS_ACP_ACTION_USER_ACT_ACTIVATE => 'activate'
		);
	}
	
	public function run()
	{
		// community exported?
		if(BS_ENABLE_EXPORT)
		{
			$this->msgs->add_error($this->locale->lang('activation_community_exported'));
			return;
		}
		
		// show delete-message?
		$action_type = $this->input->get_var('action_type','post',PLIB_Input::STRING);
		if(($ids = $this->input->get_var('delete','post')) != null && $action_type != 'none')
		{
			if($action_type == 'delete')
			{
				$msg = $this->locale->lang('delete_message');
				$at = BS_ACP_ACTION_USER_ACT_DELETE;
			}
			else
			{
				$msg = $this->locale->lang('activate_user');
				$at = BS_ACP_ACTION_USER_ACT_ACTIVATE;
			}
			
			$id_str = implode(',',$ids);
			
			$names = array();
			foreach(BS_DAO::get_user()->get_users_by_ids($ids,0) as $user)
				$names[] = $user['user_name'];
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($msg,$namelist),
				$this->url->get_acpmod_url(0,'&amp;at='.$at.'&amp;ids='.$id_str),
				$this->url->get_acpmod_url()
			);
		}
		
		$num = BS_DAO::get_user()->get_user_count(0,-1);
		$end = 20;
		$pagination = new BS_ACP_Pagination($end,$num);
		
		$userlist = BS_DAO::get_profile()->get_users(
			'p.registerdate','DESC',$pagination->get_start(),$end,0,-1
		);
		$user = array();
		foreach($userlist as $data)
		{
			$user[] = array(
				'id' => $data['id'],
				'user_name' => BS_ACP_Utils::get_instance()->get_userlink($data['id'],$data['user_name']),
				'user_email' => $data['user_email'],
				'register_date' => PLIB_Date::get_date($data["registerdate"],true)
			);
		}
		
		$this->tpl->add_variables(array(
			'not_exported' => !BS_ENABLE_EXPORT
		));
		$this->tpl->add_array('user',$user);

		$this->functions->add_pagination($pagination,$this->url->get_acpmod_url(0,'&amp;site={d}'));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('acpmod_user_activation') => $this->url->get_acpmod_url()
		);
	}
}
?>