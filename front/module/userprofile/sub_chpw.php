<?php
/**
 * Contains the chpw-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The chpw submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_chpw extends BS_Front_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$cfg = PLIB_Props::get()->cfg();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_CHANGE_USER_PW,'chguserpw');
		
		$title = $cfg['profile_max_user_changes'] != 0 ? 'user_n_pw_change' : 'pw_change';
		$renderer->add_breadcrumb($locale->lang($title),$url->get_url(0,'&amp;'.BS_URL_LOC.'=chpw'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$cfg = PLIB_Props::get()->cfg();
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		// has the user the permission to change user/pw
		if(BS_ENABLE_EXPORT)
		{
			$this->report_error(PLIB_Document_Messages::ERROR);
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
			'target_url' => $url->get_url('userprofile','&amp;'.BS_URL_LOC.'=chpw'),
			'action_type' => BS_ACTION_CHANGE_USER_PW,
			'enable_username_change' => $cfg['profile_max_user_changes'] != 0,
			'max_changes_notice' => $max_changes_notice,
			'user_name' => $user->get_profile_val('user_name')
		));
	}
}
?>