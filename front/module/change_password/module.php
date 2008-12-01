<?php
/**
 * Contains the change-password-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The change-password-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_change_password extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access(!$user->is_loggedin());
		
		$renderer->add_action(BS_ACTION_CHANGE_PASSWORD,'default');

		$user_id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$user_key = $input->get_var(BS_URL_KW,'get',FWS_Input::STRING);
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_ID,$user_id);
		$url->set(BS_URL_KW,$user_key);
		$renderer->add_breadcrumb($locale->lang('change_password'),$url->to_url());
	}
	
	/**
	 * @see BS_Front_Module::is_guest_only()
	 *
	 * @return boolean
	 */
	public function is_guest_only()
	{
		return true;
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();

		if(BS_ENABLE_EXPORT && BS_EXPORT_SEND_PW_TYPE != 'enabled')
		{
			$this->report_error();
			return;
		}

		$user_id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$user_key = $input->get_var(BS_URL_KW,'get',FWS_Input::STRING);

		// check parameter
		if($user_id == null || $user_key == null)
		{
			$this->report_error();
			return;
		}

		// check if the entry exists
		if(!BS_DAO::get_changepw()->exists($user_id,$user_key))
		{
			$this->report_error();
			return;
		}
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_ID,$user_id);
		$url->set(BS_URL_KW,$user_key);
		$tpl->add_variables(array(
			'target_url' => $url->to_url(),
			'action_type' => BS_ACTION_CHANGE_PASSWORD,
			'password_size' => max(10,min(50,$cfg['profile_max_pw_len'])),
			'password_maxlength' => $cfg['profile_max_pw_len']
		));
	}
}
?>