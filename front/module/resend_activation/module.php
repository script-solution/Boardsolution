<?php
/**
 * Contains the resend-activation-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The resend-activation-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_resend_activation extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();
		$user = FWS_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access(!$user->is_loggedin());
		
		$renderer->add_action(BS_ACTION_RESEND_ACT_LINK,'default');

		$renderer->add_breadcrumb($locale->lang('resend_activation_link'),$url->get_url(0));
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
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$url = FWS_Props::get()->url();
		$cfg = FWS_Props::get()->cfg();

		if(BS_ENABLE_EXPORT)
		{
			$this->report_error();
			return;
		}
		
		$this->request_formular(false,false);
		
		$sec_code_field = FWS_StringHelper::generate_random_key(15);
		$user->set_session_data('sec_code_field',$sec_code_field);
		
		$tpl->add_variables(array(
			'target_url' => $url->get_url('resend_activation'),
			'action_type' => BS_ACTION_RESEND_ACT_LINK,
			'enable_security_code' => $cfg['enable_security_code'] == 1,
			'security_code_img' => $url->get_url('security_code'),
			'sec_code_field' => $sec_code_field
		));
	}
}
?>