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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$this->set_guest_only(true);
		
		$user = FWS_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_template('extern_conf.htm');
		$renderer->set_has_access(!$user->is_loggedin());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$db = FWS_Props::get()->db();
		$msgs = FWS_Props::get()->msgs();
		$doc = FWS_Props::get()->doc();

		// check parametes
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$key = $input->get_var(BS_URL_PID,'get',FWS_Input::STRING);
		
		if($id == null || $key == null)
		{
			$this->report_error();
			return;
		}
		
		$data = BS_DAO::get_changeemail()->get_by_user($id,$key);
		if($data === false)
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('email_change_failed'));
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
	
		$murl = BS_URL::get_portal_url();
		$message = sprintf(
			$locale->lang('email_change_success'),
			'<a href="'.$murl->to_url().'">'.$locale->lang('here').'</a>'
		);
		$msgs->add_notice($message);
		
		$doc->request_redirect($murl);
	}
}
?>