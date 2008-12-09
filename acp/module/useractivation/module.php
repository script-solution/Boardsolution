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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_USER_ACT_DELETE,'delete');
		$renderer->add_action(BS_ACP_ACTION_USER_ACT_ACTIVATE,'activate');

		$renderer->add_breadcrumb($locale->lang('acpmod_user_activation'),BS_URL::build_acpmod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();

		// community exported?
		if(BS_ENABLE_EXPORT)
		{
			$msgs->add_error($locale->lang('activation_community_exported'));
			return;
		}
		
		// show delete-message?
		$action_type = $input->get_var('action_type','post',FWS_Input::STRING);
		if(($ids = $input->get_var('delete','post')) != null && $action_type != 'none')
		{
			if($action_type == 'delete')
			{
				$msg = $locale->lang('delete_message');
				$at = BS_ACP_ACTION_USER_ACT_DELETE;
			}
			else
			{
				$msg = $locale->lang('activate_user');
				$at = BS_ACP_ACTION_USER_ACT_ACTIVATE;
			}
			
			$id_str = implode(',',$ids);
			
			$names = array();
			foreach(BS_DAO::get_user()->get_users_by_ids($ids,0) as $user)
				$names[] = $user['user_name'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$url = BS_URL::get_acpmod_url();
			$url->set('at',$at);
			$url->set('ids',$id_str);
			
			$functions->add_delete_message(
				sprintf($msg,$namelist),$url->to_url(),BS_URL::build_acpmod_url()
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
				'user_name' => BS_ACP_Utils::get_userlink($data['id'],$data['user_name']),
				'user_email' => $data['user_email'],
				'register_date' => FWS_Date::get_date($data["registerdate"],true)
			);
		}
		
		$tpl->add_variables(array(
			'not_exported' => !BS_ENABLE_EXPORT
		));
		$tpl->add_variable_ref('user',$user);

		$pagination->populate_tpl(BS_URL::get_acpmod_url());
	}
}
?>