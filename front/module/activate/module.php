<?php
/**
 * Contains the activate-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * This class activates a user
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_activate extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_template('extern_conf.htm');
		$renderer->set_has_access(!$user->is_loggedin());
		$renderer->add_breadcrumb($locale->lang('activation'),'');
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
		$locale = FWS_Props::get()->locale();
		$db = FWS_Props::get()->db();
		$url = FWS_Props::get()->url();
		$msgs = FWS_Props::get()->msgs();
		
		// check parametes
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$key = $input->get_var(BS_URL_KW,'get',FWS_Input::STRING);
		
		if($id == null || $key == null)
		{
			$this->report_error();
			return;
		}
		
		$murl = $url->get_frontend_url();
		if(BS_DAO::get_activation()->exists($id,$key))
		{
			$db->start_transaction();
			
			BS_DAO::get_profile()->update_user_by_id(array('active' => 1),$id);
			BS_DAO::get_activation()->delete($id,$key);
			
			$db->commit_transaction();
			
			// fire community-event
			$udata = BS_DAO::get_profile()->get_user_by_id($id);
			$user = BS_Community_User::get_instance_from_data($udata);
			BS_Community_Manager::get_instance()->fire_user_registered($user);
			
			$message = sprintf(
				$locale->lang('activate_success'),
				'<a href="'.$murl.'">'.$locale->lang('here').'</a>'
			);
			$msgs->add_notice($message);
		}
	}
}
?>