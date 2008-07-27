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
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();
		
		$doc->set_has_access(!$user->is_loggedin());
		
		$doc->add_action(BS_ACTION_CHANGE_PASSWORD,'default');

		$user_id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$user_key = $input->get_var(BS_URL_KW,'get',PLIB_Input::STRING);
		$doc->add_breadcrumb(
			$locale->lang('change_password'),
			$url->get_url('change_password','&amp;'.BS_URL_ID.'='.$user_id.'&amp;'.BS_URL_KW.'='.$user_key)
		);
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
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$cfg = PLIB_Props::get()->cfg();

		if(BS_ENABLE_EXPORT && BS_EXPORT_SEND_PW_TYPE != 'enabled')
		{
			$this->report_error();
			return;
		}

		$user_id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$user_key = $input->get_var(BS_URL_KW,'get',PLIB_Input::STRING);

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

		$tpl->add_variables(array(
			'target_url' => $url->get_url(
				0,'&amp;'.BS_URL_ID.'='.$user_id.'&amp;'.BS_URL_KW.'='.$user_key
			),
			'action_type' => BS_ACTION_CHANGE_PASSWORD,
			'password_size' => max(10,min(50,$cfg['profile_max_pw_len'])),
			'password_maxlength' => $cfg['profile_max_pw_len']
		));
	}
}
?>