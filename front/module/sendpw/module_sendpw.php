<?php
/**
 * Contains the sendpw-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The sendpw-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_sendpw extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();
		
		$doc->set_has_access(!$user->is_loggedin());
		
		$doc->add_action(BS_ACTION_SEND_PW,'default');

		$doc->add_breadcrumb($locale->lang('forgetpw'),$url->get_url('sendpw'));
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
		$user = PLIB_Props::get()->user();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$cfg = PLIB_Props::get()->cfg();

		if(BS_ENABLE_EXPORT && BS_EXPORT_SEND_PW_TYPE != 'enabled')
		{
			$this->report_error();
			return;
		}
		
		$this->request_formular(false,false);
		
		$sec_code_field = PLIB_StringHelper::generate_random_key(15);
		$user->set_session_data('sec_code_field',$sec_code_field);
		
		$tpl->add_variables(array(
			'target_url' => $url->get_url('sendpw'),
			'action_type' => BS_ACTION_SEND_PW,
			'enable_security_code' => $cfg['enable_security_code'] == 1,
			'security_code_img' => $url->get_url('security_code'),
			'sec_code_field' => $sec_code_field
		));
	}
}
?>