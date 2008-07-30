<?php
/**
 * Contains the module for the confirmation of email-address-changes
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The page to confirm an email-address-change
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_conf_email extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$user = PLIB_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_template('extern_conf.htm');
		$renderer->set_has_access(!$user->is_loggedin());
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
		$locale = PLIB_Props::get()->locale();
		$db = PLIB_Props::get()->db();
		$url = PLIB_Props::get()->url();
		$msgs = PLIB_Props::get()->msgs();
		$doc = PLIB_Props::get()->doc();

		// check parametes
		$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$key = $input->get_var(BS_URL_PID,'get',PLIB_Input::STRING);
		
		if($id == null || $key == null)
		{
			$this->report_error();
			return;
		}
		
		$data = BS_DAO::get_changeemail()->get_by_user($id,$key);
		if($data === false)
		{
			$this->report_error(PLIB_Document_Messages::ERROR,$locale->lang('email_change_failed'));
			return;
		}
		
		$db->start_transaction();
		
		BS_DAO::get_user()->update($id,'',$data['email_address']);
		BS_DAO::get_changeemail()->delete_by_user($id);
		
		$db->commit_transaction();
		
		// fire community-event
		$udata = BS_DAO::get_profile()->get_user_by_id($id);
		$udata['user_email'] = $data['email_address'];
		$user = BS_Community_User::get_instance_from_data($udata);
		BS_Community_Manager::get_instance()->fire_user_changed($user);
	
		$murl = $url->get_frontend_url();
		$message = sprintf(
			$locale->lang('email_change_success'),
			'<a href="'.$murl.'">'.$locale->lang('here').'</a>'
		);
		$msgs->add_notice($message);
		
		$doc->request_redirect($murl);
	}
}
?>