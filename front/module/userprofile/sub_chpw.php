<?php
/**
 * Contains the chpw-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The chpw submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_chpw extends BS_Front_SubModule
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
		$cfg = FWS_Props::get()->cfg();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_CHANGE_USER_PW,'chguserpw');
		
		$title = $cfg['profile_max_user_changes'] != 0 ? 'user_n_pw_change' : 'pw_change';
		$renderer->add_breadcrumb($locale->lang($title),BS_URL::build_sub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		
		// has the user the permission to change user/pw
		if(!BS_Community_Manager::get_instance()->is_user_management_enabled())
		{
			$this->report_error(FWS_Document_Messages::ERROR);
			return;
		}

		$max_changes_notice = '';
		if($cfg['profile_max_user_changes'] > 0)
		{
			$left = max(0,$cfg['profile_max_user_changes'] - $user->get_profile_val('username_changes'));
			$max_changes_notice = sprintf($locale->lang('max_username_changes_notice'),$left);
		}

		$tpl->set_template('inc_pw_complexity_js.htm');
		$js_script = $tpl->parse_template();
		
		$tpl->add_variables(array(
			'js_script' => $js_script
		));
		
		$this->request_formular();
		$tpl->add_variables(array(
			'user_name_size' => max(30,$cfg['profile_max_user_len']),
			'user_name_maxlength' => $cfg['profile_max_user_len'],
			'password_size' => max(30,$cfg['profile_max_pw_len']),
			'password_maxlength' => $cfg['profile_max_pw_len'],
			'target_url' => BS_URL::build_sub_url(),
			'action_type' => BS_ACTION_CHANGE_USER_PW,
			'enable_username_change' => $cfg['profile_max_user_changes'] != 0,
			'max_changes_notice' => $max_changes_notice,
			'user_name' => $user->get_profile_val('user_name')
		));
	}
}
?>